<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;  


class Organization extends Model
{
    // ──────────────────────────────────────────
    // 定数
    // ──────────────────────────────────────────

    const STATUS_NEW      = 0; // 新規
    const STATUS_ACTIVE   = 1; // 契約中
    const STATUS_ENDED    = 2; // 契約終了
    const STATUS_SPECIAL  = 3; // 特別枠（請求なし）
    const STATUS_PERSONAL = 4; // ドクター個人契約
    const STATUS_NO_RENEW = 5; // 更新しない
    //
    const STATUS_LABELS = [
        self::STATUS_NEW      => '新規',
        self::STATUS_ACTIVE   => '契約中',
        self::STATUS_ENDED    => '契約終了',
        self::STATUS_SPECIAL  => '特別枠',
        self::STATUS_PERSONAL => '個人契約',
        self::STATUS_NO_RENEW => '更新しない',
    ];

    // ──────────────────────────────────────────
    // テーブル設定
    // ──────────────────────────────────────────

    protected $table = 'organizations';

    protected $fillable = [
        'contract_no',
        'code',
        'name',
        'abbr',
        'url',
        'contract_status',
        'contract_date',
        'payment_method',
        'register_token',
        'rep_position',
        'rep_last_name',
        'rep_first_name',
        'new_contract_date',
        'tier',
    ];

    protected $casts = [
        'contract_no'     => 'integer',
        'contract_status' => 'integer',
        'contract_date'   => 'date',
        'payment_method'  => 'integer',
        'new_contract_date' => 'date',
        'tier' => 'integer',
    ];

    // ──────────────────────────────────────────
    // リレーション
    // ──────────────────────────────────────────

    /**
     * 所属会員（1対多）
     */
    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    /**
     * 全住所（1対多）
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(OrganizationAddress::class);
    }

    /**
     * 所在地
     */
    public function locationAddress(): HasOne
    {
        return $this->hasOne(OrganizationAddress::class)
                    ->where('type', OrganizationAddress::TYPE_LOCATION);
    }

    /**
     * 郵送先
     */
    public function shippingAddress(): HasOne
    {
        return $this->hasOne(OrganizationAddress::class)
                    ->where('type', OrganizationAddress::TYPE_SHIPPING);
    }

    /**
     * 請求先
     */
    public function billingAddress(): HasOne
    {
        return $this->hasOne(OrganizationAddress::class)
                    ->where('type', OrganizationAddress::TYPE_BILLING);
    }


    public function contract()
    {
        return $this->hasOne(OrganizationContract::class);
        // or hasMany, belongsTo, belongsToMany — depending on your schema
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(OrganizationContract::class);
    }

    public function applicationDocuments()
    {
        return $this->hasManyThrough(
            ApplicationDocument::class,
            Application::class,
            'organization_id', // applications.organization_id
            'application_id',  // application_documents.application_id
        );
    }


    /**
     * 手技動画（1対多）
     */
    public function procedureVideos(): HasMany
    {
        return $this->hasMany(ProcedureVideo::class);
    }

    // ──────────────────────────────────────────
    // アクセサ
    // ──────────────────────────────────────────

    /**
     * ステータスラベル取得
     */
    public function getContractStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->contract_status] ?? '不明';
    }

    /**
     * 請求対象かどうか
     */
    public function getBillableAttribute(): bool
    {
        return $this->contract_status !== self::STATUS_SPECIAL;
    }
   
    /**
     * 請求先メールアドレスを取得
     * 優先順位: 請求先(type=3) → 郵送先(type=2) → 所在地(type=1)
     *
     * ※ addresses リレーションが eager load 済みであれば追加クエリなし
     */
    public function getBillingEmailAttribute(): ?string
    {
        $addresses = $this->relationLoaded('addresses')
            ? $this->addresses
            : $this->addresses()->get();
    
        foreach ([
            OrganizationAddress::TYPE_BILLING,
            OrganizationAddress::TYPE_SHIPPING,
            OrganizationAddress::TYPE_LOCATION,
        ] as $type) {
            $email = $addresses->firstWhere('type', $type)?->email;
            if ($email) return $email;
        }
    
        return null;
    }
    // ──────────────────────────────────────────
    // スコープ
    // ──────────────────────────────────────────

    /**
     * 契約中のみ
     */
    public function scopeActive($query)
    {
        return $query->where('contract_status', self::STATUS_ACTIVE);
    }

    /**
     * 契約終了のみ
     */
    public function scopeEnded($query)
    {
        return $query->where('contract_status', self::STATUS_ENDED);
    }

    /**
     * 請求対象（特別枠以外）
     */
    public function scopeBillable($query)
    {
        return $query->where('contract_status', '!=', self::STATUS_SPECIAL);
    }
    /*
     * 契約メール　token作成   
     */
    public function generateRegisterToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->update(['register_token' => $token]);
        return $token;
    }
    /**
     * 名前で検索（Vue組織検索用）
     */
    public function scopeSearch($query, string $keyword)
    {
        return $query->where('name', 'like', "%{$keyword}%")
                     ->orWhere('abbr', 'like', "%{$keyword}%");
    }

    public function getInvoiceAmountAttribute(): int
    {
        $contract = $this->contract;
        if (!$contract) return 0;

        if ($this->contract_status === self::STATUS_PERSONAL) {
            return $contract->personal_fee * $this->members()->count();
        }

        return $contract->corporate_fee;
    }


    // ──────────────────────────────────────────
    // 契約日更新（クラウドサイン・Stripe・銀行振込共通）
    // organization_contractsのended_atがnullの場合のみ更新する
    // ──────────────────────────────────────────
 
    /**
     * @param bool $updateLicenseIssuedAt クラウドサインの場合はtrue
     */
    public function updateContractDate(bool $updateLicenseIssuedAt = false): void
    {
        // new_contract_date が未設定の場合は何もしない
        if (!$this->new_contract_date) {
            \Log::warning('Organization::updateContractDate: new_contract_date が未設定です', [
                'organization_id' => $this->id,
            ]);
            return;
        }
 
        // 現在有効な organization_contracts を取得
        $contract = \App\Models\OrganizationContract::where('organization_id', $this->id)
            ->whereNull('ended_at')
            ->orderByDesc('created_at')
            ->first();
 
        // ended_at が既に設定済み → 既に更新済みのためスキップ
        if (!$contract) {
            \Log::info('Organization::updateContractDate: 有効なcontractが見つかりません（既に更新済みの可能性）', [
                'organization_id' => $this->id,
            ]);
            return;
        }
 
        // organization_contracts の ended_at を設定（このサイクル終了）
        $contract->update(['ended_at' => now()->toDateString()]);
 
        // organizations の日付を更新
        $updates = [
            'contract_date'     => $this->new_contract_date,
            'new_contract_date' => \Carbon\Carbon::parse($this->new_contract_date)->addYear()->toDateString(),
        ];
 
        // クラウドサインの場合のみ license_issued_at も更新
        if ($updateLicenseIssuedAt) {
            $updates['license_issued_at'] = $this->new_contract_date;
        }
 
        $this->update($updates);
        
        // tier自動判定（tier1・2のみ、tier3・4は引き継ぎ）
        $this->recalculateTier();
 
        \Log::info('Organization::updateContractDate: 契約日を更新しました', [
            'organization_id'   => $this->id,
            'contract_date'     => $updates['contract_date'],
            'new_contract_date' => $updates['new_contract_date'],
            'license_issued_at' => $updates['license_issued_at'] ?? null,
        ]);

    }
    /**
     * 契約更新時のtier自動判定
     * case_reportsの通算件数で判定する。自動判定は昇格のみ(降格は管理画面で手動対応)
     */
    private function recalculateTier(): void
    {
        // 新期間の履歴を追加(tier関係なく必要)
        $this->addNewTierHistory();

        // 通算症例報告数を集計(将来、集計期間を絞る場合はここのクエリ条件を変更する)
        $totalCaseCount = \App\Models\CaseReport::where('organization_id', $this->id)
            // ->where('submitted_at', '>=', now()->subYears(5)) // 将来「直近5年」に絞る場合はこの行を有効化
            ->count();

        $newTier = $this->calculateTierFromCaseCount($totalCaseCount);

        // 自動判定は昇格のみ(降格させない。手動変更は管理画面で対応)
        if ($newTier > $this->tier) {
            $oldTier = $this->tier;
            $this->update(['tier' => $newTier]);

            \Log::info('Organization::recalculateTier: tier自動昇格', [
                'organization_id'  => $this->id,
                'total_case_count' => $totalCaseCount,
                'old_tier'         => $oldTier,
                'new_tier'         => $newTier,
            ]);
        }
    }

    /**
     * 通算症例報告数からTierを判定する
     * Tier1(ブロンズ): 基準なし
     * Tier2(シルバー): 通算100件
     * Tier3(ゴールド): 通算300件
     * Tier4(プラチナ): 通算1,000件
     */
    private function calculateTierFromCaseCount(int $totalCaseCount): int
    {
        return match (true) {
            $totalCaseCount >= 1000 => self::TIER_MASTER,
            $totalCaseCount >= 300  => self::TIER_EXPERT,
            $totalCaseCount >= 100  => self::TIER_ADVANCE,
            default                 => self::TIER_BASIC,
        };
    }
 
    /**
     * 新しい契約期間の履歴レコードを追加
     */
    private function addNewTierHistory(): void
    {
        OrganizationTierHistory::create([
            'organization_id' => $this->id,
            'period_start'    => $this->contract_date,
            'period_end'      => \Carbon\Carbon::parse($this->new_contract_date)->subDay()->toDateString(),
            'case_count'      => 0,
            'tier'            => $this->tier,
        ]);
 
        \Log::info('Organization::addNewTierHistory: 新期間履歴追加', [
            'organization_id' => $this->id,
            'period_start'    => $this->contract_date,
            'period_end'      => \Carbon\Carbon::parse($this->new_contract_date)->subDay()->toDateString(),
        ]);
    }
    /**
     * 症例報告登録時に呼ぶ: organization_tier_historiesを再計算し、
     * organizations.tierがそれ以上の場合のみ反映する(手動設定を尊重)
     */
    public function syncTierFromHistory(): void
    {
        $currentHistory = $this->tierHistories()->first();

        if (!$currentHistory) {
            return;
        }

        $totalCaseCount = \App\Models\CaseReport::where('organization_id', $this->id)->count();
        $calculatedTier = $this->calculateTierFromCaseCount($totalCaseCount);

        $currentHistory->update([
            'case_count' => $totalCaseCount,
            'tier'       => $calculatedTier,
        ]);

        if ($this->tier < $calculatedTier) {
            return;
        }

        if ($this->tier !== $calculatedTier) {
            $this->update(['tier' => $calculatedTier]);
        }
    }
    
    // リレーション追加
    public function tierHistories(): HasMany
    {
        return $this->hasMany(OrganizationTierHistory::class)->orderBy('period_start');
    }
    
    public function currentTierHistory(): HasOne
    {
        return $this->hasOne(OrganizationTierHistory::class)
                    ->where('period_start', '<=', today())
                    ->where('period_end', '>=', today())
                    ->orderByDesc('period_start');
    }
    
    // Tier定数
    const TIER_BASIC    = 1;
    const TIER_ADVANCE  = 2;
    const TIER_EXPERT   = 3;
    const TIER_MASTER   = 4;
    
    const TIER_LABELS = [
        self::TIER_BASIC   => 'ベーシック',
        self::TIER_ADVANCE => 'アドバンス',
        self::TIER_EXPERT  => 'エキスパート',
        self::TIER_MASTER  => 'マスター',
    ];
    
    // アクセサ
    public function getTierLabelAttribute(): string
    {
        return self::TIER_LABELS[$this->tier] ?? 'ベーシック';
    }

}
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
        'license_issued_at',
        // 'tier', ← 削除（変更点1：Tierはmemberに移動）
    ];

    protected $casts = [
        'contract_no'     => 'integer',
        'contract_status' => 'integer',
        'contract_date'   => 'date',
        'payment_method'  => 'integer',
        'new_contract_date' => 'date',
        'license_issued_at' => 'date',
        // 'tier' => 'integer', ← 削除
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
            'organization_id',
            'application_id',
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

        // クラウドサインの場合のみ license_issued_at も更新対象になるが、
        // 既に値が入っている（＝再契約）場合は上書きしない。
        // 新規契約で初めて値が入る時だけセットする。
        if ($updateLicenseIssuedAt && !$this->license_issued_at) {
            $updates['license_issued_at'] = $this->new_contract_date;
        }

        $this->update($updates);

        // Tier自動判定（変更点1：Organizationからは削除。
        // 契約更新に伴い、所属する全memberのTierを再計算する）
        foreach ($this->members as $member) {
            $member->recalculateTier();
        }

        \Log::info('Organization::updateContractDate: 契約日を更新しました', [
            'organization_id'   => $this->id,
            'contract_date'     => $updates['contract_date'],
            'new_contract_date' => $updates['new_contract_date'],
            'license_issued_at' => $updates['license_issued_at'] ?? null,
        ]);

    }

    // ──────────────────────────────────────────
    // ↓↓↓ 以下、Tier関連（変更点1によりMemberへ移動済み。削除）↓↓↓
    //
    // 削除したメソッド：
    //   - recalculateTier()
    //   - calculateTierFromCaseCount()
    //   - addNewTierHistory()
    //   - syncTierFromHistory()
    //   - tierHistories()
    //   - currentTierHistory()
    //   - getTierLabelAttribute()
    // 削除した定数：
    //   - TIER_BASIC / TIER_ADVANCE / TIER_EXPERT / TIER_MASTER
    //   - TIER_LABELS
    // これらは全て Member.php に移植済み。
    // ──────────────────────────────────────────

    // ──────────────────────────────────────────
    // 契約前e-ラーニング（変更点4）
    // ──────────────────────────────────────────

    /**
     * 所属する全ての先生が契約前の簡易e-ラーニングを受講済みかどうか。
     * この結果がtrueの場合のみ契約申込メールを送信できる。
     * memberが1件も居ない場合はfalse。
     */
    public function allMembersCompletedElearning(): bool
    {
        $members = $this->members;

        if ($members->isEmpty()) {
            return false;
        }

        return $members->every(fn (\App\Models\Member $member) => $member->hasCompletedElearning());
    }

    /**
     * 未受講の先生一覧（管理画面での進捗確認用）
     */
    public function membersWithIncompleteElearning(): \Illuminate\Support\Collection
    {
        return $this->members->reject(fn (\App\Models\Member $member) => $member->hasCompletedElearning());
    }

}

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
    ];

    protected $casts = [
        'contract_no'     => 'integer',
        'contract_status' => 'integer',
        'contract_date'   => 'date',
        'payment_method'  => 'integer',
        'new_contract_date' => 'date',
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
 
        \Log::info('Organization::updateContractDate: 契約日を更新しました', [
            'organization_id'   => $this->id,
            'contract_date'     => $updates['contract_date'],
            'new_contract_date' => $updates['new_contract_date'],
            'license_issued_at' => $updates['license_issued_at'] ?? null,
        ]);
    }

}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    // ──────────────────────────────────────────
    // 定数
    // ──────────────────────────────────────────

    const STATUS_ACTIVE    = 1; // 通常
    const STATUS_SUSPENDED = 2; // 休会
    const STATUS_WITHDRAWN = 3; // 退会

    const STATUS_LABELS = [
        self::STATUS_ACTIVE    => '通常',
        self::STATUS_SUSPENDED => '休会',
        self::STATUS_WITHDRAWN => '退会',
    ];

    const GENDER_MALE   = 'male';
    const GENDER_FEMALE = 'female';
    const GENDER_OTHER  = 'other';

    const GENDER_LABELS = [
        self::GENDER_MALE   => '男性',
        self::GENDER_FEMALE => '女性',
        self::GENDER_OTHER  => 'その他',
    ];

    // ──────────────────────────────────────────
    // テーブル設定
    // ──────────────────────────────────────────

    protected $table = 'members';

    protected $fillable = [
        'organization_id',
        'member_number',
        'doctor_number',
        'position',
        'last_name',
        'first_name',
        'last_name_kana',
        'first_name_kana',
        'gender',
        'birthdate',
        'tel',
        'mobile',
        'fax',
        'email',
        'personal_email',
        'status_id',
        'member_type',
        'joined_at',
        'withdrawn_at',
    ];

    protected $casts = [
        'birthdate'    => 'date',
        'joined_at'    => 'date',
        'withdrawn_at' => 'date',
        'status_id'    => 'integer',
    ];

    protected $appends = [
        'full_name',
        'full_name_kana',
        'status_label',
        'gender_label',
    ];

    // ──────────────────────────────────────────
    // リレーション
    // ──────────────────────────────────────────

    /**
     * 所属組織
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * ログインユーザー
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
    
    public function attempts(): HasMany
    {
        return $this->hasMany(ElearningAttempt::class, 'member_id');
    }
    /**
     * 住所（自宅・送付先）
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(MemberAddress::class);
    }

    /**
     * 自宅住所
     */
    public function homeAddress(): HasOne
    {
        return $this->hasOne(MemberAddress::class)
                    ->where('type', MemberAddress::TYPE_HOME);
    }

    /**
     * 送付先住所
     */
    public function shippingAddress(): HasOne
    {
        return $this->hasOne(MemberAddress::class)
                    ->where('type', MemberAddress::TYPE_SHIPPING);
    }

    /**
     * 学歴
     */
    public function educations(): HasMany
    {
        return $this->hasMany(MemberEducation::class);
    }

    /**
     * 取得学位
     */
    public function degrees(): HasMany
    {
        return $this->hasMany(MemberDegree::class);
    }

    /**
     * 学会役職歴
     */
    public function roles(): HasMany
    {
        return $this->hasMany(MemberRole::class);
    }

    /**
     * 学会委員歴
     */
    public function committees(): HasMany
    {
        return $this->hasMany(MemberCommittee::class);
    }

    /**
     * PDFアップロード
     */
    public function pdfUploads(): HasMany
    {
        return $this->hasMany(PdfUpload::class);
    }

    /**
     * レポート
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /**
     * 銀行口座
     */
    public function bankAccount(): HasOne
    {
        return $this->hasOne(BankAccount::class);
    }

    // ──────────────────────────────────────────
    // アクセサ
    // ──────────────────────────────────────────

    /**
     * 氏名（姓＋名）
     */
    public function getFullNameAttribute(): string
    {
        return collect([$this->last_name, $this->first_name])
            ->filter()
            ->implode('　');
    }

    /**
     * 氏名かな（せい＋めい）
     */
    public function getFullNameKanaAttribute(): string
    {
        return collect([$this->last_name_kana, $this->first_name_kana])
            ->filter()
            ->implode('　');
    }

    /**
     * 会員状況ラベル
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status_id] ?? '不明';
    }

    /**
     * 性別ラベル
     */
    public function getGenderLabelAttribute(): string
    {
        return self::GENDER_LABELS[$this->gender] ?? '';
    }

    // ──────────────────────────────────────────
    // スコープ
    // ──────────────────────────────────────────

    /**
     * 通常会員のみ
     */
    public function scopeActive($query)
    {
        return $query->where('status_id', self::STATUS_ACTIVE);
    }

    /**
     * 退会済みのみ
     */
    public function scopeWithdrawn($query)
    {
        return $query->where('status_id', self::STATUS_WITHDRAWN);
    }

    /**
     * 氏名・メールで検索
     */
    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('last_name', 'like', "%{$keyword}%")
              ->orWhere('first_name', 'like', "%{$keyword}%")
              ->orWhere('last_name_kana', 'like', "%{$keyword}%")
              ->orWhere('first_name_kana', 'like', "%{$keyword}%")
              ->orWhere('email', 'like', "%{$keyword}%")
              ->orWhere('member_number', 'like', "%{$keyword}%")
              ->orWhere('doctor_number', 'like', "%{$keyword}%");
        });
    }

    /**
     * 組織に所属しているメンバー
     */
    public function scopeOfOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}

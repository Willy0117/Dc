<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Models\ElearningInvitation;


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

    // Tier定数（変更点1：Organizationから移動）
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

    // ──────────────────────────────────────────
    // テーブル設定
    // ──────────────────────────────────────────

    protected $table = 'members';

    protected $fillable = [
        'organization_id',
        'member_number',
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
        'tier',              // 追加
        'doctor_group_id',   // 追加：変更点3(名寄せ)・8(合算)用
    ];

    protected $casts = [
        'birthdate'    => 'date',
        'joined_at'    => 'date',
        'withdrawn_at' => 'date',
        'status_id'    => 'integer',
        'tier'         => 'integer', // 追加
    ];

    protected $appends = [
        'full_name',
        'full_name_kana',
        'status_label',
        'gender_label',
        'tier_label', // 追加
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

    /**
     * 症例報告（変更点9：症例報告はmember主体）
     */
    public function caseReports(): HasMany
    {
        return $this->hasMany(CaseReport::class);
    }

    /**
     * Tier履歴（変更点1：Organizationから移動）
     */
    public function tierHistories(): HasMany
    {
        return $this->hasMany(MemberTierHistory::class)->orderBy('period_start');
    }

    public function currentTierHistory(): HasOne
    {
        return $this->hasOne(MemberTierHistory::class)
                    ->where('period_start', '<=', today())
                    ->where('period_end', '>=', today())
                    ->orderByDesc('period_start');
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

    /**
     * Tierラベル（変更点1：Organizationから移動）
     */
    public function getTierLabelAttribute(): string
    {
        return self::TIER_LABELS[$this->tier] ?? 'ベーシック';
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
              ->orWhere('member_number', 'like', "%{$keyword}%");
        });
    }

    /**
     * 組織に所属しているメンバー
     */
    public function scopeOfOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    // ──────────────────────────────────────────
    // Tier自動判定（変更点1：Organizationから移動）
    // ──────────────────────────────────────────

    /**
     * 自分と同じ doctor_group_id を持つ全member（名寄せグループ）。
     * 掛け持ちなしの場合は自分1件のみを返す。
     */
    public function doctorGroupMembers()
    {
        return static::where('doctor_group_id', $this->doctor_group_id)->get();
    }

    /**
     * 契約更新時のtier自動判定
     * case_reportsの通算件数（doctor_group単位で合算）で判定する。
     * 自動判定は昇格のみ(降格は管理画面で手動対応)
     */
    public function recalculateTier(): void
    {
        $this->addNewTierHistory();

        $totalCaseCount = $this->getTotalCaseCountForDoctorGroup();

        $newTier = $this->calculateTierFromCaseCount($totalCaseCount);

        if ($newTier > $this->tier) {
            $oldTier = $this->tier;
            $this->update(['tier' => $newTier]);

            \Log::info('Member::recalculateTier: tier自動昇格', [
                'member_id'        => $this->id,
                'doctor_group_id'  => $this->doctor_group_id,
                'total_case_count' => $totalCaseCount,
                'old_tier'         => $oldTier,
                'new_tier'         => $newTier,
            ]);
        }
    }

    /**
     * 通算症例報告数からTierを判定する
     * Tier1(ベーシック): 基準なし
     * Tier2(アドバンス): 通算100件
     * Tier3(エキスパート): 通算300件
     * Tier4(マスター): 通算1,000件
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
     * 変更点8：同一doctor_group_id（＝名寄せ済みの同一医師）に属する
     * 全memberの症例報告を合算してカウントする。
     */
    private function getTotalCaseCountForDoctorGroup(): int
    {
        $memberIds = static::where('doctor_group_id', $this->doctor_group_id)
            ->pluck('id');

        return \App\Models\CaseReport::whereIn('member_id', $memberIds)->count();
    }

    /**
     * 新しい契約期間の履歴レコードを追加
     * 契約期間はこのmemberが所属するorganizationの契約日を参照する
     */
    private function addNewTierHistory(): void
    {
        MemberTierHistory::create([
            'member_id'    => $this->id,
            'period_start' => $this->organization->contract_date ?? now(),
            'period_end'   => $this->organization?->new_contract_date
                ? \Carbon\Carbon::parse($this->organization->new_contract_date)->subDay()->toDateString()
                : null,
            'case_count'   => 0,
            'tier'         => $this->tier,
        ]);

        \Log::info('Member::addNewTierHistory: 新期間履歴追加', [
            'member_id' => $this->id,
        ]);
    }

    /**
     * 症例報告登録時に呼ぶ: member_tier_historiesを再計算し、
     * members.tierがそれ以上の場合のみ反映する(手動設定を尊重)
     * 変更点8：合算件数はdoctor_group全体で共通のため、Tierも
     * グループ内の他memberに同期する。
     */
    public function syncTierFromHistory(): void
    {
        $currentHistory = $this->tierHistories()->first();

        if (!$currentHistory) {
            return;
        }

        $totalCaseCount = $this->getTotalCaseCountForDoctorGroup();
        $calculatedTier = $this->calculateTierFromCaseCount($totalCaseCount);

        $currentHistory->update([
            'case_count' => $totalCaseCount,
            'tier'       => $calculatedTier,
        ]);

        if ($this->tier < $calculatedTier) {
            // 自分自身は昇格処理へ委ねる（手動降格は行わない）
        } elseif ($this->tier !== $calculatedTier) {
            $this->update(['tier' => $calculatedTier]);
        }

        // doctor_group内の他memberにもTierを同期
        static::where('doctor_group_id', $this->doctor_group_id)
            ->where('id', '!=', $this->id)
            ->where('tier', '<', $calculatedTier)
            ->update(['tier' => $calculatedTier]);
    }
    /**
     * この先生宛のe-ラーニング受講案内（複数回送られる可能性を考慮しhasMany）
     */
    public function elearningInvitations(): HasMany
    {
        return $this->hasMany(ElearningInvitation::class)->orderByDesc('created_at');
    }

    /**
     * 直近の受講案内（最新の1件）
     */
    public function latestElearningInvitation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ElearningInvitation::class)->latestOfMany();
    }

    /**
     * 受講済みかどうか（直近の案内が完了しているか）
     */
    /**
     * 受講済みかどうか。
     * 変更点：自分自身の履歴だけでなく、doctor_group_id（氏名名寄せで統合済みの
     * 同一先生グループ）全体のいずれかが受講済みであればtrueとする。
     * これにより「別施設で既に受講済みの先生を、新しい施設に追加登録した際に
     * 二重で受講案内メールを送らない」という判定が可能になる。
     * doctor_group_idが未設定（＝まだ名寄せされていない単独の先生）の場合は、
     * 自分自身のmember_idのみで判定する。
     */
    public function hasCompletedElearning(): bool
    {
        if ($this->doctor_group_id) {
            $memberIds = static::where('doctor_group_id', $this->doctor_group_id)->pluck('id');
        } else {
            // 未名寄せ（単独）の場合は自分自身のみで判定
            $memberIds = [$this->id];
        }

        return ElearningInvitation::whereIn('member_id', $memberIds)
            ->whereNotNull('completed_at')
            ->exists();
    }
}
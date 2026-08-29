<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class ElearningAttempt extends Model
{
    protected $table = 'elearning_attempts';

    const PASS_THRESHOLD = 12; // 15問中12問以上で合格

    protected $fillable = [
        'user_id',
        'member_id',
        'organization_id',
        'total_questions',
        'correct_count',
        'is_passed',
        'started_at',
        'submitted_at',
        'period_key',
    ];

    protected $casts = [
        'is_passed'    => 'boolean',
        'started_at'   => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ElearningAttemptAnswer::class, 'attempt_id');
    }

    public function scopePassed($query)
    {
        return $query->where('is_passed', true);
    }

    public function scopeInPeriod($query, string $periodKey)
    {
        return $query->where('period_key', $periodKey);
    }

    /**
     * 契約開始日を基準に、指定日時点の「半年ごとの期間キー」を算出する
     * 例: contract_date = 2025-08-09 の場合
     *   2025-08-09 ～ 2026-02-08 → "2025-08-09_1"
     *   2026-02-09 ～ 2026-08-08 → "2025-08-09_2"
     */
    public static function calculatePeriodKey(Carbon $contractDate, ?Carbon $target = null): string
    {
        $target = $target ?? now();
        $contractDate = $contractDate->copy()->startOfDay();

        if ($target->lt($contractDate)) {
            return $contractDate->toDateString() . '_0';
        }

        $periodStart = $contractDate->copy();
        $index = 0;

        while (true) {
            $periodEnd = $periodStart->copy()->addMonths(6);
            if ($target->lt($periodEnd)) {
                break;
            }
            $periodStart = $periodEnd;
            $index++;
        }

        return $contractDate->toDateString() . '_' . $index;
    }

    /**
     * 同一先生（doctor_group_idが一致するMember全員）の中で、
     * 過去に一度でも合格した実績があるかどうか（期間は問わない）
     *
     * 変更点2（医師番号取得不可）に伴い、doctor_numberベースの判定から
     * doctor_group_id（変更点3の氏名名寄せで統合されたグループ）ベースの
     * 判定に置き換えた。
     * doctor_group_idが未設定（＝掛け持ちなし・単独）の場合は、
     * 自分自身のmember_idのみで判定する。
     */
    public static function hasPassedByDoctorGroup(?int $doctorGroupId, int $fallbackMemberId): bool
    {
        if (empty($doctorGroupId)) {
            return static::where('member_id', $fallbackMemberId)->passed()->exists();
        }

        return static::whereHas('member', function ($q) use ($doctorGroupId) {
                $q->where('doctor_group_id', $doctorGroupId);
            })
            ->passed()
            ->exists();
    }
}
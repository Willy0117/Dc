<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'video_set_id', 'customer_name', 'affiliation', 'phone', 'customer_email',
        'membership_status', 'occupation', 'occupation_other', 'bed_count',
        'stripe_checkout_session_id', 'stripe_payment_intent',
        'status', 'access_token', 'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function videoSet(): BelongsTo
    {
        return $this->belongsTo(VideoSet::class);
    }

    public function videoViews(): HasMany
    {
        return $this->hasMany(VideoView::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * このセット内の全動画を視聴完了しているか（管理画面等の参考用。テスト解放条件には使わない）
     */
    public function hasCompletedAllVideos(): bool
    {
        $totalVideos = $this->videoSet->videos()->count();
        $completedCount = $this->videoViews()->whereNotNull('completed_at')->count();

        return $totalVideos > 0 && $completedCount >= $totalVideos;
    }

    /**
     * いずれか1つでも動画のテストに合格しているか
     */
    public function hasPassedAnyQuiz(): bool
    {
        return $this->quizAttempts()->where('passed', true)->exists();
    }
}

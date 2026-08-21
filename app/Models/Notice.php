<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notice extends Model
{
    protected $table = 'notices';

    const TARGET_ALL               = 'all';               // 全員(病院+先生)
    const TARGET_ORGANIZATIONS_ALL = 'organizations_all';  // 病院のみ(全病院)
    const TARGET_MEMBERS_ALL       = 'members_all';        // 先生のみ(全先生)
    const TARGET_ORGANIZATIONS     = 'organizations';      // 個別病院
    const TARGET_MEMBERS           = 'members';            // 個別先生

    const TARGET_LABELS = [
        self::TARGET_ALL               => '全員(病院・先生)',
        self::TARGET_ORGANIZATIONS_ALL => '病院のみ(全病院)',
        self::TARGET_MEMBERS_ALL       => '先生のみ(全先生)',
        self::TARGET_ORGANIZATIONS     => '個別病院を指定',
        self::TARGET_MEMBERS           => '個別先生を指定',
    ];

    protected $fillable = [
        'title',
        'body',
        'youtube_url',
        'video_available_from',
        'video_available_until',
        'target_type',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'video_available_from'  => 'datetime',
        'video_available_until' => 'datetime',
        'published_at'          => 'datetime',
    ];

    // ──────────────────────────────────────────
    // リレーション
    // ──────────────────────────────────────────

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'notice_organization');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'notice_member');
    }

    public function views(): HasMany
    {
        return $this->hasMany(NoticeView::class);
    }

    // ──────────────────────────────────────────
    // アクセサ
    // ──────────────────────────────────────────

    public function getYoutubeIdAttribute(): ?string
    {
        if ($this->youtube_url && preg_match('/(?:youtu\.be\/|v=|embed\/)([a-zA-Z0-9_-]{6,15})/', $this->youtube_url, $m)) {
            return $m[1];
        }
        return null;
    }

    public function getEmbedUrlAttribute(): ?string
    {
        $id = $this->youtube_id;
        return $id ? "https://www.youtube.com/embed/{$id}" : null;
    }

    public function getIsVideoAvailableAttribute(): bool
    {
        if (!$this->youtube_url) {
            return false;
        }

        $now = now();

        if ($this->video_available_from && $now->lt($this->video_available_from)) {
            return false;
        }

        if ($this->video_available_until && $now->gt($this->video_available_until)) {
            return false;
        }

        return true;
    }

    public function getTargetTypeLabelAttribute(): string
    {
        return self::TARGET_LABELS[$this->target_type] ?? '不明';
    }

    // ──────────────────────────────────────────
    // スコープ
    // ──────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
                      ->where('published_at', '<=', now());
    }

    public function scopeOrderedLatest($query)
    {
        return $query->orderByDesc('published_at');
    }

    /**
     * ログイン中のUserに配信すべきお知らせだけに絞り込む
     */
    public function scopeVisibleTo($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            // 全員向け
            $q->where('target_type', self::TARGET_ALL);

            if ($user->type === 1) {
                // 病院アカウント
                $q->orWhere('target_type', self::TARGET_ORGANIZATIONS_ALL);

                if ($user->organization_id) {
                    $q->orWhere(function ($qq) use ($user) {
                        $qq->where('target_type', self::TARGET_ORGANIZATIONS)
                           ->whereHas('organizations', function ($q3) use ($user) {
                               $q3->where('organizations.id', $user->organization_id);
                           });
                    });
                }
            }

            if ($user->type === 2) {
                // 先生アカウント
                $q->orWhere('target_type', self::TARGET_MEMBERS_ALL);

                if ($user->member_id) {
                    $q->orWhere(function ($qq) use ($user) {
                        $qq->where('target_type', self::TARGET_MEMBERS)
                           ->whereHas('members', function ($q3) use ($user) {
                               $q3->where('members.id', $user->member_id);
                           });
                    });

                    $organizationId = $user->member?->organization_id;

                    if ($organizationId) {
                        $q->orWhere(function ($qq) use ($organizationId) {
                            $qq->where('target_type', self::TARGET_ORGANIZATIONS)
                               ->whereHas('organizations', function ($q3) use ($organizationId) {
                                   $q3->where('organizations.id', $organizationId);
                               });
                        });
                    }
                }
            }
        });
    }
}

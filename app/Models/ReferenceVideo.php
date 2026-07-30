<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferenceVideo extends Model
{
    protected $table = 'reference_videos';

    const CATEGORIES = ['全体', '手', '足', '肘', '肩', '膝'];

    protected $fillable = [
        'category',
        'title',
        'youtube_url',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'sort_order'  => 'integer',
        'is_required' => 'boolean',
    ];

    public function views(): HasMany
    {
        return $this->hasMany(ReferenceVideoView::class);
    }

    // ──────────────────────────────────────────
    // アクセサ：YouTube動画IDを抽出
    // ──────────────────────────────────────────
    public function getYoutubeIdAttribute(): ?string
    {
        if (preg_match('/(?:youtu\.be\/|v=|embed\/)([a-zA-Z0-9_-]{6,15})/', $this->youtube_url, $m)) {
            return $m[1];
        }
        return null;
    }

    public function getEmbedUrlAttribute(): ?string
    {
        $id = $this->youtube_id;
        return $id ? "https://www.youtube.com/embed/{$id}" : null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        $id = $this->youtube_id;
        return $id ? "https://img.youtube.com/vi/{$id}/mqdefault.jpg" : null;
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('category')->orderBy('sort_order');
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }
}

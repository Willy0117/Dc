<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Video extends Model
{
    protected $fillable = [
        'video_set_id', 'title', 'speaker_name', 'duration_minutes',
        'vimeo_id', 'vimeo_hash', 's3_key', 'sort_order',
    ];

    public function videoSet(): BelongsTo
    {
        return $this->belongsTo(VideoSet::class);
    }

    /**
     * この動画(講義)専用の設問
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    /**
     * 埋め込み用のVimeoプレイヤーURL（限定公開動画は h パラメータが必要）
     */
    public function embedUrl(): string
    {
        $url = "https://player.vimeo.com/video/{$this->vimeo_id}?api=1&title=0&byline=0&portrait=0";
        if ($this->vimeo_hash) {
            $url .= "&h={$this->vimeo_hash}";
        }
        return $url;
    }
}

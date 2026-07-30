<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = ['video_set_id', 'video_id', 'question_text', 'explanation', 'is_multiple', 'sort_order'];

    public function choices(): HasMany
    {
        return $this->hasMany(Choice::class)->orderBy('sort_order');
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
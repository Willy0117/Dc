<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferenceVideoCategory extends Model
{
    protected $table = 'reference_video_categories';

    protected $fillable = [
        'name',
        'required_tier',
        'sort_order',
    ];

    protected $casts = [
        'required_tier' => 'integer',
        'sort_order'    => 'integer',
    ];

    public function videos(): HasMany
    {
        return $this->hasMany(ReferenceVideo::class, 'category_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * 指定したグレード以下の先生でも視聴できるカテゴリーのみ
     */
    public function scopeAvailableForTier($query, int $tier)
    {
        return $query->where('required_tier', '<=', $tier);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferenceVideoCategory extends Model
{
    protected $table = 'reference_video_categories';

    protected $fillable = [
        'name',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function videos(): HasMany
    {
        return $this->hasMany(ReferenceVideo::class, 'category_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}

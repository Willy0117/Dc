<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseReportCategory extends Model
{
    protected $table = 'case_report_categories';

    protected $fillable = [
        'name',
        'required_tier',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'required_tier' => 'integer',
        'sort_order'    => 'integer',
        'is_active'     => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 指定したグレード以下の先生でも選択できるカテゴリーのみ
     */
    public function scopeAvailableForTier($query, int $tier)
    {
        return $query->where('required_tier', '<=', $tier);
    }
}

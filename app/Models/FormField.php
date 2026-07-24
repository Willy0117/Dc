<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormField extends Model
{
    protected $fillable = [
        'treatment_area',
        'field_name',
        'field_type',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(FormOption::class)->orderBy('sort_order');
    }

    public function activeOptions(): HasMany
    {
        return $this->hasMany(FormOption::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByArea($query, string $area)
    {
        return $query->where('treatment_area', $area);
    }
}

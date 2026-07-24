<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CaseReport extends Model
{
    protected $fillable = [
        'organization_id',
        'facility_name_raw',
        'patient_gender',
        'patient_age_group',
        'treatment_area',
        'complications',
        'complication_types',
        'notes',
        'submitted_at',
        'csv_row',
    ];

    protected $casts = [
        'submitted_at'       => 'datetime',
        'complication_types' => 'array',
    ];

    // ──────────────────────────────────────────
    // リレーション
    // ──────────────────────────────────────────

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function detail(): HasOne
    {
        return $this->hasOne(CaseReportDetail::class);
    }

    // ──────────────────────────────────────────
    // スコープ
    // ──────────────────────────────────────────
    public function scopeByArea($query, string $area)
    {
        return $query->where('treatment_area', $area);
    }

    public function scopeCurrentPeriod($query, Organization $organization)
    {
        $history = $organization->currentTierHistory;
        if (!$history) return $query;

        return $query->whereBetween('submitted_at', [
            $history->period_start,
            $history->period_end . ' 23:59:59',
        ]);
    }
}

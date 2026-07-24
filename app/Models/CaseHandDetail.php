<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseHandDetail extends Model
{
    protected $fillable = [
        'case_report_id',
        'puncture_vessel',
        'puncture_vessels',
        'disease_name',
        'disease_names',
        'right_pain_areas',
        'right_pain_area_list',
        'left_pain_areas',
        'left_pain_area_list',
    ];

    protected $casts = [
        'puncture_vessels'     => 'array',
        'disease_names'        => 'array',
        'right_pain_area_list' => 'array',
        'left_pain_area_list'  => 'array',
    ];

    public function caseReport(): BelongsTo
    {
        return $this->belongsTo(CaseReport::class);
    }
}

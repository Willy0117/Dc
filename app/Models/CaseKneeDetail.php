<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseKneeDetail extends Model
{
    protected $fillable = [
        'case_report_id',
        'puncture_vessel',
        'puncture_vessels',
        'disease_name',
        'disease_names',
        'tourniquet_position',
    ];

    protected $casts = [
        'puncture_vessels' => 'array',
        'disease_names'    => 'array',
    ];

    public function caseReport(): BelongsTo
    {
        return $this->belongsTo(CaseReport::class);
    }
}

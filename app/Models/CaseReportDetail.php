<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseReportDetail extends Model
{
    protected $fillable = [
        'case_report_id',
        'treatment_area',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function caseReport(): BelongsTo
    {
        return $this->belongsTo(CaseReport::class);
    }

    // フィールド値を取得
    public function get(string $fieldName): mixed
    {
        return $this->data[$fieldName] ?? null;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RehabApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'facility',
        'age',
        'gender',
        'visit_type',
        'diagnosis',
        'current_history',
        'past_history',
        'rehab',
        'future_plan',
        'recommendation_pdf',
        'clinical_report_1',
        'clinical_report_2',
        'status',
        'reject_message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

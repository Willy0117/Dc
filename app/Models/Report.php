<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'is_detailed',
        'member_id',
        'facility_name',
        'age',
        'gender',
        'visit_type',
        'diagnosis',
        'current_history',
        'past_history',
        'rehab_program',
        'future_plan',
        'supervisor',
        'body_build',
        'findings_assessment',
    ];
    //
}

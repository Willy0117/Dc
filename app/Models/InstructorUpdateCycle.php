<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstructorUpdateCycle extends Model
{
    protected $fillable = [
        'member_id',
        'instructor_no',
        'exam_round',
        'start_date',
        'end_date',
        'status',
        'reason',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}

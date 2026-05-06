<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'member_id',
        'gender',
        'last_name',
        'first_name',
        'birthdate',
        'status',
    ];

    protected $appends = [
        'full_name',
    ];
    

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function documents()
    {
        return $this->hasMany(ExamDocument::class);
    }

     /* =====================
     |  表示用ラベル
     * ===================== */
    public function getFullNameAttribute()
    {
        return collect([
            $this->last_name,
            $this->first_name,
        ])->filter()->implode('');
    }

    //
}

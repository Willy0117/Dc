<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamDocument extends Model
{
    protected $fillable = [
        'exam_id',
        'type',
        'file_path',
        'thumbnail_path',
    ];
    
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }    
    //
}

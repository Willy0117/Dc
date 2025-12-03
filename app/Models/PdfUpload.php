<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdfUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'file_path',
        'thumbnail_path',
        'credit_category_id',
        'credit_conference_id',
        'credit_role_id',
        'session',
        'points',
        'status',
        'rejection_message',
    ];

    public function member() { return $this->belongsTo(Member::class); }
    public function creditCategory() { return $this->belongsTo(CreditCategory::class); }
    public function creditConference() { return $this->belongsTo(CreditConference::class); }
    public function creditRole() { return $this->belongsTo(CreditRolePoint::class, 'credit_role_id'); }
}



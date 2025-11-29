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
        'category',
        'role',
        'organization_name',
        'status',
        'rejection_message',
        'unit',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}

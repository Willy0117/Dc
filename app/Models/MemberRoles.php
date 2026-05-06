<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberRole extends Model
{
    use HasFactory;

    protected $table = 'member_roles';

    protected $fillable = [
        'member_id',
        'role_name',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * 会員（親）
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
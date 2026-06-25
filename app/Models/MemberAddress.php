<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberAddress extends Model
{
    protected $fillable = [
        'member_id', 'type',
        'postal_code',
        'address1', 'address2', 'address3',
        'tel', 'fax',
    ];

    protected $casts = [
        'type' => 'integer',
    ];

    const TYPE_HOME     = 1; // 自宅
    const TYPE_SHIPPING = 2; // 送付先

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
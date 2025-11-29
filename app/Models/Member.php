<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'member_code', // 会員ID
        'name',        // 氏名
        'postal_code', // 郵便番号
        'address1',    // 住所1
        'address2',    // 住所2
        'phone',       // 電話番号
        'fax',       // fax
        'organization_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
   // Organization とのリレーション
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'billing_name',
        'billing_postal',
        'billing_address',
        'contact_person',
        'contact_email',
        'contact_phone',
        'registration_number',
    ];

    // 会員との多対多
    public function members()
    {
        return $this->belongsToMany(Member::class)
                    ->withPivot('role')
                    ->withTimestamps();
    }
}


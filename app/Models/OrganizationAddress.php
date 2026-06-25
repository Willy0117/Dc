<?php

// app/Models/OrganizationAddress.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationAddress extends Model
{
    protected $fillable = [
        'organization_id',
        'type',
        'name',
        'postal_code',
        'address1',
        'address2',
        'address3',
        'tel',
        'fax',
        'email',
    ];

    protected $casts = [
        'type' => 'integer',
    ];

    const TYPE_LOCATION = 1; // 所在地
    const TYPE_SHIPPING = 2; // 郵送先
    const TYPE_BILLING  = 3; // 請求先

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}

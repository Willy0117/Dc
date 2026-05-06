<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationContact extends Model
{
    protected $fillable = [
        'organization_id',
        'type', // 1:代表者, 2:担当者
        'last_name',
        'first_name',
        'last_name_kana',
        'first_name_kana',
        'email',
        'tel',
        'fax',
        'mobile',
    ];

    protected $appends = ['full_name'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->last_name . ' ' . $this->first_name);
    }
}

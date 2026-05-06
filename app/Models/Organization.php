<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = [
        'member_id',
        'type',
        'position',
        'name',
        'name_kana',
        'name_prefix',
        'name_suffix',
        'postal_code',
        'address1',
        'address2',
        'address3',
        'last_name',
        'first_name',
        'tel',
        'mobile',
        'fax',
        'email',
        'allow_text_color',
        'allow_background_color',
    ];

    /* ===== 表示用 ===== */

    public function getFullNameAttribute()
    {
        return trim(
            ($this->name_prefix ?? '') .
            ($this->name ?? '') .
            ($this->name_suffix ?? '')
        );
    }

    public function documents()
    {
        return $this->hasMany(OrganizationDocument::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function getFullAddressAttribute()
    {
        return collect([
            $this->address1,
            $this->address2,
            $this->address3,
        ])->filter()->implode('');
    }

    public function getContactNameAttribute()
    {
        return collect([
            $this->last_name,
            $this->first_name,
        ])->filter()->implode(' ');
    }
}


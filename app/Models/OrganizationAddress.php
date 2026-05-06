<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationAddress extends Model
{
    protected $fillable = [
        'organization_id',
        'type',
        'postal',
        'address1',
        'address2',
        'address3',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function documents()
    {
        return $this->hasMany(OrganizationAddressDocument::class);
    }
    
    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address1,
            $this->address2,
            $this->address3,
        ])
        ->filter(fn ($v) => filled($v)) // null, '' を除外
        ->implode('');
    }    
}

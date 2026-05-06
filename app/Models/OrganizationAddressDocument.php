<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OrganizationAddressDocument extends Model
{
    protected $fillable = [
        'organization_address_id',
        'path',
        'thumbnail_path',
    ];

    protected $appends = ['url', 'thumbnail_url'];

    public function address()
    {
        return $this->belongsTo(OrganizationAddress::class, 'organization_address_id');
    }

    public function getUrlAttribute()
    {
        return $this->path ? Storage::url($this->path) : null;
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail_path ? Storage::url($this->thumbnail_path) : null;
    }
}

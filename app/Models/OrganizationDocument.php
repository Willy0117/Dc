<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OrganizationDocument extends Model
{
    protected $table = 'organization_documents';
    
    protected $fillable = [
        'organization_id',
        'type', // 1:history_certificate 2:
        'file_path',
        'thumbnail_path',
    ];

    protected $appends = ['url', 'thumbnail_url'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
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

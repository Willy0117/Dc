<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Services\FileService;

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
        return $this->path 
            ? app(FileService::class)->getUrl($this->path) 
            : null;
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail_path 
            ? app(FileService::class)->getUrl($this->thumbnail_path) 
            : null;
    }
}

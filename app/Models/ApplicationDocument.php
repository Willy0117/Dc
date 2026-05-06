<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ApplicationDocument extends Model
{
    protected $table = 'application_documents';
    
    protected $fillable = [
        'application_id',
        'type', 
        'file_path',
        'thumbnail_path',
    ];

    protected $appends = [
        'url', 'thumbnail_url'
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function getUrlAttribute()
    {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail_path ? Storage::url($this->thumbnail_path) : null;
    }
}

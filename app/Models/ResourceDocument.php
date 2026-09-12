<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceDocument extends Model
{
    protected $table = 'resource_documents';

    protected $fillable = [
        'category_id',
        'title',
        'required_tier',
        'file_path',
        'original_filename',
        'file_size',
        'uploaded_by',
        'sort_order',
    ];

    protected $casts = [
        'required_tier' => 'integer',
        'file_size'      => 'integer',
        'sort_order'     => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ResourceDocumentCategory::class, 'category_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('created_at');
    }

    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->original_filename ?? $this->file_path, PATHINFO_EXTENSION));
    }
}
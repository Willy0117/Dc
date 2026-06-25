<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    protected $fillable = [
        'organization_id',
        'cloudsign_document_id',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    // ステータス定数
    const STATUS_PENDING   = 0; // 申込中
    const STATUS_SENT      = 1; // 送信済
    const STATUS_COMPLETED = 2; // 締結完了
    const STATUS_CANCELED  = 3; // 取り消し

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function contractDocument()
    {
        return $this->documents()->where('type', ApplicationDocument::TYPE_CONTRACT)->latest()->first();
    }
}
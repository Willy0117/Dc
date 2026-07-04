<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Services\FileService;

class ApplicationDocument extends Model
{
    protected $fillable = [
        'application_id',
        'type',
        'file_path',
        'thumbnail_path',
    ];

    protected $casts = [
        'type' => 'integer',
    ];

    // タイプ定数
    const TYPE_CONTRACT = 1; // 契約書PDF（送信前の自社生成版）
    const TYPE_SIGNED    = 2; // 締結済みPDF（クラウドサインからの最終版）
    const TYPE_AGREEMENT        = 3; // 合意書PDF（送信前）
    const TYPE_AGREEMENT_SIGNED = 4; // 合意書PDF（締結済み）

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    // ストレージURL取得
    public function getUrlAttribute(): string
    {
        return app(FileService::class)->getUrl($this->file_path);
    }

    // 絶対パス取得
    public function getFullPathAttribute(): string
    {
        return Storage::disk(config('filesystems.default'))->path($this->file_path);
    }

    // サムネイルURL取得
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path 
            ? app(FileService::class)->getUrl($this->thumbnail_path) 
            : null;
    }
}
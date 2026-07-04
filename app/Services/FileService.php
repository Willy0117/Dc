<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    protected $disk;

    public function __construct(?string $disk = null)
    {
        $this->disk = $disk ?? config('filesystems.default');
    }

    /**
     * ファイル保存
     */
    public function storeUploadedFile(UploadedFile $file, string $dir): array
    {
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs($dir, $filename, $this->disk);
        $thumbnail = $this->createThumbnail($file->getRealPath(), $dir, $filename);

        return [$path, $thumbnail];
    }

    /**
     * base64画像保存
     */
    public function storeBase64Image(string $base64, string $dir): array
    {
        if (str_contains($base64, ',')) {
            $base64 = explode(',', $base64)[1];
        }

        $data = base64_decode($base64);
        $filename = Str::uuid().'.png';
        $path = $dir.'/'.$filename;

        Storage::disk($this->disk)->put($path, $data);

        // 一時ファイルに書き出してサムネイル生成
        $tmpPath = sys_get_temp_dir().'/'.$filename;
        file_put_contents($tmpPath, $data);
        $thumbnail = $this->createThumbnail($tmpPath, $dir, $filename);
        @unlink($tmpPath);

        return [$path, $thumbnail];
    }

    // ──────────────────────────────────────────
    // URL取得（(共通）
    // ──────────────────────────────────────────

    /**
     * URLを取得（S3は署名付きURL、ローカルは通常URL）
     */
    public function getUrl(string $path, int $minutes = 30): string
    {
        if ($this->disk === 's3') {
            return Storage::disk('s3')->temporaryUrl($path, now()->addMinutes($minutes));
        }

        return Storage::url($path);
    }

    /**
     * ファイル削除
     */
    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    /**
     * サムネイル生成（ローカル一時ファイルを使うのでS3対応）
     */
    public function createThumbnail(string $localPath, string $dir, string $filename): ?string
    {
        try {
            if (!class_exists('Imagick')) return null;

            $thumbDir = $dir.'/thumbnails';
            $thumbName = pathinfo($filename, PATHINFO_FILENAME).'_thumb.png';
            $thumbPath = $thumbDir.'/'.$thumbName;

            // 一時ファイルにサムネイルを生成
            $tmpThumbPath = sys_get_temp_dir().'/'.$thumbName;

            $imagick = new \Imagick();
            $ext = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));

            if ($ext === 'pdf') {
                $imagick->setResolution(150, 150);
                $imagick->readImage($localPath.'[0]');
            } else {
                $imagick->readImage($localPath);
            }

            $imagick->setImageFormat('png');
            $imagick->thumbnailImage(150, 150, true);
            $imagick->writeImage($tmpThumbPath);
            $imagick->clear();
            $imagick->destroy();

            // S3またはローカルに保存
            Storage::disk($this->disk)->put($thumbPath, file_get_contents($tmpThumbPath));
            @unlink($tmpThumbPath);

            return $thumbPath;

        } catch (\Exception $e) {
            \Log::error('Thumbnail error: '.$e->getMessage());
            return null;
        }
    }
    
    /**
     * S3からファイルを一時ファイルに落としてパスを返す
     */
    public function getLocalTempPath(string $s3Key): string
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'pdf_');
        file_put_contents($tmpPath, Storage::disk($this->disk)->get($s3Key));
        return $tmpPath;
    }

}
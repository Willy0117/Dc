<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Imagick;

class FileService
{
    protected $disk;

    public function __construct()
    {
        $this->disk = config('filesystems.default');
    }

    /**
     * ファイル保存 + サムネイル
     */
    public function storeUploadedFile(UploadedFile $file, string $dir): array
    {
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();

        $path = $file->storeAs($dir, $filename, $this->disk);

        $thumbnail = $this->createThumbnail($path, $dir);

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

        $thumbnail = $this->createThumbnail($path, $dir);

        return [$path, $thumbnail];
    }

    /**
     * サムネイル生成
     */
    public function createThumbnail(string $path, string $dir): ?string
    {
        try {
            $fullPath = Storage::disk($this->disk)->path($path);

            $thumbDir = $dir.'/thumbnails';

            $thumbName = pathinfo($path, PATHINFO_FILENAME).'_thumb.png';

            $thumbPath = $thumbDir.'/'.$thumbName;

            $thumbFullPath = Storage::disk($this->disk)->path($thumbPath);

            if (!file_exists(dirname($thumbFullPath))) {
                mkdir(dirname($thumbFullPath), 0755, true);
            }

            $imagick = new Imagick();

            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

            if ($ext === 'pdf') {
                $imagick->setResolution(150,150);
                $imagick->readImage($fullPath.'[0]');
            } else {
                $imagick->readImage($fullPath);
            }

            $imagick->setImageFormat('png');

            $imagick->thumbnailImage(150, 150, true);

            $imagick->writeImage($thumbFullPath);

            $imagick->clear();
            $imagick->destroy();

            return $thumbPath;
        } catch (\Exception $e) {
            \Log::error('Thumbnail error: '.$e->getMessage());
            return null;
        }
    }
}
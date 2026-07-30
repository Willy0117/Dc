<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
    public function index(Request $request)
    {
        $fileService = app(FileService::class);
        $keyword     = $request->input('keyword', '');
        $directory   = $request->input('directory', 'all');
        $dateFrom    = $request->input('date_from', '');
        $dateTo      = $request->input('date_to', '');

        $directories = $directory !== 'all' ? [$directory] : ['contracts', 'invoices'];

        $files = [];
        foreach ($directories as $dir) {
            $list = Storage::disk(config('filesystems.default'))->files($dir);
            foreach ($list as $path) {
                $name     = basename($path);
                $modified = date('Y/m/d H:i', Storage::disk(config('filesystems.default'))->lastModified($path));
                $modifiedDate = date('Y-m-d', Storage::disk(config('filesystems.default'))->lastModified($path));

                // キーワードフィルタ
                if ($keyword && !str_contains($name, $keyword)) continue;

                // 日付フィルタ
                if ($dateFrom && $modifiedDate < $dateFrom) continue;
                if ($dateTo   && $modifiedDate > $dateTo)   continue;

                $files[] = [
                    'path'      => $path,
                    'name'      => $name,
                    'directory' => $dir,
                    'url'       => $fileService->getUrl($path),
                    'size'      => Storage::disk(config('filesystems.default'))->size($path),
                    'modified'  => $modified,
                ];
            }
        }

        // 更新日降順
        usort($files, fn($a, $b) => $b['modified'] <=> $a['modified']);

        return inertia('Admin/Storage/Index', [
            'files'   => $files,
            'filters' => [
                'keyword'   => $keyword,
                'directory' => $directory,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ],
        ]);
    }
}
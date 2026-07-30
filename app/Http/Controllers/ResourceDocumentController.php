<?php

namespace App\Http\Controllers;

use App\Models\ResourceDocumentCategory;
use App\Services\FileService;
use Inertia\Inertia;

class ResourceDocumentController extends Controller
{
    public function __construct(private FileService $fileService) {}

    // ──────────────────────────────────────────
    // 一覧（カテゴリー別にグルーピングして表示）
    // ──────────────────────────────────────────
    public function index()
    {
        $categories = ResourceDocumentCategory::ordered()
            ->with(['documents' => fn($q) => $q->ordered()])
            ->get()
            ->map(fn($cat) => [
                'id'   => $cat->id,
                'name' => $cat->name,
                'documents' => $cat->documents->map(fn($d) => [
                    'id'                => $d->id,
                    'title'             => $d->title,
                    'original_filename' => $d->original_filename,
                    'extension'         => $d->extension,
                    'file_size'         => $d->file_size,
                    'file_url'          => $this->fileService->getUrl($d->file_path),
                ]),
            ])
            ->filter(fn($cat) => $cat['documents']->isNotEmpty()) // 資料が1件もないカテゴリーは非表示
            ->values();

        return Inertia::render('ResourceDocuments/Index', [
            'categories' => $categories,
        ]);
    }
}

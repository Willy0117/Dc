<?php

namespace App\Http\Controllers;

use App\Models\ResourceDocumentCategory;
use App\Services\FileService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResourceDocumentController extends Controller
{
    public function __construct(private FileService $fileService) {}

    // ──────────────────────────────────────────
    // 一覧（カテゴリー別にグルーピングして表示）
    // 変更点：ログイン中の先生のグレード未満の資料は、
    // 一覧に含めない（見せない）。
    // 病院(organization)ログインの場合は、常にベーシック(Tier1)の
    // 資料のみ表示する（グレードという概念を持たないため、
    // 最も制限の強い状態で扱う）。
    // ──────────────────────────────────────────
    public function index(Request $request)
    {
        $user = $request->user();
        // 2:先生(member)ログインなら自分のグレード、
        // それ以外(病院ログイン)は常にベーシック(1)扱いにする
        $effectiveTier = ((int) $user->type === 2)
            ? ($user->member?->tier ?? 1)
            : 1;

        $categories = ResourceDocumentCategory::ordered()
            ->with(['documents' => fn($q) => $q->ordered()])
            ->get()
            ->map(fn($cat) => [
                'id'   => $cat->id,
                'name' => $cat->name,
                'documents' => $cat->documents
                    // 自分のグレード（病院ログインは常にベーシック扱い）未満の資料は除外
                    ->filter(fn($d) => $d->required_tier <= $effectiveTier)
                    ->map(fn($d) => [
                        'id'                => $d->id,
                        'title'             => $d->title,
                        'original_filename' => $d->original_filename,
                        'extension'         => $d->extension,
                        'file_size'         => $d->file_size,
                        'file_url'          => $this->fileService->getUrl($d->file_path),
                    ])
                    ->values(),
            ])
            ->filter(fn($cat) => $cat['documents']->isNotEmpty()) // 資料が1件もないカテゴリーは非表示
            ->values();

        return Inertia::render('ResourceDocuments/Index', [
            'categories' => $categories,
        ]);
    }
}

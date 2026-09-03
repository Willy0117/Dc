<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResourceDocument;
use App\Models\ResourceDocumentCategory;
use App\Services\FileService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResourceDocumentController extends Controller
{
    public function __construct(private FileService $fileService) {}

    // ──────────────────────────────────────────
    // 一覧
    // ──────────────────────────────────────────
    public function index()
    {
        $categories = ResourceDocumentCategory::ordered()->withCount('documents')->get();

        $documents = ResourceDocument::ordered()->get()->map(fn($d) => [
            'id'                 => $d->id,
            'category_id'        => $d->category_id,
            'title'              => $d->title,
            'required_tier'      => $d->required_tier, // 追加
            'original_filename'  => $d->original_filename,
            'extension'          => $d->extension,
            'file_size'          => $d->file_size,
            'file_url'           => $this->fileService->getUrl($d->file_path),
            'created_at'         => $d->created_at->format('Y-m-d H:i'),
        ]);

        return Inertia::render('Admin/ResourceDocuments/Index', [
            'documents'  => $documents,
            'categories' => $categories,
            'tierLabels' => \App\Models\Member::TIER_LABELS, // 追加
        ]);
    }

    // ──────────────────────────────────────────
    // 保存（新規アップロード、カテゴリー必須）
    // ──────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'category_id'    => 'required|exists:resource_document_categories,id',
            'title'          => 'required|string|max:255',
            'required_tier'  => 'required|integer|in:1,2,3,4', // 追加
            'document'       => 'required|file|mimes:pdf,jpg,jpeg,doc,docx|max:51200', // 50MB, PDF/JPEG/Word
        ]);

        $file = $request->file('document');
        [$filePath] = $this->fileService->storeUploadedFile($file, 'resource_documents');

        $maxOrder = ResourceDocument::max('sort_order') ?? 0;

        ResourceDocument::create([
            'category_id'        => $request->category_id,
            'title'              => $request->title,
            'required_tier'      => $request->required_tier, // 追加
            'file_path'          => $filePath,
            'original_filename'  => $file->getClientOriginalName(),
            'file_size'          => $file->getSize(),
            'uploaded_by'        => $request->user()->id,
            'sort_order'         => $maxOrder + 1,
        ]);

        return redirect()->back()->with('success', '資料をアップロードしました。');
    }

    // ──────────────────────────────────────────
    // タイトル・カテゴリー更新
    // ──────────────────────────────────────────
    public function update(Request $request, ResourceDocument $resourceDocument)
    {
        $validated = $request->validate([
            'category_id'    => 'required|exists:resource_document_categories,id',
            'title'          => 'required|string|max:255',
            'required_tier'  => 'required|integer|in:1,2,3,4', // 追加
        ]);

        $resourceDocument->update($validated);

        return redirect()->back()->with('success', '資料を更新しました。');
    }

    // ──────────────────────────────────────────
    // 削除（S3実ファイルも削除）
    // ──────────────────────────────────────────
    public function destroy(ResourceDocument $resourceDocument)
    {
        $this->fileService->delete($resourceDocument->file_path);
        $resourceDocument->delete();

        return redirect()->back()->with('success', '資料を削除しました。');
    }

    // ──────────────────────────────────────────
    // 並べ替え
    // ──────────────────────────────────────────
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'required|integer|exists:resource_documents,id',
        ]);

        foreach ($validated['ids'] as $index => $id) {
            ResourceDocument::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['message' => '並び順を更新しました。']);
    }
}

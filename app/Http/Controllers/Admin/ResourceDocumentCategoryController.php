<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResourceDocumentCategory;
use Illuminate\Http\Request;

class ResourceDocumentCategoryController extends Controller
{
    // ──────────────────────────────────────────
    // 保存（新規）
    // ──────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $maxOrder = ResourceDocumentCategory::max('sort_order') ?? 0;

        ResourceDocumentCategory::create([
            ...$validated,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->back()->with('success', 'カテゴリーを追加しました。');
    }

    // ──────────────────────────────────────────
    // 更新
    // ──────────────────────────────────────────
    public function update(Request $request, ResourceDocumentCategory $resourceDocumentCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $resourceDocumentCategory->update($validated);

        return redirect()->back()->with('success', 'カテゴリーを更新しました。');
    }

    // ──────────────────────────────────────────
    // 削除（資料が紐づいている場合は不可）
    // ──────────────────────────────────────────
    public function destroy(ResourceDocumentCategory $resourceDocumentCategory)
    {
        if ($resourceDocumentCategory->documents()->exists()) {
            return redirect()->back()->withErrors([
                'error' => 'このカテゴリーには資料が登録されているため削除できません。先に資料を削除するか、別カテゴリーに移動してください。',
            ]);
        }

        $resourceDocumentCategory->delete();

        return redirect()->back()->with('success', 'カテゴリーを削除しました。');
    }

    // ──────────────────────────────────────────
    // 並べ替え
    // ──────────────────────────────────────────
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'required|integer|exists:resource_document_categories,id',
        ]);

        foreach ($validated['ids'] as $index => $id) {
            ResourceDocumentCategory::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['message' => '並び順を更新しました。']);
    }
}

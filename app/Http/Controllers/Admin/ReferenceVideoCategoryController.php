<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferenceVideoCategory;
use Illuminate\Http\Request;

class ReferenceVideoCategoryController extends Controller
{
    // ──────────────────────────────────────────
    // 保存（新規）
    // ──────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $maxOrder = ReferenceVideoCategory::max('sort_order') ?? 0;

        ReferenceVideoCategory::create([
            ...$validated,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->back()->with('success', 'カテゴリーを追加しました。');
    }

    // ──────────────────────────────────────────
    // 更新
    // ──────────────────────────────────────────
    public function update(Request $request, ReferenceVideoCategory $referenceVideoCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $referenceVideoCategory->update($validated);

        return redirect()->back()->with('success', 'カテゴリーを更新しました。');
    }

    // ──────────────────────────────────────────
    // 削除（動画が紐づいている場合は不可）
    // ──────────────────────────────────────────
    public function destroy(ReferenceVideoCategory $referenceVideoCategory)
    {
        if ($referenceVideoCategory->videos()->exists()) {
            return redirect()->back()->withErrors([
                'error' => 'このカテゴリーには動画が登録されているため削除できません。先に動画を削除するか、別カテゴリーに移動してください。',
            ]);
        }

        $referenceVideoCategory->delete();

        return redirect()->back()->with('success', 'カテゴリーを削除しました。');
    }

    // ──────────────────────────────────────────
    // 並べ替え
    // ──────────────────────────────────────────
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'required|integer|exists:reference_video_categories,id',
        ]);

        foreach ($validated['ids'] as $index => $id) {
            ReferenceVideoCategory::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['message' => '並び順を更新しました。']);
    }
}

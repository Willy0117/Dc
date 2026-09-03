<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseReportCategory;
use Illuminate\Http\Request;

class CaseReportCategoryController extends Controller
{
    // ──────────────────────────────────────────
    // 新規追加（例：「肩こり」「頭痛」）
    // ──────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:50|unique:case_report_categories,name',
            'required_tier' => 'nullable|integer|in:1,2,3,4',
        ]);

        $maxOrder = CaseReportCategory::max('sort_order') ?? 0;

        CaseReportCategory::create([
            'name'          => $validated['name'],
            'required_tier' => $validated['required_tier'] ?? 1,
            'sort_order'    => $maxOrder + 1,
            'is_active'     => true,
        ]);

        return back()->with('success', 'カテゴリーを追加しました。');
    }

    // ──────────────────────────────────────────
    // 更新（名称・グレード制限）
    // ──────────────────────────────────────────
    public function update(Request $request, CaseReportCategory $caseReportCategory)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:50|unique:case_report_categories,name,' . $caseReportCategory->id,
            'required_tier' => 'required|integer|in:1,2,3,4',
        ]);

        // 名称変更時は、既存のcase_reports/case_report_details/form_fieldsの
        // treatment_areaも一致するよう追随させる（名前で紐付いているため）
        $oldName = $caseReportCategory->name;
        $caseReportCategory->update($validated);

        if ($oldName !== $validated['name']) {
            \App\Models\CaseReport::where('treatment_area', $oldName)->update(['treatment_area' => $validated['name']]);
            \DB::table('case_report_details')->where('treatment_area', $oldName)->update(['treatment_area' => $validated['name']]);
            \DB::table('form_fields')->where('treatment_area', $oldName)->update(['treatment_area' => $validated['name']]);
        }

        return back()->with('success', 'カテゴリーを更新しました。');
    }

    // ──────────────────────────────────────────
    // 表示/非表示トグル（先生の選択肢から一時的に外す）
    // ──────────────────────────────────────────
    public function toggle(CaseReportCategory $caseReportCategory)
    {
        $caseReportCategory->update(['is_active' => !$caseReportCategory->is_active]);
        return back()->with('success', '更新しました。');
    }

    // ──────────────────────────────────────────
    // 削除（既に症例報告で使われている場合は削除不可）
    // ──────────────────────────────────────────
    public function destroy(CaseReportCategory $caseReportCategory)
    {
        $inUse = \App\Models\CaseReport::where('treatment_area', $caseReportCategory->name)->exists();

        if ($inUse) {
            return back()->withErrors([
                'error' => 'このカテゴリーは既に症例報告で使用されているため削除できません。表示/非表示の切り替えをご利用ください。',
            ]);
        }

        $caseReportCategory->delete();

        return back()->with('success', 'カテゴリーを削除しました。');
    }

    // ──────────────────────────────────────────
    // 並べ替え
    // ──────────────────────────────────────────
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'required|integer|exists:case_report_categories,id',
        ]);

        foreach ($validated['ids'] as $index => $id) {
            CaseReportCategory::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['message' => '並び順を更新しました。']);
    }
}

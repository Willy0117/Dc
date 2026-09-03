<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseReportCategory;
use App\Models\FormField;
use App\Models\FormOption;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FormFieldController extends Controller
{
    const FIELD_TYPES = ['checkbox', 'radio', 'text'];

    // 「共通」は先生が選ぶ治療部位ではなく、部位を問わない共通項目
    // （トラブル・合併症等）を管理するための特別な区分。
    // case_report_categoriesには含めず、ここで固定追加する。
    const COMMON_AREA = '共通';

    // ──────────────────────────────────────────
    // 一覧
    // 変更点：治療部位の選択肢を、ハードコードではなく
    // case_report_categoriesから動的に取得する（管理画面から
    // 追加された「肩こり」「頭痛」等も自動的にここに現れる）。
    // ──────────────────────────────────────────
    public function index(Request $request)
    {
        $categories = CaseReportCategory::orderBy('sort_order')->get(['id', 'name', 'required_tier']);
        $treatmentAreas = $categories->pluck('name')->push(self::COMMON_AREA)->toArray();

        $area = $request->input('treatment_area', $treatmentAreas[0] ?? '手');

        $fields = FormField::with(['options'])
            ->where('treatment_area', $area)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Admin/FormFields/Index', [
            'fields'         => $fields,
            'treatmentAreas' => $treatmentAreas,
            'categories'     => $categories, // 追加：グレード表示・カテゴリー管理用
            'fieldTypes'     => self::FIELD_TYPES,
            'currentArea'    => $area,
        ]);
    }

    // ──────────────────────────────────────────
    // 質問項目 追加
    // ──────────────────────────────────────────
    public function store(Request $request)
    {
        $validAreas = CaseReportCategory::pluck('name')->push(self::COMMON_AREA)->toArray();

        $validated = $request->validate([
            'treatment_area' => 'required|string|in:' . implode(',', $validAreas),
            'field_name'     => 'required|string|max:50',
            'field_type'     => 'required|in:checkbox,radio,text',
        ]);

        $maxOrder = FormField::where('treatment_area', $validated['treatment_area'])
            ->max('sort_order') ?? -1;

        FormField::create([
            'treatment_area' => $validated['treatment_area'],
            'field_name'     => $validated['field_name'],
            'field_type'     => $validated['field_type'],
            'sort_order'     => $maxOrder + 1,
            'is_active'      => true,
        ]);

        return back()->with('success', '質問項目を追加しました。');
    }

    // ──────────────────────────────────────────
    // 質問項目 表示/非表示トグル
    // ──────────────────────────────────────────
    public function toggle(FormField $formField)
    {
        $formField->update(['is_active' => !$formField->is_active]);
        return back()->with('success', '更新しました。');
    }

    // ──────────────────────────────────────────
    // 質問項目 削除
    // ──────────────────────────────────────────
    public function destroy(FormField $formField)
    {
        $formField->delete();
        return back()->with('success', '質問項目を削除しました。');
    }

    // ──────────────────────────────────────────
    // 選択肢 追加
    // ──────────────────────────────────────────
    public function storeOption(Request $request, FormField $formField)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:100',
        ]);

        $maxOrder = $formField->options()->max('sort_order') ?? -1;

        FormOption::create([
            'form_field_id'  => $formField->id,
            'treatment_area' => $formField->treatment_area,
            'field_name'     => $formField->field_name,
            'label'          => $validated['label'],
            'sort_order'     => $maxOrder + 1,
            'is_active'      => true,
        ]);

        return back()->with('success', '選択肢を追加しました。');
    }

    // ──────────────────────────────────────────
    // 選択肢 表示/非表示トグル
    // ──────────────────────────────────────────
    public function toggleOption(FormOption $formOption)
    {
        $formOption->update(['is_active' => !$formOption->is_active]);
        return back()->with('success', '更新しました。');
    }

    // ──────────────────────────────────────────
    // 選択肢 削除
    // ──────────────────────────────────────────
    public function destroyOption(FormOption $formOption)
    {
        $formOption->delete();
        return back()->with('success', '選択肢を削除しました。');
    }
}

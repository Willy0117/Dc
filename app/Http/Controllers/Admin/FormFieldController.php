<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormField;
use App\Models\FormOption;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FormFieldController extends Controller
{
    const TREATMENT_AREAS = ['手', '足', '肘', '肩', '膝', '共通'];
    const FIELD_TYPES     = ['checkbox', 'radio', 'text'];

    // ──────────────────────────────────────────
    // 一覧
    // ──────────────────────────────────────────
    public function index(Request $request)
    {
        $area = $request->input('treatment_area', '手');

        $fields = FormField::with(['options'])
            ->where('treatment_area', $area)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Admin/FormFields/Index', [
            'fields'         => $fields,
            'treatmentAreas' => self::TREATMENT_AREAS,
            'fieldTypes'     => self::FIELD_TYPES,
            'currentArea'    => $area,
        ]);
    }

    // ──────────────────────────────────────────
    // 質問項目 追加
    // ──────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'treatment_area' => 'required|in:手,足,肘,肩,膝,共通',
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ElearningQuestion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ElearningQuestionController extends Controller
{
    // ──────────────────────────────────────────
    // 一覧
    // ──────────────────────────────────────────
    public function index()
    {
        $questions = ElearningQuestion::orderByDesc('id')->get();

        return Inertia::render('Admin/Elearning/Questions/Index', [
            'questions' => $questions,
        ]);
    }

    // ──────────────────────────────────────────
    // 保存（新規）
    // ──────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $this->validateQuestion($request);

        ElearningQuestion::create($validated);

        return redirect()->back()->with('success', '問題を追加しました。');
    }

    // ──────────────────────────────────────────
    // 更新
    // ──────────────────────────────────────────
    public function update(Request $request, ElearningQuestion $elearningQuestion)
    {
        $validated = $this->validateQuestion($request);

        $elearningQuestion->update($validated);

        return redirect()->back()->with('success', '問題を更新しました。');
    }

    // ──────────────────────────────────────────
    // 削除
    // ──────────────────────────────────────────
    public function destroy(ElearningQuestion $elearningQuestion)
    {
        $elearningQuestion->delete();

        return redirect()->back()->with('success', '問題を削除しました。');
    }

    // ──────────────────────────────────────────
    // 出題対象のON/OFF切り替え
    // ──────────────────────────────────────────
    public function toggleActive(ElearningQuestion $elearningQuestion)
    {
        $elearningQuestion->update(['is_active' => !$elearningQuestion->is_active]);

        return redirect()->back()->with('success', '出題設定を更新しました。');
    }

    private function validateQuestion(Request $request): array
    {
        return $request->validate([
            // 追加：'main'(本試験) or 'simple'(契約前簡易テスト)
            'category'       => 'required|in:main,simple',
            'question'       => 'required|string|max:1000',
            'choice_a'       => 'required|string|max:255',
            'choice_b'       => 'required|string|max:255',
            'choice_c'       => 'required|string|max:255',
            'choice_d'       => 'required|string|max:255',
            'correct_answer' => 'required|in:A,B,C,D',
            'explanation'    => 'nullable|string|max:2000',
            'is_active'      => 'boolean',
        ]);
    }
}
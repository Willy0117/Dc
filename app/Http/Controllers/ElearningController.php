<?php

namespace App\Http\Controllers;

use App\Models\ElearningAttempt;
use App\Models\ElearningAttemptAnswer;
use App\Models\ElearningQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ElearningController extends Controller
{
    // ──────────────────────────────────────────
    // トップ画面（受験可否・過去の合格状況を表示）
    // ──────────────────────────────────────────
    public function index(Request $request)
    {
        $user = $request->user();
        $member = $user->member;

        if (!$member || !$member->organization) {
            return Inertia::render('Elearning/Index', [
                'isEligible' => false,
            ]);
        }

        $organization = $member->organization;
        $periodKey = ElearningAttempt::calculatePeriodKey($organization->contract_date);

        // 同一先生（doctor_group_id一致）が、期間を問わずどこかで合格していれば合格扱い
        $isPassed = ElearningAttempt::hasPassedByDoctorGroup($member->doctor_group_id, $member->id);

        $recentAttempts = ElearningAttempt::where('member_id', $member->id)
            ->inPeriod($periodKey)
            ->orderByDesc('id')
            ->get(['id', 'correct_count', 'total_questions', 'is_passed', 'submitted_at']);

        return Inertia::render('Elearning/Index', [
            'isEligible'     => true,
            'isPassed'       => $isPassed,
            'recentAttempts' => $recentAttempts,
            'periodLabel'    => $this->periodLabel($organization->contract_date, $periodKey),
        ]);
    }

    // ──────────────────────────────────────────
    // 受験開始（15問ランダム抽出してセッション作成）
    // ──────────────────────────────────────────
    public function start(Request $request)
    {
        $user = $request->user();
        $member = $user->member;

        if (!$member || !$member->organization) {
            return redirect()->route('elearning.index');
        }

        $organization = $member->organization;
        $periodKey = ElearningAttempt::calculatePeriodKey($organization->contract_date);

        $questions = ElearningQuestion::active()->main()->inRandomOrder()->limit(15)->get();

        if ($questions->count() < 15) {
            return back()->withErrors(['error' => '出題可能な問題数が不足しています。運営にお問い合わせください。']);
        }

        $attempt = DB::transaction(function () use ($user, $member, $organization, $periodKey, $questions) {
            $attempt = ElearningAttempt::create([
                'user_id'         => $user->id,
                'member_id'       => $member->id,
                'organization_id' => $organization->id,
                'total_questions' => $questions->count(),
                'period_key'      => $periodKey,
                'started_at'      => now(),
            ]);

            foreach ($questions->values() as $index => $q) {
                ElearningAttemptAnswer::create([
                    'attempt_id'  => $attempt->id,
                    'question_id' => $q->id,
                    'sort_order'  => $index + 1,
                ]);
            }

            return $attempt;
        });

        return redirect()->route('elearning.show', $attempt->id);
    }
    // ──────────────────────────────────────────
    // 受験画面（設問一覧表示）
    // 変更点：is_multiple（複数正解かどうか）を追加。
    // 正解の中身自体（correct_answer）はフロントへ渡さない。
    // ──────────────────────────────────────────
    public function show(Request $request, ElearningAttempt $attempt)
    {
        $this->authorizeAttempt($request, $attempt);
 
        if ($attempt->submitted_at) {
            return redirect()->route('elearning.result', $attempt->id);
        }
 
        $questions = $attempt->answers()
            ->with('question:id,question,choice_a,choice_b,choice_c,choice_d,correct_answer')
            ->orderBy('sort_order')
            ->get()
            ->map(fn($a) => [
                'sort_order'  => $a->sort_order,
                'question_id' => $a->question_id,
                'question'    => $a->question->question,
                'choices'     => [
                    'A' => $a->question->choice_a,
                    'B' => $a->question->choice_b,
                    'C' => $a->question->choice_c,
                    'D' => $a->question->choice_d,
                ],
                // 追加：正解がカンマ区切り（複数）かどうかだけをフロントに伝える。
                // 正解の中身自体は渡さない（cheat防止）
                'is_multiple' => str_contains($a->question->correct_answer, ','),
            ]);
 
        return Inertia::render('Elearning/Show', [
            'attemptId' => $attempt->id,
            'questions' => $questions,
        ]);
    }
    // ──────────────────────────────────────────
    // 回答提出・採点
    // 変更点：複数正解（correct_answerがカンマ区切りの場合）に対応。
    // フロント側は、単一選択の問題でも必ず配列（例: ['A']）で送ること。
    // ──────────────────────────────────────────
    public function submit(Request $request, ElearningAttempt $attempt)
    {
        $this->authorizeAttempt($request, $attempt);

        if ($attempt->submitted_at) {
            return redirect()->route('elearning.result', $attempt->id);
        }

        $validated = $request->validate([
            'answers'                 => 'required|array',
            'answers.*.question_id'   => 'required|integer|exists:elearning_questions,id',
            // 変更：単一文字ではなく配列で受け取る（単一正解の問題でも要素1件の配列で送ること）
            'answers.*.selected'      => 'required|array|min:1',
            'answers.*.selected.*'    => 'required|string|in:A,B,C,D',
        ]);

        DB::transaction(function () use ($attempt, $validated) {
            $correctCount = 0;

            $questionMap = ElearningQuestion::whereIn('id', collect($validated['answers'])->pluck('question_id'))
                ->get()
                ->keyBy('id');

            foreach ($validated['answers'] as $ans) {
                $question = $questionMap->get($ans['question_id']);

                // 選択された選択肢・正解、それぞれをソートして集合として比較する
                // （選んだ順番は問わない。過不足なく一致していれば正解）
                $selected = collect($ans['selected'])->map(fn ($s) => trim($s))->sort()->values();
                $correct  = collect(explode(',', $question->correct_answer ?? ''))
                    ->map(fn ($s) => trim($s))
                    ->sort()
                    ->values();

                $isCorrect = $question && $selected->all() === $correct->all();
                if ($isCorrect) $correctCount++;

                ElearningAttemptAnswer::where('attempt_id', $attempt->id)
                    ->where('question_id', $ans['question_id'])
                    ->update([
                        // 複数選択の場合もカンマ区切りの1文字列として保存（correct_answerと同じ形式）
                        'selected_answer' => $selected->implode(','),
                        'is_correct'      => $isCorrect,
                    ]);
            }

            $attempt->update([
                'correct_count' => $correctCount,
                'is_passed'     => $correctCount >= ElearningAttempt::PASS_THRESHOLD,
                'submitted_at'  => now(),
            ]);
        });

        return redirect()->route('elearning.result', $attempt->id);
    }   
// ──────────────────────────────────────────
    // 結果画面（正誤・解説表示）
    // 変更点：selected_answer・correct_answerを、カンマ区切り文字列から
    // 配列に変換してフロントへ渡す（複数正解対応）
    // ──────────────────────────────────────────
    public function result(Request $request, ElearningAttempt $attempt)
    {
        $this->authorizeAttempt($request, $attempt);

        if (!$attempt->submitted_at) {
            return redirect()->route('elearning.show', $attempt->id);
        }

        $answers = $attempt->answers()
            ->with('question')
            ->orderBy('sort_order')
            ->get()
            ->map(fn($a) => [
                'question'        => $a->question->question,
                'choices'         => [
                    'A' => $a->question->choice_a,
                    'B' => $a->question->choice_b,
                    'C' => $a->question->choice_c,
                    'D' => $a->question->choice_d,
                ],
                // 変更：カンマ区切り文字列 → 配列
                'selected_answers' => collect(explode(',', $a->selected_answer ?? ''))
                    ->map(fn ($s) => trim($s))->filter()->values(),
                'correct_answers'  => collect(explode(',', $a->question->correct_answer ?? ''))
                    ->map(fn ($s) => trim($s))->filter()->values(),
                'is_correct'      => $a->is_correct,
                'explanation'     => $a->question->explanation,
            ]);

        return Inertia::render('Elearning/Result', [
            'correctCount'    => $attempt->correct_count,
            'totalQuestions'  => $attempt->total_questions,
            'isPassed'        => $attempt->is_passed,
            'answers'         => $answers,
        ]);
    }

    // ──────────────────────────────────────────
    // 受験結果一覧
    // ──────────────────────────────────────────
    public function history(Request $request)
    {
        $user = $request->user();

        // 病院代表アカウント：所属する全先生分の受験結果一覧
        if ((int) $user->type === 1) {
            $organization = $user->organization;

            if (!$organization) {
                return Inertia::render('Elearning/History', ['isEligible' => false]);
            }

            $attempts = ElearningAttempt::whereHas('member', fn($q) => $q->where('organization_id', $organization->id))
                ->whereNotNull('submitted_at')
                ->with('member:id,last_name,first_name')
                ->orderByDesc('submitted_at')
                ->get()
                ->map(fn($a) => [
                    'id'              => $a->id,
                    'member_name'     => $a->member ? "{$a->member->last_name} {$a->member->first_name}" : '-',
                    'correct_count'   => $a->correct_count,
                    'total_questions' => $a->total_questions,
                    'is_passed'       => $a->is_passed,
                    'submitted_at'    => $a->submitted_at->format('Y-m-d H:i'),
                ]);

            return Inertia::render('Elearning/History', [
                'isEligible' => true,
                'viewType'   => 'organization',
                'attempts'   => $attempts,
            ]);
        }

        // 先生個人アカウント：自分自身の全期間分の受験結果一覧
        $member = $user->member;

        if (!$member) {
            return Inertia::render('Elearning/History', ['isEligible' => false]);
        }

        $attempts = ElearningAttempt::where('member_id', $member->id)
            ->whereNotNull('submitted_at')
            ->orderByDesc('submitted_at')
            ->get()
            ->map(fn($a) => [
                'id'              => $a->id,
                'correct_count'   => $a->correct_count,
                'total_questions' => $a->total_questions,
                'is_passed'       => $a->is_passed,
                'submitted_at'    => $a->submitted_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Elearning/History', [
            'isEligible' => true,
            'viewType'   => 'member',
            'attempts'   => $attempts,
        ]);
    }

    // ──────────────────────────────────────────
    // Private: 本人の受験セッションかチェック
    // ──────────────────────────────────────────
    private function authorizeAttempt(Request $request, ElearningAttempt $attempt): void
    {
        if ($attempt->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    // ──────────────────────────────────────────
    // Private: 期間の表示ラベル生成（例: 2026/02/09 〜 2026/08/08）
    // ──────────────────────────────────────────
    private function periodLabel($contractDate, string $periodKey): string
    {
        $index = (int) substr($periodKey, strrpos($periodKey, '_') + 1);
        $start = \Carbon\Carbon::parse($contractDate)->addMonths($index * 6);
        $end = $start->copy()->addMonths(6)->subDay();

        return $start->format('Y/m/d') . ' 〜 ' . $end->format('Y/m/d');
    }
}
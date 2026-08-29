<?php

namespace App\Http\Controllers;

use App\Models\ElearningInvitation;
use App\Models\ElearningQuestion;
use Illuminate\Http\Request;

/**
 * 契約前の「簡易e-ラーニング」専用Controller。
 * 変更点4：先生登録時に自動送信されるメールのリンク先。
 *
 * 既存の本試験（My Page / ElearningController・ElearningAttempt、
 * 15問出題）とは受験・採点の仕組みが別物だが、
 * 問題バンクは既存の ElearningQuestion を共用し、
 * category='simple' の問題だけを対象にする。
 *
 * 出題数：category='simple'の有効な問題が5問以下なら全部、
 *        6問以上ならランダム5問。
 * 合格ライン：全問正解。
 * ログイン不要、token付きURLでアクセスする。
 * 受講済みかどうかは ElearningInvitation.completed_at のみで判定する。
 */
class ElearningInvitationController extends Controller
{
    private const MAX_QUESTION_COUNT = 10;

    // ──────────────────────────────────────────
    // 受講画面表示
    // ──────────────────────────────────────────
    public function show(string $token)
    {
        $invitation = ElearningInvitation::with('member')
            ->where('token', $token)
            ->firstOrFail();

        $activeCount = ElearningQuestion::active()->simple()->count();

        // 5問以下なら全部、6問以上ならランダム5問
        $query = ElearningQuestion::active()->simple()->inRandomOrder();
        if ($activeCount > self::MAX_QUESTION_COUNT) {
            $query->limit(self::MAX_QUESTION_COUNT);
        }
        $questions = $query->get();

        return view('elearning-invitations.show', [
            'memberName'  => $invitation->member->full_name,
            'isCompleted' => $invitation->completed_at !== null,
            'completedAt' => $invitation->completed_at?->format('Y-m-d H:i'),
            'questions'   => $questions->map(fn (ElearningQuestion $q) => [
                'id'      => $q->id,
                'text'    => $q->question,
                'choices' => collect(['A', 'B', 'C', 'D'])
                    ->mapWithKeys(fn ($letter) => [$letter => $q->getChoiceAttribute($letter)])
                    ->filter(fn ($text) => !is_null($text) && $text !== ''),
            ])->values(),
            'token' => $token,
        ]);
    }

    // ──────────────────────────────────────────
    // 解答提出・採点（出題された全問正解で受講完了）
    // ──────────────────────────────────────────
    public function submit(Request $request, string $token)
    {
        $invitation = ElearningInvitation::where('token', $token)->firstOrFail();

        $validated = $request->validate([
            'question_ids'   => 'required|array',
            'question_ids.*' => 'required|integer|exists:elearning_questions,id',
            'answers'        => 'required|array',
            'answers.*'      => 'required|string|in:A,B,C,D',
        ]);

        // category='simple'の問題だけを対象に採点する
        // （question_idsがsimple以外を指していないことも念のため確認）
        $questions = ElearningQuestion::simple()
            ->whereIn('id', $validated['question_ids'])
            ->get()
            ->keyBy('id');

        $allCorrect = count($validated['question_ids']) === $questions->count();

        foreach ($validated['question_ids'] as $qid) {
            $question = $questions->get($qid);
            $selected = $validated['answers'][$qid] ?? null;

            if (!$question || $selected !== $question->correct_answer) {
                $allCorrect = false;
                break;
            }
        }

        if (!$allCorrect) {
            return back()->withErrors([
                'error' => '不正解の設問があります。もう一度お試しください。',
            ]);
        }

        // 先生単位（member_idに紐づくinvitation）で受講済みを記録する
        if (!$invitation->completed_at) {
            $invitation->update(['completed_at' => now()]);

            \Log::info('ElearningInvitationController: 簡易e-ラーニング受講完了', [
                'member_id'     => $invitation->member_id,
                'invitation_id' => $invitation->id,
            ]);

            // 変更点：受講完了に伴い、この先生のPWメールを送信する
            // （病院側は入金確認時に既に送信済み。先生はここが初回送信のタイミング）
            app(\App\Services\UserInviteService::class)
                ->sendMemberPasswordSetupMailIfEligible($invitation->member);
        }

        return redirect()->route('elearning-invitations.show', $token)
            ->with('info', '受講が完了しました。');
    }
}
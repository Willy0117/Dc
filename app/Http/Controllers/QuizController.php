<?php

namespace App\Http\Controllers;

use App\Mail\CertificateMail;
use App\Models\Certificate;
use App\Models\QuizAttempt;
use App\Models\Video;
use App\Support\CertificatePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class QuizController extends Controller
{
    /**
     * テスト受験ページ（動画=講義単位）。
     * ページを開くたびに進行状況をリセットし、1問目から開始する。
     */
    public function show(Request $request, string $token, Video $video)
    {
        $order = $request->attributes->get('order');

        $this->authorizeVideoBelongsToOrder($order, $video);

        $completed = $order->videoViews()
            ->where('video_id', $video->id)
            ->whereNotNull('completed_at')
            ->exists();

        abort_unless($completed, 403, '先にこの動画を視聴してください。');

        $alreadyPassed = $order->quizAttempts()
            ->where('video_id', $video->id)
            ->where('passed', true)
            ->exists();

        if ($alreadyPassed) {
            return redirect()
                ->route('watch.index', $token)
                ->with('info', 'このテストは既に合格済みです。証明書は動画一覧からダウンロードできます。');
        }

        $questions = $video->questions()->with('choices')->orderBy('sort_order')->get();

        abort_if($questions->isEmpty(), 404, 'この講義の設問はまだ登録されていません。');

        // 進行状況をリセット（毎回1問目から）
        session()->forget($this->answersKey($order, $video));
        session()->put($this->progressKey($order, $video), 0);

        $firstQuestion = $questions->first();

        return view('quiz.show', [
            'order' => $order,
            'video' => $video,
            'totalQuestions' => $questions->count(),
            'question' => $this->presentQuestion($firstQuestion, 0),
        ]);
    }

    /**
     * 1問分の解答を受け取り、正誤判定する。
     * 正解 → 次の設問（または合格確定）／ 不正解 → 即座に不合格確定。
     */
    public function answer(Request $request, string $token, Video $video)
    {
        $order = $request->attributes->get('order');

        $this->authorizeVideoBelongsToOrder($order, $video);

        $completed = $order->videoViews()
            ->where('video_id', $video->id)
            ->whereNotNull('completed_at')
            ->exists();

        abort_unless($completed, 403);

        $questions = $video->questions()->with('choices')->orderBy('sort_order')->get();
        $progressKey = $this->progressKey($order, $video);
        $answersKey = $this->answersKey($order, $video);

        $index = session()->get($progressKey);
        abort_if($index === null || $index >= $questions->count(), 400, '不正な状態です。テストページを開き直してください。');

        $question = $questions[$index];

        $submitted = $request->input('choices', []);
        $selectedIds = collect(is_array($submitted) ? $submitted : [$submitted])
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->map(fn ($v) => (int) $v)
            ->sort()
            ->values()
            ->all();

        $correctIds = $question->choices->where('is_correct', true)->pluck('id')->sort()->values()->all();
        $isCorrect = $selectedIds === $correctIds;

        $answers = session()->get($answersKey, []);
        $answers[$question->id] = $selectedIds;
        session()->put($answersKey, $answers);

        // 不正解 → 即座に不合格確定
        if (! $isCorrect) {
            $scorePercent = (int) round($index / max($questions->count(), 1) * 100);

            QuizAttempt::create([
                'order_id' => $order->id,
                'video_id' => $video->id,
                'score_percent' => $scorePercent,
                'passed' => false,
                'answers' => $answers,
            ]);

            session()->forget($progressKey);
            session()->forget($answersKey);

            return response()->json([
                'correct' => false,
                'finished' => true,
                'passed' => false,
                'score_percent' => $scorePercent,
            ]);
        }

        $nextIndex = $index + 1;

        // 最後の設問にも正解 → 合格確定・証明書発行（結果表示は解説確認後にフロント側で行う）
        if ($nextIndex >= $questions->count()) {
            $attempt = QuizAttempt::create([
                'order_id' => $order->id,
                'video_id' => $video->id,
                'score_percent' => 100,
                'passed' => true,
                'answers' => $answers,
            ]);

            $this->issueCertificate($order, $video, $attempt);

            session()->forget($progressKey);
            session()->forget($answersKey);

            return response()->json([
                'correct' => true,
                'finished' => true,
                'passed' => true,
                'explanation' => $question->explanation,
                'certificate_download_url' => route('certificate.download', [$order->access_token, $video->id]),
            ]);
        }

        // 次の設問へ（解説文も一緒に返し、フロント側で解説確認後に次の設問を表示させる）
        session()->put($progressKey, $nextIndex);
        $nextQuestion = $questions[$nextIndex];

        return response()->json([
            'correct' => true,
            'finished' => false,
            'explanation' => $question->explanation,
            'question' => $this->presentQuestion($nextQuestion, $nextIndex),
        ]);
    }

    protected function presentQuestion($question, int $index): array
    {
        return [
            'index' => $index,
            'id' => $question->id,
            'question_text' => $question->question_text,
            'is_multiple' => (bool) $question->is_multiple,
            'choices' => $question->choices->map(fn ($c) => [
                'id' => $c->id,
                'choice_text' => $c->choice_text,
            ])->values(),
        ];
    }

    protected function progressKey($order, Video $video): string
    {
        return "quiz_progress_{$order->id}_{$video->id}";
    }

    protected function answersKey($order, Video $video): string
    {
        return "quiz_answers_{$order->id}_{$video->id}";
    }

    protected function authorizeVideoBelongsToOrder($order, Video $video): void
    {
        abort_unless($video->video_set_id === $order->video_set_id, 404);
    }

    protected function issueCertificate($order, Video $video, QuizAttempt $attempt): void
    {
        $certificateNumber = 'LIC-' . now()->format('Y') . '-'
            . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) . '-'
            . str_pad((string) $video->id, 3, '0', STR_PAD_LEFT);

        $pdf = CertificatePdf::render('certificate.pdf', [
            'order' => $order,
            'video' => $video,
            'attempt' => $attempt,
            'certificateNumber' => $certificateNumber,
            'issuedAt' => now(),
        ]);

        $relativePath = "certificates/{$certificateNumber}.pdf";
        Storage::disk('local')->put($relativePath, $pdf->output());

        $certificate = Certificate::create([
            'order_id' => $order->id,
            'video_id' => $video->id,
            'quiz_attempt_id' => $attempt->id,
            'certificate_number' => $certificateNumber,
            'issued_at' => now(),
            'pdf_path' => $relativePath,
        ]);

        Mail::to($order->customer_email)->send(new CertificateMail($order, $video, $certificate));
    }
}
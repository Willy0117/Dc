<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Video;
use App\Models\VideoSet;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:video-sets.update'),
        ];
    }

    /**
     * 設問＋選択肢をまとめて保存（動画=講義単位。choicesは配列、is_correctは1件のみtrueを想定）
     */
    public function store(Request $request, VideoSet $videoSet, Video $video)
    {
        abort_unless($video->video_set_id === $videoSet->id, 404);

        $data = $this->validated($request);

        DB::transaction(function () use ($video, $data) {
            $question = $video->questions()->create([
                'video_set_id' => $video->video_set_id,
                'question_text' => $data['question_text'],
                'is_multiple' => $data['is_multiple'] ?? false,
                'sort_order' => $video->questions()->max('sort_order') + 1,
            ]);

            foreach ($data['choices'] as $i => $choice) {
                $question->choices()->create([
                    'choice_text' => $choice['choice_text'],
                    'is_correct' => (bool) ($choice['is_correct'] ?? false),
                    'sort_order' => $i + 1,
                ]);
            }
        });

        return back()->with('success', '設問を追加しました。');
    }

    public function update(Request $request, VideoSet $videoSet, Video $video, Question $question)
    {
        abort_unless($video->video_set_id === $videoSet->id && $question->video_id === $video->id, 404);

        $data = $this->validated($request);

        DB::transaction(function () use ($question, $data) {
            $question->update([
                'question_text' => $data['question_text'],
                'is_multiple' => $data['is_multiple'] ?? false,
            ]);

            // シンプルにするため、更新のたびに選択肢を作り直す
            $question->choices()->delete();
            foreach ($data['choices'] as $i => $choice) {
                $question->choices()->create([
                    'choice_text' => $choice['choice_text'],
                    'is_correct' => (bool) ($choice['is_correct'] ?? false),
                    'sort_order' => $i + 1,
                ]);
            }
        });

        return back()->with('success', '設問を更新しました。');
    }

    public function destroy(VideoSet $videoSet, Video $video, Question $question)
    {
        abort_unless($video->video_set_id === $videoSet->id && $question->video_id === $video->id, 404);

        $question->delete();

        return back()->with('success', '設問を削除しました。');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'question_text' => ['required', 'string'],
            'is_multiple' => ['boolean'],
            'choices' => ['required', 'array', 'min:2'],
            'choices.*.choice_text' => ['required', 'string', 'max:255'],
            'choices.*.is_correct' => ['boolean'],
        ]);
    }
}

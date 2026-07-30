<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\VideoSet;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class VideoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:video-sets.update'),
        ];
    }

    public function store(Request $request, VideoSet $videoSet)
    {
        $data = $this->validated($request);
        $data['sort_order'] = $videoSet->videos()->max('sort_order') + 1;

        $videoSet->videos()->create($data);

        return back()->with('success', '動画を追加しました。');
    }

    public function update(Request $request, VideoSet $videoSet, Video $video)
    {
        $video->update($this->validated($request));

        return back()->with('success', '動画情報を更新しました。');
    }

    public function destroy(VideoSet $videoSet, Video $video)
    {
        $video->delete();

        return back()->with('success', '動画を削除しました。');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'vimeo_id' => ['required', 'string', 'max:50'],
            'vimeo_hash' => ['nullable', 'string', 'max:50'],
            's3_key' => ['nullable', 'string', 'max:500'],
        ]);
    }
}

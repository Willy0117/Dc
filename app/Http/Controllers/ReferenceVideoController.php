<?php

namespace App\Http\Controllers;

use App\Models\ReferenceVideo;
use App\Models\ReferenceVideoView;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReferenceVideoController extends Controller
{
    // ──────────────────────────────────────────
    // 一覧（カテゴリ別にグルーピングして表示、ログインユーザー単位の視聴済みフラグ付き）
    // ──────────────────────────────────────────
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $watchedIds = ReferenceVideoView::where('user_id', $userId)
            ->pluck('reference_video_id')
            ->toArray();

        $videos = ReferenceVideo::ordered()->get()->map(fn($v) => [
            'id'            => $v->id,
            'category'      => $v->category,
            'title'         => $v->title,
            'is_required'   => $v->is_required,
            'is_watched'    => in_array($v->id, $watchedIds),
            'embed_url'     => $v->embed_url,
            'thumbnail_url' => $v->thumbnail_url,
        ]);

        $categories = ReferenceVideo::CATEGORIES;
        $videosByCategory = collect($categories)->mapWithKeys(fn($cat) => [
            $cat => $videos->where('category', $cat)->values(),
        ]);

        return Inertia::render('ReferenceVideos/Index', [
            'videosByCategory' => $videosByCategory,
            'categories'       => $categories,
        ]);
    }

    // ──────────────────────────────────────────
    // 視聴済みにする（ログインユーザー単位）
    // ──────────────────────────────────────────
    public function markWatched(Request $request, ReferenceVideo $referenceVideo)
    {
        $user = $request->user();

        // organization_idは参考情報として保存（病院代表アカウントならuser->organization_id、
        // 先生個人アカウントならuser->member->organization_id）
        $organizationId = match ((int) $user->type) {
            1 => $user->organization_id,
            2 => $user->member?->organization_id,
            default => null,
        };

        ReferenceVideoView::updateOrCreate(
            [
                'user_id'             => $user->id,
                'reference_video_id'  => $referenceVideo->id,
            ],
            [
                'organization_id' => $organizationId,
                'viewed_at'       => now(),
            ]
        );

        return response()->json(['message' => '視聴済みにしました。']);
    }
}

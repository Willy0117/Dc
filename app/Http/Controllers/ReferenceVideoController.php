<?php

namespace App\Http\Controllers;

use App\Models\ReferenceVideo;
use App\Models\ReferenceVideoCategory;
use App\Models\ReferenceVideoView;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReferenceVideoController extends Controller
{
    // ──────────────────────────────────────────
    // 一覧（カテゴリー別にグルーピングして表示、ログインユーザー単位の視聴済みフラグ付き）
    // 変更点：ログイン中の先生のグレード未満のカテゴリーは、
    // 一覧に含めない（見せない）。
    // 病院(organization)ログインの場合は、常にベーシック(Tier1)の
    // カテゴリーのみ表示する（資料一覧・症例報告と同じ考え方）。
    // ──────────────────────────────────────────
    public function index(Request $request)
    {
        $user   = $request->user();
        $userId = $user->id;

        // 2:先生(member)ログインなら自分のグレード、
        // それ以外(病院ログイン)は常にベーシック(1)扱いにする
        $effectiveTier = ((int) $user->type === 2)
            ? ($user->member?->tier ?? 1)
            : 1;

        $watchedIds = ReferenceVideoView::where('user_id', $userId)
            ->pluck('reference_video_id')
            ->toArray();

        $categories = ReferenceVideoCategory::ordered()
            ->availableForTier($effectiveTier)
            ->with(['videos' => fn($q) => $q->ordered()])
            ->get()
            ->map(fn($cat) => [
                'id'   => $cat->id,
                'name' => $cat->name,
                'videos' => $cat->videos->map(fn($v) => [
                    'id'            => $v->id,
                    'title'         => $v->title,
                    'is_required'   => $v->is_required,
                    'is_watched'    => in_array($v->id, $watchedIds),
                    'embed_url'     => $v->embed_url,
                    'thumbnail_url' => $v->thumbnail_url,
                ]),
            ]);

        return Inertia::render('ReferenceVideos/Index', [
            'categories' => $categories,
        ]);
    }

    // ──────────────────────────────────────────
    // 視聴済みにする（ログインユーザー単位）
    // ──────────────────────────────────────────
    public function markWatched(Request $request, ReferenceVideo $referenceVideo)
    {
        $user = $request->user();

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

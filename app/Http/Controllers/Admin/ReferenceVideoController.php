<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferenceVideo;
use App\Models\ReferenceVideoCategory;
use App\Models\ReferenceVideoView;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReferenceVideoController extends Controller
{
    // ──────────────────────────────────────────
    // 一覧
    // ──────────────────────────────────────────
    public function index()
    {
        $videos = ReferenceVideo::ordered()->get();
        $categories = ReferenceVideoCategory::ordered()->withCount('videos')->get();

        return Inertia::render('Admin/ReferenceVideos/Index', [
            'videos'     => $videos,
            'categories' => $categories,
            'tierLabels' => \App\Models\Member::TIER_LABELS, // 追加：カテゴリーのグレード選択用
        ]);
    }

    // ──────────────────────────────────────────
    // 保存（新規）
    // ──────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:reference_video_categories,id',
            'title'       => 'required|string|max:255',
            'youtube_url' => 'required|url|max:255',
            'is_required' => 'boolean',
        ]);

        $maxOrder = ReferenceVideo::where('category_id', $validated['category_id'])->max('sort_order') ?? 0;

        ReferenceVideo::create([
            ...$validated,
            'is_required' => $validated['is_required'] ?? false,
            'sort_order'  => $maxOrder + 1,
        ]);

        return redirect()->back()->with('success', '動画を追加しました。');
    }

    // ──────────────────────────────────────────
    // 更新
    // ──────────────────────────────────────────
    public function update(Request $request, ReferenceVideo $referenceVideo)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:reference_video_categories,id',
            'title'       => 'required|string|max:255',
            'youtube_url' => 'required|url|max:255',
            'is_required' => 'boolean',
        ]);

        $referenceVideo->update([
            ...$validated,
            'is_required' => $validated['is_required'] ?? false,
        ]);

        return redirect()->back()->with('success', '動画を更新しました。');
    }

    // ──────────────────────────────────────────
    // 削除
    // ──────────────────────────────────────────
    public function destroy(ReferenceVideo $referenceVideo)
    {
        $referenceVideo->delete();

        return redirect()->back()->with('success', '動画を削除しました。');
    }

    // ──────────────────────────────────────────
    // 並べ替え（同一カテゴリー内でのドラッグ&ドロップ）
    // ──────────────────────────────────────────
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'required|integer|exists:reference_videos,id',
        ]);

        foreach ($validated['ids'] as $index => $id) {
            ReferenceVideo::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['message' => '並び順を更新しました。']);
    }

    // ──────────────────────────────────────────
    // 視聴状況一覧（誰が必須動画を視聴済みか）
    // ──────────────────────────────────────────
    public function views(Request $request)
    {
        $requiredVideos = ReferenceVideo::required()->ordered()->get(['id', 'category_id', 'title']);
        $requiredCount = $requiredVideos->count();
        $requiredIds = $requiredVideos->pluck('id');

        $userType = $request->input('user_type', 'organization') === 'member' ? 2 : 1;
        $statusFilter = $request->input('status', 'all');

        $completedUserIds = ReferenceVideoView::whereIn('reference_video_id', $requiredIds)
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(DISTINCT reference_video_id) >= ?', [$requiredCount])
            ->pluck('user_id');

        $baseQuery = fn($type) => User::where('type', $type)
            ->when($request->keyword, function ($q, $kw) {
                $q->where(function ($sub) use ($kw) {
                    $sub->where('name', 'like', "%{$kw}%")
                        ->orWhereHas('organization', fn($o) => $o->where('name', 'like', "%{$kw}%"))
                        ->orWhereHas('member.organization', fn($o) => $o->where('name', 'like', "%{$kw}%"));
                });
            });

        $organizationCount = $baseQuery(1)->count();
        $memberCount = $baseQuery(2)->count();

        $incompleteCount = (clone $baseQuery($userType))->whereNotIn('id', $completedUserIds)->count();

        $usersQuery = $baseQuery($userType)
            ->with(['organization:id,name', 'member:id,organization_id,last_name,first_name', 'member.organization:id,name'])
            ->when($statusFilter === 'incomplete', fn($q) => $q->whereNotIn('id', $completedUserIds))
            ->orderBy('name');

        $users = $usersQuery->paginate(20)->withQueryString();

        $views = ReferenceVideoView::whereIn('user_id', $users->pluck('id'))
            ->whereIn('reference_video_id', $requiredIds)
            ->get()
            ->groupBy('user_id');

        $users->getCollection()->transform(function ($user) use ($views, $requiredVideos) {
            $watchedIds = $views->get($user->id, collect())->pluck('reference_video_id')->toArray();

            $organizationName = $user->type == 1
                ? $user->organization?->name
                : $user->member?->organization?->name;

            $unwatchedTitles = $requiredVideos
                ->reject(fn($v) => in_array($v->id, $watchedIds))
                ->pluck('title')
                ->values();

            return [
                'user_id'           => $user->id,
                'user_name'         => $user->name,
                'user_type'         => $user->type == 1 ? 'organization' : 'member',
                'organization_name' => $organizationName ?? '-',
                'completed_count'   => count($watchedIds),
                'total_count'       => $requiredVideos->count(),
                'is_complete'       => count($watchedIds) >= $requiredVideos->count(),
                'unwatched_titles'  => $unwatchedTitles,
            ];
        });

        return Inertia::render('Admin/ReferenceVideos/Views', [
            'rows'              => $users,
            'organizationCount' => $organizationCount,
            'memberCount'       => $memberCount,
            'incompleteCount'   => $incompleteCount,
            'filters'           => $request->only(['keyword', 'user_type', 'status']),
        ]);
    }
}

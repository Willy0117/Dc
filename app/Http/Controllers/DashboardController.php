<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\RehabApplication;
use App\Models\ReferenceVideo;
use App\Models\ReferenceVideoView;
use App\Models\Notice;
use App\Models\NoticeView;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $applications = array();

        $watchedIds = ReferenceVideoView::where('user_id', $user->id)->pluck('reference_video_id');
        $unwatchedRequiredVideosCount = ReferenceVideo::required()->whereNotIn('id', $watchedIds)->count();

        $viewedNoticeIds = NoticeView::where('user_id', $user->id)->pluck('notice_id');

        $notices = Notice::published()
            ->visibleTo($user)
            ->orderedLatest()
            ->limit(10)
            ->get()
            ->map(function (Notice $notice) use ($viewedNoticeIds) {
                return [
                    'id'                     => $notice->id,
                    'title'                  => $notice->title,
                    'body'                   => $notice->body,
                    'published_at'           => $notice->published_at?->format('Y-m-d'),
                    'embed_url'              => $notice->embed_url,
                    'is_video_available'     => $notice->is_video_available,
                    'video_available_until'  => $notice->video_available_until?->format('Y-m-d H:i'),
                    'is_viewed'              => $viewedNoticeIds->contains($notice->id),
                ];
            });

        return Inertia::render('Dashboard', [
            'user' => $user,
            'applications' => $applications,
            'unwatchedRequiredVideosCount' => $unwatchedRequiredVideosCount,
            'notices' => $notices,
        ]);
    }

    /**
     * お知らせを開いた/動画を視聴したタイミングで呼ぶ
     */
    public function viewNotice(Request $request, Notice $notice)
    {
        NoticeView::firstOrCreate(
            ['notice_id' => $notice->id, 'user_id' => $request->user()->id],
            ['viewed_at' => now()]
        );

        return response()->noContent();
    }
}

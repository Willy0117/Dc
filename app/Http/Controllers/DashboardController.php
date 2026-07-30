<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\RehabApplication;
use App\Models\ReferenceVideo;
use App\Models\ReferenceVideoView;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $applications = array();

        $watchedIds = ReferenceVideoView::where('user_id', $user->id)->pluck('reference_video_id');
        $unwatchedRequiredVideosCount = ReferenceVideo::required()->whereNotIn('id', $watchedIds)->count();

        return Inertia::render('Dashboard', [
            'user' => $user, 
            'applications' => $applications,
            'unwatchedRequiredVideosCount' => $unwatchedRequiredVideosCount,
        ]);
    }
}

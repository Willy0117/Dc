<?php

namespace App\Http\Controllers;

use App\Models\VideoView;
use Illuminate\Http\Request;

class ViewingController extends Controller
{
    /**
     * 視聴ページ本体（購入した動画セットの一覧を表示）
     * 動画ごとに「視聴完了→テストを受ける→合格→証明書」の状態を出し分ける。
     */
    public function index(Request $request, string $token)
    {
        $order = $request->attributes->get('order');
        $videos = $order->videoSet->videos;

        $completedVideoIds = $order->videoViews()
            ->whereNotNull('completed_at')
            ->pluck('video_id')
            ->toArray();

        $passedVideoIds = $order->quizAttempts()
            ->where('passed', true)
            ->pluck('video_id')
            ->toArray();

        return view('watch.index', [
            'order' => $order,
            'videos' => $videos,
            'completedVideoIds' => $completedVideoIds,
            'passedVideoIds' => $passedVideoIds,
        ]);
    }

    /**
     * 動画再生完了時にフロントからAjaxで呼ばれるエンドポイント
     */
    public function markComplete(Request $request, string $token)
    {
        $order = $request->attributes->get('order');

        $request->validate([
            'video_id' => ['required', 'integer'],
        ]);

        // 購入したセットに含まれる動画かを確認
        $videoIds = $order->videoSet->videos->pluck('id')->toArray();
        abort_unless(in_array((int) $request->video_id, $videoIds), 403);

        VideoView::updateOrCreate(
            ['order_id' => $order->id, 'video_id' => $request->video_id],
            ['completed_at' => now()]
        );

        return response()->json(['ok' => true]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WebhookLogController extends Controller
{
    /**
     * 未確認件数を返す（ヘッダーの赤バッジ表示用）
     */
    public function unreadCount(): JsonResponse
    {
        $count = WebhookLog::where('is_read', false)->count();

        return response()->json(['count' => $count]);
    }

    /**
     * 一覧取得
     */
    public function index(Request $request): JsonResponse
    {
        $logs = WebhookLog::orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json(['logs' => $logs]);
    }

    /**
     * 全件既読化
     */
    public function markAllRead(): JsonResponse
    {
        WebhookLog::where('is_read', false)->update(['is_read' => true]);

        return response()->json(['status' => 'ok']);
    }
}

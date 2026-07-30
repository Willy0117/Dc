<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class OrderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:orders.view'),
        ];
    }
    /**
     * 管理画面ダッシュボード：直近の注文を簡易表示する
     */
    public function dashboard()
    {
        $recentOrders = Order::with('videoSet')
            ->orderByDesc('paid_at')
            ->limit(10)
            ->get()
            ->map(fn ($order) => [
                'id' => $order->id,
                'paid_at' => $order->paid_at,
                'customer_name' => $order->customer_name,
                'video_set_name' => $order->videoSet
                    ? "セット{$order->videoSet->name}：{$order->videoSet->category}"
                    : '',
                'status' => $order->status,
            ]);

        return Inertia::render('Admin/Dashboard', [
            'recentOrders' => $recentOrders,
        ]);
    }


    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'paid_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $perPage = (int) $request->input('per_page', 20);
        $status = $request->input('status');

        $allowedSorts = ['id', 'paid_at', 'customer_name', 'status', 'created_at'];
        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'paid_at';
        }

        $orders = Order::with('videoSet')
            ->when($request->input('video_set_id'), fn ($q, $id) => $q->where('video_set_id', $id))
            ->when($status && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'video_set_id' => $request->input('video_set_id'),
                'status' => $status ?? 'all',
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ],
        ]);
    }

    /**
     * 注文一覧をCSV出力する（画面上の絞り込みには影響されず、常に全件・購入日の新しい順）
     */
    public function export()
    {
        $orders = Order::with('videoSet')
            ->orderByDesc('paid_at')
            ->get();

        $statusLabel = [
            'paid' => '購入済み',
            'pending' => '保留',
            'refunded' => '返金済み',
        ];

        $filename = 'orders_' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($orders, $statusLabel) {
            $handle = fopen('php://output', 'w');

            // ExcelでSJIS/文字化けせず開けるよう、UTF-8 BOMを先頭に付与
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['購入日', '動画セット名', '購入者名', '所属', 'メールアドレス', '状態']);

            foreach ($orders as $order) {
                $paidAt = $order->paid_at ?? $order->created_at;
                $videoSetName = $order->videoSet
                    ? "セット{$order->videoSet->name}：{$order->videoSet->category}"
                    : '';

                fputcsv($handle, [
                    $paidAt?->format('Y-m-d H:i:s'),
                    $videoSetName,
                    $order->customer_name,
                    $order->affiliation,
                    $order->customer_email,
                    $statusLabel[$order->status] ?? $order->status,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['videoSet.videos', 'videoViews', 'quizAttempts', 'certificates']);

        $videos = $order->videoSet->videos->map(function ($video) use ($order) {
            $view = $order->videoViews->firstWhere('video_id', $video->id);
            $attempts = $order->quizAttempts->where('video_id', $video->id)->sortByDesc('created_at')->values();
            $certificate = $order->certificates->firstWhere('video_id', $video->id);

            return [
                'id' => $video->id,
                'title' => $video->title,
                'speaker_name' => $video->speaker_name,
                'duration_minutes' => $video->duration_minutes,
                'watched' => $view && $view->completed_at !== null,
                'watched_at' => $view?->completed_at,
                'quiz_attempts' => $attempts->map(fn ($a) => [
                    'score_percent' => $a->score_percent,
                    'passed' => $a->passed,
                    'created_at' => $a->created_at,
                ]),
                'certificate' => $certificate ? [
                    'certificate_number' => $certificate->certificate_number,
                    'issued_at' => $certificate->issued_at,
                    'id' => $certificate->id,
                ] : null,
            ];
        });

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
            'videos' => $videos,
        ]);
    }
}

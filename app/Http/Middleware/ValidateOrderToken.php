<?php

namespace App\Http\Middleware;

use App\Models\Order;
use Closure;
use Illuminate\Http\Request;

class ValidateOrderToken
{
    /**
     * URLの {token} から有効な注文を取得し、リクエストに紐付ける。
     * 見つからない/未払いの場合は404。
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->route('token');

        $order = Order::where('access_token', $token)
            ->where('status', 'paid')
            ->with('videoSet.videos')
            ->first();

        abort_unless($order, 404, 'このURLは無効です。購入時のメールをご確認ください。');

        $request->attributes->set('order', $order);

        return $next($request);
    }
}

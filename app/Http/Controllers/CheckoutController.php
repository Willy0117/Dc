<?php

namespace App\Http\Controllers;

use App\Models\VideoSet;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    /**
     * 商品(動画セット)紹介ページ
     */
    public function show(VideoSet $videoSet)
    {
        abort_unless($videoSet->active, 404);

        return view('checkout.product', compact('videoSet'));
    }

    /**
     * Stripe Checkoutセッションを作成し、Stripeの決済ページへリダイレクト
     */
    public function start(Request $request, VideoSet $videoSet)
    {
        abort_unless($videoSet->active, 404);

        $request->validate([
            'email' => ['required', 'email'],
            'name' => ['nullable', 'string', 'max:100'],
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'customer_email' => $request->input('email'),
            'line_items' => [[
                'price' => $videoSet->stripe_price_id,
                'quantity' => 1,
            ]],
            // 決済完了の実処理はWebhookで行う（このURLはユーザー体験用の遷移先）
            'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.show', $videoSet),
            'metadata' => [
                'video_set_id' => $videoSet->id,
                'customer_name' => $request->input('name', ''),
            ],
        ]);

        return redirect($session->url);
    }

    /**
     * 決済完了後にStripeからリダイレクトされてくる画面
     * Webhookの処理が先に完了していれば、その場で視聴URLを表示する。
     * 間に合っていない場合は「メールをご確認ください」の案内のみ表示する。
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        $order = $sessionId
            ? \App\Models\Order::where('stripe_checkout_session_id', $sessionId)->first()
            : null;

        $watchUrl = $order ? route('watch.index', $order->access_token) : null;

        return view('checkout.success', compact('watchUrl'));
    }
}

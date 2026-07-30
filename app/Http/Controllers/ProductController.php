<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\VideoSet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class ProductController extends Controller
{
    /**
     * 申込サイト（1ページ完結：一覧・詳細・申込ダイアログをすべてこの1画面で処理）
     */
    public function index()
    {
        $videoSets = VideoSet::where('active', true)
            ->with('videos')
            ->orderBy('id')
            ->get()
            ->map(function ($set) {
                return [
                    'id' => $set->id,
                    'name' => $set->name,
                    'theme' => $set->theme,
                    'overview' => $set->overview,
                    'category' => $set->category,
                    'price_jpy' => $set->price_jpy,
                    'lecture_count' => $set->videos->count(),
                    'total_minutes' => $set->videos->sum('duration_minutes'),
                    'lectures' => $set->videos->map(fn ($v) => [
                        'title' => $v->title,
                        'speaker_name' => $v->speaker_name,
                        'duration_minutes' => $v->duration_minutes,
                    ]),
                ];
            });

        return Inertia::render('Products/Index', [
            'videoSets' => $videoSets,
        ]);
    }

    /**
     * 申込ダイアログから送信された申込者情報を受け取り、Stripe Checkoutセッションを作成してリダイレクト
     *
     * 開発中・Stripe未設定時の動作確認用に、.envで STRIPE_SKIP=true にすると
     * 実際のStripe決済を行わず、その場で「決済済み」として注文を作成する（本番公開前に必ずfalseへ戻すこと）。
     */
    public function checkout(Request $request, VideoSet $videoSet)
    {
        abort_unless($videoSet->active, 404);

        $data = $request->validate([
            'membership_status' => ['required', 'string', 'in:member,non_member'],
            'name' => ['required', 'string', 'max:100'],
            'affiliation' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:150'],
            'occupation' => ['required', 'string', 'in:1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20'],
            'occupation_other' => ['required_if:occupation,20', 'nullable', 'string', 'max:100'],
            'bed_count' => ['required', 'string', 'in:1,2,3,4,5,6,7,8'],
        ]);

        // 視聴用トークンを先に生成しておき、Stripeの決済レシートメールにも同じURLを載せる
        $accessToken = Str::random(48);
        $watchUrl = route('watch.index', $accessToken);

        // ── 開発用バイパス：Stripeを使わずアプリだけで最後まで通しで確認したい場合 ──
        if (filter_var(env('STRIPE_SKIP', false), FILTER_VALIDATE_BOOL)) {
            $order = \App\Models\Order::create([
                'video_set_id' => $videoSet->id,
                'membership_status' => $data['membership_status'],
                'customer_name' => $data['name'],
                'affiliation' => $data['affiliation'],
                'phone' => $data['phone'],
                'customer_email' => $data['email'],
                'occupation' => $data['occupation'],
                'occupation_other' => $data['occupation_other'] ?? null,
                'bed_count' => $data['bed_count'],
                'stripe_checkout_session_id' => 'dev_skip_' . Str::random(24),
                'stripe_payment_intent' => null,
                'status' => 'paid',
                'access_token' => $accessToken,
                'paid_at' => now(),
            ]);

            \Illuminate\Support\Facades\Mail::to($order->customer_email)
                ->send(new \App\Mail\OrderConfirmationMail($order));

            return redirect()->route('checkout.success', ['session_id' => $order->stripe_checkout_session_id]);
        }
        // ── ここまで開発用バイパス ──

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'customer_email' => $data['email'],
            'line_items' => [[
                'price' => $videoSet->stripe_price_id,
                'quantity' => 1,
            ]],
            'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('products.index'),
            'payment_intent_data' => [
                // Stripeの決済レシートメールに表示される説明文（ここに視聴URLを載せる）
                'description' => "セット{$videoSet->name}「{$videoSet->category}」ご購入ありがとうございます。視聴用URL: {$watchUrl}",
            ],
            'metadata' => [
                'video_set_id' => $videoSet->id,
                'membership_status' => $data['membership_status'],
                'customer_name' => $data['name'],
                'affiliation' => $data['affiliation'],
                'phone' => $data['phone'],
                'occupation' => $data['occupation'],
                'occupation_other' => $data['occupation_other'] ?? '',
                'bed_count' => $data['bed_count'],
                'access_token' => $accessToken,
            ],
        ]);

        return \Inertia\Inertia::location($session->url);
    }
}
<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\VideoSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $this->fulfillOrder($event->data->object);
        }

        return response('ok', 200);
    }

    protected function fulfillOrder($session): void
    {
        // 冪等性の確保：同じセッションで二重に処理しない
        if (Order::where('stripe_checkout_session_id', $session->id)->exists()) {
            return;
        }

        $videoSetId = $session->metadata->video_set_id ?? null;
        $videoSet = VideoSet::find($videoSetId);

        if (! $videoSet) {
            Log::error('Webhook: video_set not found', ['session_id' => $session->id]);
            return;
        }

        $order = Order::create([
            'video_set_id' => $videoSet->id,
            'membership_status' => $session->metadata->membership_status ?? null,
            'customer_name' => $session->metadata->customer_name ?? null,
            'affiliation' => $session->metadata->affiliation ?? null,
            'phone' => $session->metadata->phone ?? null,
            'customer_email' => $session->customer_details->email ?? $session->customer_email,
            'occupation' => $session->metadata->occupation ?? null,
            'occupation_other' => $session->metadata->occupation_other ?? null,
            'bed_count' => $session->metadata->bed_count ?? null,
            'stripe_checkout_session_id' => $session->id,
            'stripe_payment_intent' => $session->payment_intent,
            'status' => 'paid',
            // 決済開始時（ProductController@checkout）に発行済みのトークンをそのまま使う。
            // これにより、Stripeの決済レシートメールに載せたURLと、確認メールのURLが完全に一致する。
            'access_token' => $session->metadata->access_token ?? Str::random(48),
            'paid_at' => now(),
        ]);

        Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));
    }
}
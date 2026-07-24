<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\OrganizationContract;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        // 署名検証
        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe Webhook: 署名検証エラー', [
                'message' => $e->getMessage(),
            ]);
            return response('Invalid signature', 400);
        } catch (\Throwable $e) {
            Log::error('Stripe Webhook: ペイロード解析エラー', [
                'message' => $e->getMessage(),
            ]);
            return response('Bad Request', 400);
        }

        Log::info('Stripe webhook HIT', [
            'type'     => $event->type,
            'event_id' => $event->id,
        ]);

        // 受信内容を記録（管理画面の通知表示用）
        WebhookLog::create([
            'source'     => WebhookLog::SOURCE_STRIPE,
            'event_type' => $event->type,
            'payload'    => json_encode($event->toArray()),
            'created_at' => now(),
        ]);

        if ($event->type === 'checkout.session.completed') {
            $this->handleCheckoutCompleted($event->data->object);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * checkout.session.completed を受け取り、該当する invoice を支払済みに更新する
     */
    private function handleCheckoutCompleted(object $session): void
    {
        $invoiceId = $session->metadata->invoice_id ?? null;

        if (!$invoiceId) {
            Log::warning('Stripe Webhook: metadata.invoice_id が取得できません', [
                'session_id' => $session->id ?? null,
            ]);
            return;
        }

        $invoice = Invoice::find($invoiceId);

        if (!$invoice) {
            Log::error('Stripe Webhook: 対応するinvoiceが見つかりません', [
                'invoice_id' => $invoiceId,
            ]);
            return;
        }

        // 既に支払済みの場合は二重処理を防ぐ
        if ((int)$invoice->status === 2) {
            Log::info('Stripe Webhook: 既に支払済みのためスキップ', [
                'invoice_id' => $invoice->id,
            ]);
            return;
        }

        $invoice->update([
            'status'                => 2, // 支払済み
            'paid_at'               => now(),
            'stripe_session_id'     => $session->id ?? null,
            'stripe_payment_intent' => $session->payment_intent ?? $invoice->stripe_payment_intent,
        ]);

        // organization_contracts の started_at 確定処理（保留中）
        // $contract = OrganizationContract::where('invoice_id', $invoice->id)->first();
        // if ($contract && !$contract->started_at) {
        //     $contract->update(['started_at' => now()->toDateString()]);
        //     Log::info('Stripe Webhook: 契約開始日を確定しました', [
        //         'organization_contract_id' => $contract->id,
        //         'started_at'               => $contract->started_at,
        //     ]);
        // }
        // 契約日を更新
        $organization = $invoice->organization;
        $organization->updateContractDate();

        // リマインダー送信済みをリセット
        $organization->update(['reminder_sent_at' => null]);

        // MyPageパスワード設定メール送信(初回のみ内部で判定される)
        app(\App\Services\UserInviteService::class)->sendPasswordSetupMail($organization);

        Log::info('Stripe Webhook: 入金処理完了', [
            'invoice_id' => $invoice->id,
            'invoice_no' => $invoice->invoice_no,
        ]);
    }
}
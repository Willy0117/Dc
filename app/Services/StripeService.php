<?php

namespace App\Services;

use App\Exceptions\StripePaymentException;
use App\Models\Organization;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\OrganizationContract;
use Stripe\Stripe;
use Stripe\PaymentLink;
use Stripe\Price;
use Stripe\Product;
use Carbon\Carbon;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createAndSend(Organization $organization, array $data): Invoice
    {
        try {
            // 1. 金額計算（$dataにフラットに入っている料金情報を使用）
            $total    = $data['total'];
            $subtotal = $data['subtotal'] ?? null;
            $tax      = $data['tax'] ?? null;

            $memberCount = Member::where('organization_id', $organization->id)->count();

            // 2. Invoice を先に作成（IDをPayment Linkの紐付けキーとして使うため）
            $invoice = Invoice::create([
                'organization_id' => $organization->id,
                'invoice_no'      => $this->generateInvoiceNo(),
                'amount'          => $total,
                'corporate_fee'   => $data['corporate_fee'] ?? null,
                'personal_fee'    => $data['personal_fee']  ?? null,
                'member_count'    => $memberCount,
                'subtotal'        => $subtotal,
                'tax'             => $tax,
                'billing_date'    => now()->toDateString(),
                'due_date'        => now()->addDays(30)->toDateString(),
                'status'          => 0, // 未送信（Payment Link発行後に1へ更新）
            ]);

            // 3. Stripe Product 作成
            $product = Product::create([
                'name' => "ライセンス料 - {$organization->name}",
            ]);

            // 4. Stripe Price 作成
            $price = Price::create([
                'product'     => $product->id,
                'unit_amount' => $total,
                'currency'    => 'jpy',
            ]);

            // 5. Payment Link 作成（invoice.idをmetadataに埋め込む）
            $paymentLink = PaymentLink::create([
                'line_items' => [
                    [
                        'price'    => $price->id,
                        'quantity' => 1,
                    ],
                ],
                'after_completion' => [
                    'type'     => 'redirect',
                    'redirect' => ['url' => route('applications.stripe_complete')],
                ],
                'metadata' => [
                    'invoice_id' => $invoice->id,
                ],
            ]);

            // 6. Invoice を更新
            $invoice->update([
                'status'              => 1, // 送信済み
                'stripe_payment_link' => $paymentLink->url,
                'email_sent'          => 1,
                'email_sent_at'       => now(),
            ]);

            // 6.5 organization_contracts を作成
            OrganizationContract::create([
                'organization_id' => $organization->id,
                'invoice_id'      => $invoice->id,
                'corporate_fee'   => $data['corporate_fee'] ?? null,
                'personal_fee'    => $data['personal_fee']  ?? null,
                'started_at'      => $organization->new_contract_date
                    ?? \Carbon\Carbon::parse($organization->contract_date)->addYear()->toDateString(),
                'ended_at'        => null,
            ]);

            // 7. メール送信
            $email = $data['email'];
            \Mail::to($email)->send(new \App\Mail\StripePaymentMail($organization, $invoice));

            return $invoice;
        } catch (\Throwable $e) {
            \Log::error('StripeService: 決済リンク作成・送信に失敗しました', [
                'organization_id' => $organization->id,
                'message'         => $e->getMessage(),
            ]);

            throw new StripePaymentException('決済リンクの作成・送信に失敗しました: ' . $e->getMessage(), previous: $e);
        }
    }

    /**
     * 請求書番号を発行する
     * フォーマット: {西暦4桁}DL-{月2桁}{月内通し番号3桁}
     * 例: 2026年6月の1枚目 → 2026DL-06001
     *
     * 変更点：以前はCOUNT（件数）を+1する方式だったため、
     * 途中の請求書が削除されると番号がズレて重複してしまうバグがあった。
     * 既存番号の「最大値」を+1する方式に変更し、念のため
     * 実際に重複していないかも確認してから返す。
     */
    private function generateInvoiceNo(): string
    {
        $year   = now()->format('Y');
        $month  = now()->format('m');
        $prefix = $year . 'DL-' . $month;

        // 変更点：invoice_noのUNIQUE制約はソフトデリートされたレコードにも
        // 適用されるため、withTrashed()で削除済みも含めて確認する。
        $maxSuffix = Invoice::withTrashed()
            ->where('invoice_no', 'like', $prefix . '%')
            ->get()
            ->map(fn ($invoice) => (int) substr($invoice->invoice_no, -3))
            ->max() ?? 0;

        do {
            $maxSuffix++;
            $candidate = sprintf('%s%03d', $prefix, $maxSuffix);
        } while (Invoice::withTrashed()->where('invoice_no', $candidate)->exists());

        return $candidate;
    }
}
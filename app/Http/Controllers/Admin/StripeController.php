<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\OrganizationAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Stripe\Stripe;
use Stripe\PaymentLink;
use Stripe\Price;
use Stripe\Product;

class StripeController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    // ──────────────────────────────────────────
    // Stripe支払い管理一覧
    // ──────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Invoice::with(['organization', 'organization.addresses'])
            ->whereNotNull('stripe_payment_link')
            ->when($request->keyword, fn($q, $kw) =>
                $q->whereHas('organization', fn($o) =>
                    $o->where('name', 'like', "%{$kw}%")
                )
            )
            ->when(
                $request->status !== null && $request->status !== '' && $request->status !== 'all',
                fn($q) => $q->where('status', $request->status)
            )
            ->orderByDesc('created_at');

        $invoices = $query->paginate($request->per_page ?? 20)->withQueryString();

        return Inertia::render('Admin/Stripe/Index', [
            'invoices'     => $invoices,
            'filters'      => [
                'keyword'  => $request->keyword  ?? '',
                'status'   => $request->status   ?? 'all',
                'per_page' => $request->per_page ?? 20,
                'sort_by'  => $request->sort_by  ?? 'created_at',
                'sort_dir' => $request->sort_dir ?? 'desc',
            ],
            'statusLabels' => Invoice::$statusLabels,
        ]);
    }

    // ──────────────────────────────────────────
    // 支払いリンク発行（単発 or 一括）
    // ──────────────────────────────────────────
    public function paymentLink(Request $request)
    {
        $request->validate([
            'organization_ids'   => ['required', 'array', 'min:1'],
            'organization_ids.*' => ['integer', 'exists:organizations,id'],
            'amount'             => ['required', 'integer', 'min:50'],
            'description'        => ['nullable', 'string', 'max:500'],
            'payment_methods'    => ['array'],
            'send_email'         => ['boolean'],
        ]);

        // addresses を eager load して N+1 を防ぐ
        $organizations = Organization::with('addresses')
            ->whereIn('id', $request->organization_ids)
            ->get();

        $stripeLink = null;

        foreach ($organizations as $org) {
            // Stripe Product 作成
            $product = Product::create([
                'name' => $request->description ?: "年次サービス利用料 - {$org->name}",
            ]);

            // Price 作成
            $price = Price::create([
                'product'     => $product->id,
                'unit_amount' => $request->amount,
                'currency'    => 'jpy',
            ]);

            // 支払い方法の変換
            $allowedTypes = $this->resolvePaymentMethodTypes(
                $request->payment_methods ?? ['card']
            );

            // PaymentLink 作成
            $link = PaymentLink::create([
                'line_items' => [
                    ['price' => $price->id, 'quantity' => 1],
                ],
                'payment_method_types' => $allowedTypes,
                'metadata' => [
                    'organization_id'   => $org->id,
                    'organization_name' => $org->name,
                ],
            ]);

            // Invoice に保存 or 更新
            $invoice = Invoice::firstOrCreate(
                [
                    'organization_id' => $org->id,
                    'status'          => Invoice::STATUS_UNSENT,
                ],
                [
                    'invoice_no'   => Invoice::generateInvoiceNo(),
                    'amount'       => $request->amount,
                    'billing_date' => $org->contract_date
                                         ? Invoice::calcBillingDate($org->contract_date)
                                         : today(),
                    'due_date'     => now()->addDays(30)->toDateString(),
                ]
            );

            $invoice->update([
                'stripe_payment_link' => $link->url,
            ]);

            // 単発の場合はリンクをフラッシュで返す
            if ($organizations->count() === 1) {
                $stripeLink = $link->url;
            }

            // メール送信
            if ($request->boolean('send_email')) {
                $this->sendPaymentLinkMail($invoice, $org, $link->url);
            }
        }

        return back()->with([
            'success'     => '支払いリンクを発行しました。',
            'stripe_link' => $stripeLink,
        ]);
    }

    // ──────────────────────────────────────────
    // 支払いリンクメール再送（既存リンクをそのまま使う・新規発行はしない）
    // ──────────────────────────────────────────
    public function resendEmail(Invoice $invoice)
    {
        $invoice->load(['organization', 'organization.addresses']);

        if (!$invoice->stripe_payment_link) {
            return back()->withErrors(['error' => 'この請求書にはStripe決済リンクが発行されていません。']);
        }

        $this->sendPaymentLinkMail($invoice, $invoice->organization, $invoice->stripe_payment_link);

        return back()->with('success', '支払いリンクのメールを再送しました。');
    }

    // ──────────────────────────────────────────
    // 支払い方法の変換
    // ──────────────────────────────────────────
    private function resolvePaymentMethodTypes(array $methods): array
    {
        $map = [
            'card'          => 'card',
            'konbini'       => 'konbini',
            'bank_transfer' => 'customer_balance',
        ];

        return array_values(array_filter(
            array_map(fn($m) => $map[$m] ?? null, $methods)
        ));
    }

    // ──────────────────────────────────────────
    // 支払いリンクメール送信
    // 優先: 請求先(type=3) → 郵送先(type=2) → 所在地(type=1) → organizations.email
    // ──────────────────────────────────────────
    private function sendPaymentLinkMail(Invoice $invoice, Organization $org, string $link): void
    {
        $to = $org->billing_email; // Organization モデルの getBillingEmailAttribute()
        if (!$to) {
            \Log::warning("Stripe mail skipped: no email for organization {$org->id}");
            return;
        }

        // 請求先住所（宛名表示用）
        $billingAddress = $org->addresses
            ->firstWhere('type', OrganizationAddress::TYPE_BILLING)
            ?? $org->addresses->first();

        Mail::send(
            'emails.stripe_payment',
            [
                'invoice'        => $invoice,
                'organization'   => $org,
                'billingAddress' => $billingAddress,
                'link'           => $link,
            ],
            function ($message) use ($to, $invoice, $billingAddress) {
                $message->to($to, $billingAddress?->name ?? null)
                        ->subject("【お支払いのご案内】{$invoice->invoice_no}");
            }
        );

        $invoice->update([
            'email_sent'    => true,
            'email_sent_at' => now(),
            'status'        => Invoice::STATUS_SENT,
        ]);
    }
}
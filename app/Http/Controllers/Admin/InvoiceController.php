<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\OrganizationAddress;
use App\Models\OrganizationContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    // ──────────────────────────────────────────
    // 一覧
    // ──────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Invoice::with(['organization', 'organization.addresses'])
            ->when($request->keyword, fn($q, $kw) =>
                $q->whereHas('organization', fn($o) =>
                    $o->where('name', 'like', "%{$kw}%")
                    ->orWhere('contract_no', 'like', "%{$kw}%")
                )
            )
            ->when(
                $request->status !== null && $request->status !== '' && $request->status !== 'all',
                fn($q) => $q->where('status', $request->status)
            )
            ->when(
                $request->billing_year && $request->billing_year !== 'all',
                fn($q) => $q->whereYear('billing_date', $request->billing_year)
            )
            ->when(
                $request->payment_method && $request->payment_method !== 'all',
                fn($q) => $q->whereHas('organization', function ($sub) use ($request) {
                    $sub->where('payment_method', $request->payment_method);
                })
            )
            ->orderBy(
                $request->sort_by  ?? 'billing_date',
                $request->sort_dir ?? 'desc'
            );

        $invoices = $query->paginate($request->per_page ?? 20)->withQueryString();

        return Inertia::render('Admin/Invoices/Index', [
            'invoices'     => $invoices,
            'filters'      => [
                'keyword'        => $request->keyword        ?? '',
                'status'         => $request->status         ?? 'all',
                'billing_year'   => $request->billing_year   ?? 'all',
                'payment_method' => $request->payment_method ?? 'all',
                'per_page'       => $request->per_page       ?? 20,
                'sort_by'        => $request->sort_by        ?? 'billing_date',
                'sort_dir'       => $request->sort_dir       ?? 'desc',
            ],
            'statusLabels' => Invoice::$statusLabels,
        ]);
    }

    // ──────────────────────────────────────────
    // 詳細
    // ──────────────────────────────────────────
    public function show(Invoice $invoice)
    {
        $invoice->load(['organization', 'organization.addresses']);

        return Inertia::render('Admin/Invoices/Show', [
            'invoice'      => $invoice,
            'statusLabels' => Invoice::$statusLabels,
        ]);
    }

    // ──────────────────────────────────────────
    // ステータス更新
    // 「支払済み」に変更する場合、フロントから入金日（paid_at）を受け取る。
    // 新たに支払済みになったタイミングで organization_contracts.started_at を確定させる。
    // ──────────────────────────────────────────
    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'status'  => ['required', 'integer', 'in:0,1,2,3'],
            'paid_at' => ['nullable', 'date', 'required_if:status,' . Invoice::STATUS_PAID],
        ]);

        $isNewlyPaid = (int)$request->status === Invoice::STATUS_PAID
            && (int)$invoice->status !== Invoice::STATUS_PAID;

        $invoice->update([
            'status'  => $request->status,
            'paid_at' => (int)$request->status === Invoice::STATUS_PAID
                ? ($request->paid_at ?? now())
                : $invoice->paid_at,
        ]);

        if ($isNewlyPaid) {
            // organization_contracts の started_at 確定処理（保留中）
            // $contract = OrganizationContract::where('invoice_id', $invoice->id)->first();
            // if ($contract && !$contract->started_at) {
            //     $contract->update([
            //         'started_at' => $request->paid_at ?? now()->toDateString(),
            //     ]);
            //     \Log::info('管理画面: 契約開始日を確定しました', [
            //         'organization_contract_id' => $contract->id,
            //         'started_at'               => $contract->started_at,
            //     ]);
            // }


            $organization = $invoice->organization;

            // 契約日を更新
            $organization->updateContractDate();

            // リマインダー送信済みをリセット
            $organization->update(['reminder_sent_at' => null]);

            // MyPageパスワード設定メール送信(初回のみ内部で判定される)
            app(\App\Services\UserInviteService::class)->sendPasswordSetupMail($organization);

            // 変更点：契約締結・入金確認後、所属する先生全員に
            // 簡易e-ラーニングの受講案内を送信する
            // （先生へのPWメールは、この受講が完了してから別途送られる）
            app(\App\Services\UserInviteService::class)->sendElearningInvitationsForOrganization($organization);

        }

        return back()->with('success', 'ステータスを更新しました。');
    }

    // ──────────────────────────────────────────
    // 削除
    // ──────────────────────────────────────────
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return back()->with('success', '請求書を削除しました。');
    }

    // ──────────────────────────────────────────
    // メール再送
    // ──────────────────────────────────────────
    public function resendEmail(Invoice $invoice)
    {
        $invoice->load(['organization', 'organization.addresses']);
        $this->sendInvoiceMail($invoice, $invoice->organization);

        return back()->with('success', 'メールを再送しました。');
    }

    // ──────────────────────────────────────────
    // メール送信（内部）
    // 既存PDF（pdf_path）をそのまま添付して送信する。PDFの再生成は行わない。
    // ──────────────────────────────────────────
    private function sendInvoiceMail(Invoice $invoice, Organization $org): void
    {
        $to = $org->billing_email; // Organization モデルの getBillingEmailAttribute()
        if (!$to) {
            \Log::warning("Invoice mail skipped: no email for organization {$org->id}");
            return;
        }

        if (!$invoice->pdf_path) {
            \Log::warning("Invoice mail skipped: pdf_path not found for invoice {$invoice->id}");
            return;
        }

        Mail::to($to)->send(
            new \App\Mail\InvoiceMail($org, $invoice, $invoice->pdf_path)
        );

        $invoice->update([
            'status'        => Invoice::STATUS_SENT,
            'email_sent'    => true,
            'email_sent_at' => now(),
        ]);
    }
}
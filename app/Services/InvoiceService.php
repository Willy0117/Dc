<?php

namespace App\Services;

use App\Exceptions\InvoiceException;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Organization;
use App\Models\OrganizationContract;

class InvoiceService
{
    public function __construct(
        private PdfService $pdfService
    ) {}

    public function createAndSend(Organization $organization, array $data): Invoice
    {
        try {
            // 1. 金額計算（$dataにフラットに入っている料金情報を使用）
            $amount        = $data['subtotal'];
            $tax           = $data['tax'];
            $total         = $data['total'];
            $personalFee   = $data['personal_fee'];
            $extra         = $data['extra'] ?? 0;
            $personalCount = $extra > 0 ? (int)($extra / $personalFee) : 0;

            $billingDate = now()->toDateString();
            $dueDate     = $data['due_date'] ?? now()->addMonth()->toDateString();
            $invoiceNo   = $this->generateInvoiceNo();

            $items = [
                ['ライセンス料（基本 3名まで）', 1, $data['corporate_fee']],
            ];

            if ($personalCount > 0) {
                $items[] = ['ライセンス料（追加 ' . $personalCount . '名）', $personalCount, $extra];
            }

            // 2. PDF生成（PdfServiceに委譲）
            $pdfPath = $this->pdfService->createInvoicePdf(
                $organization,
                $data,
                $invoiceNo,
                $items,
                $amount,
                $tax,
                $total,
                $billingDate,
                $dueDate
            );

            // 3. Invoice 作成
            $memberCount = Member::where('organization_id', $organization->id)->count();

            $invoice = Invoice::create([
                'organization_id' => $organization->id,
                'invoice_no'      => $invoiceNo,
                'amount'          => $total,
                'corporate_fee'   => $data['corporate_fee'],
                'personal_fee'    => $personalFee,
                'member_count'    => $memberCount,
                'subtotal'        => $amount,
                'tax'             => $tax,
                'billing_date'    => $billingDate,
                'due_date'        => $dueDate,
                'pdf_path'        => $pdfPath,
                'status'          => 1, // 送信済み
                'email_sent'      => 1,
                'email_sent_at'   => now(),
            ]);

            // 3.5 organization_contracts を作成
            OrganizationContract::create([
                'organization_id' => $organization->id,
                'invoice_id'      => $invoice->id,
                'corporate_fee'   => $data['corporate_fee'],
                'personal_fee'    => $personalFee,
                'started_at'      => $organization->new_contract_date
                    ?? \Carbon\Carbon::parse($organization->contract_date)->addYear()->toDateString(),
                'ended_at'        => null,
            ]);

            // 4. メール送信
            $email = $data['email'];
            \Mail::to($email)->send(new \App\Mail\InvoiceMail($organization, $invoice, $pdfPath));

            return $invoice;
        } catch (\Throwable $e) {
            \Log::error('InvoiceService: 請求書作成・送信に失敗しました', [
                'organization_id' => $organization->id,
                'message'         => $e->getMessage(),
            ]);

            throw new InvoiceException('請求書の作成・送信に失敗しました: ' . $e->getMessage(), previous: $e);
        }
    }

    /**
     * 請求書番号を発行する
     * フォーマット: {西暦4桁}DL-{月2桁}{月内通し番号3桁}
     * 例: 2026年6月の1枚目 → 2026DL-06001
     */
    private function generateInvoiceNo(): string
    {
        $year  = now()->format('Y');
        $month = now()->format('m');

        // 当月の発行済み件数をカウントして通し番号を採番
        $count = Invoice::where('invoice_no', 'like', $year . 'DL-' . $month . '%')->count() + 1;

        return sprintf('%sDL-%s%03d', $year, $month, $count);
    }
}
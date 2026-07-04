<?php

namespace App\Services;

use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PdfService
{
    protected $disk;

    public function __construct(
        private FileService $fileService
    ) {
        $this->disk = config('filesystems.default');
    }

    // ──────────────────────────────────────────
    // 契約書PDF生成
    // ──────────────────────────────────────────

    /**
     * 契約書PDFを生成してストレージに保存し、パスを返す
     *
     * @param  \App\Models\Organization $organization
     * @param  array                    $data  セッションの application データ
     * @return string                   storage_path からの相対パス（public/contracts/xxx.pdf）
     */
    public function createContractPdf($organization, array $data): string
    {
        $pdf = new Fpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // テンプレート読み込み
        $templatePath = storage_path('app/templates/Contract.pdf');//Storage::path('templates/contract.pdf');
        $pageCount    = $pdf->setSourceFile($templatePath);

        // 日本語フォント
        $pdf->SetFont('kozminproregular', '', 12);

        // -----------------------------------------------
        // 1ページ目：当事者記載
        // -----------------------------------------------
        $pdf->AddPage();
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect(15, 45, 180, 30, 'F'); 
        
        $text = '医療法人社団祐優会（以下「甲」という。）、' . $organization->name . '（以下「乙」という。）および株式会社Ａｌｉｖｉｏ ＪＡＰＡＮ（以下「丙」という。）とは、次のとおりライセンス契約（以下「本契約」という。）を締結する。';
        // \xc2\xa0 はUTF-8のノーブレークスペースに置き換え
        $text = str_replace(["\r\n", "\r", "\n", "　", " "], "\xc2\xa0", $text);
        $pdf->setFontSpacing(0.3);
        $pdf->SetXY(20, 50);
        $pdf->MultiCell(170, 8, $text);
        $pdf->setFontSpacing(0);
        // -----------------------------------------------
        // 中間ページをそのまま取り込む
        // -----------------------------------------------
        for ($i = 2; $i < $pageCount; $i++) {
            $pdf->AddPage();
            $tpl = $pdf->importPage($i);
            $pdf->useTemplate($tpl);

            if ($i == 10) {
                    // 契約日
                    $pdf->SetFont('kozminproregular', '', 10);
                    $pdf->SetXY(20, 28);

                    $contractDate = $organization->new_contract_date
                        ? \Carbon\Carbon::parse($organization->new_contract_date)->format('Y　　　n　　　j')
                        : now()->format('Y　　　n　　　j');
                    $pdf->Write(0, $contractDate);

                    // 乙（動的）
                    $address     = $organization->locationAddress;
                    $otsuAddress = implode('', array_filter([
                        $address?->address1,
                        $address?->address2,
                        $address?->address3,
                    ]));
                    $pdf->SetFont('kozminproregular', '', 12);
                    $x = 80; $y = 103;
                    $width = 114; // 右端までの幅を調整

                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->Rect($x-5, $y-5, 210-$x, 30, 'F'); 

                    $pdf->SetXY($x, $y);
                    $pdf->Cell($width, 8, '（住所）' . $otsuAddress, 0, 1, 'R');

                    $pdf->SetXY($x, $y+8);
                    $pdf->Cell($width, 8,'乙　　' . ($data['corp_name'] ?? $organization->name), 0, 1, 'R');

                    $pdf->SetXY($x, $y+16);
                    $pdf->Cell($width, 8, ($data['rep_position'] ?? '') . '　' . ($data['rep_last_name'] ?? '') . '　' . ($data['rep_first_name'] ?? '') . '　印', 0, 1, 'R');            }
        }

        // -----------------------------------------------
        // 保存
        // -----------------------------------------------
        $code = $organization->code;

        if (empty($code)) {
            $code = 'OC' . str_pad($organization->contract_no, 5, '0', STR_PAD_LEFT);
        }

        $fileName = 'contracts/' . $code . '_' . now()->format('Y-m-d') . '.pdf';

        // 一時ファイルとして生成
        $tmpPath = tempnam(sys_get_temp_dir(), 'contract_');
        $pdf->Output($tmpPath, 'F');

        // Storageファサード経由で保存（ローカル/S3どちらでも対応）
        Storage::disk($this->disk)->put($fileName, file_get_contents($tmpPath));
        unlink($tmpPath);

        return $fileName;
    }

    // ──────────────────────────────────────────
    // 合意書PDF生成
    // ──────────────────────────────────────────
 
    public function createAgreementPdf($organization, array $data): string
    {
        $pdf = new Fpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
 
        // テンプレート読み込み
        $templatePath = storage_path('app/templates/Agreement.pdf');//Storage::path('templates/contract.pdf');
        $pageCount    = $pdf->setSourceFile($templatePath);
 
        // 日本語フォント
        $pdf->SetFont('kozminproregular', '', 11);
 
        // -----------------------------------------------
        // 1ページ目：当事者記載・合意日
        // -----------------------------------------------
        $pdf->AddPage();
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl);
 
        // 合意日（contract_dateを使用）
        $contractDate = $organization->contract_date
            ? Carbon::parse($organization->contract_date)->format('Y年n月j日')
            : now()->format('Y年n月j日');

        $text = '医療法人社団祐優会（以下「甲」という。）、' . $organization->name 
        . '（以下「乙」という。）及び株式会社Ａｌｉｖｉｏ ＪＡＰＡＮ（以下「丙」という。）は、甲乙丙間の' 
        . $contractDate
        . '付ライセンス契約（同契約の内容の変更・追加をする合意を含む。以下「旧契約」という。）及び甲乙丙間の本日付ライセンス契約（以下「新契約」という。）について、次のとおり合意する。';
        // \xc2\xa0 はUTF-8のノーブレークスペースに置き換え
        $text = str_replace(["\r\n", "\r", "\n", "　", " "], "\xc2\xa0", $text);
        $x = 25; $y = 60;
        $width = 114; // 右端までの幅を調整

        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect($x-5, $y-5, 210-$x, 32, 'F'); 

        $pdf->SetXY(30, 58);
        $pdf->MultiCell(150, 8, $text);
       // 契約日
        $pdf->SetFont('kozminproregular', '', 10);
        $pdf->SetXY(38, 203);

        $contractDate = $organization->new_contract_date
            ? \Carbon\Carbon::parse($organization->new_contract_date)->format('Y　　　n　　　j')
            : now()->format('Y　　　n　　　j');
        $pdf->Write(0, $contractDate);

        // 乙（動的）
                // -----------------------------------------------
        // 2ページ目：テンプレート取り込み＋住所書き込み
        // -----------------------------------------------
        $pdf->AddPage();
        $tpl = $pdf->importPage(2);
        $pdf->useTemplate($tpl);
 

        // 乙（動的）
        $address     = $organization->locationAddress;
        $otsuAddress = implode('', array_filter([
            $address?->address1,
            $address?->address2,
            $address?->address3,
        ]));
        $pdf->SetFont('kozminproregular', '', 10.5);
        $x = 70; $y = 74;
        $width = 110; // 右端までの幅を調整

        $pdf->SetXY($x, $y);
        $pdf->Cell($width, 8, $otsuAddress, 0, 1, 'R');

        $pdf->SetXY($x, $y+11);
        $pdf->Cell($width, 8, ($data['corp_name'] ?? $organization->name), 0, 1, 'R');

        $pdf->SetXY($x-20, $y+22);
        $pdf->Cell($width, 8, ($data['rep_position'] ?? '') . '　' . ($data['rep_last_name'] ?? '') . '　' . ($data['rep_first_name'] ?? '') . '', 0, 1, 'R');
        // -----------------------------------------------
        // 保存
        // -----------------------------------------------
        return $this->savePdf($pdf, 'contracts/agreement_' . $organization->id . '_' . now()->format('YmdHis') . '.pdf');
    }

    // ──────────────────────────────────────────
    // 請求書PDF生成
    // ──────────────────────────────────────────

    /**
     * 請求書PDFを生成してストレージに保存し、[path, thumbnailPath]を返す
     */
    public function createInvoicePdf(
        $organization,
        array $data,
        string $invoiceNo,
        array $items,
        int $amount,
        int $tax,
        int $total,
        string $billingDate,
        string $dueDate
    ): string {
        $pdf = new Fpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();

        // テンプレート読み込み
        $templatePath = storage_path('app/templates/Invoice.pdf');//Storage::path('templates/Invoice.pdf');

        $pdf->setSourceFile($templatePath);
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl);

        // 日本語フォント
        $pdf->SetFont('kozminproregular', '', 10);

        // 請求書番号
        $pdf->SetXY(36, 17);
        $pdf->Write(8, $invoiceNo);

        // 請求書発行日
        $pdf->SetXY(168, 17);
        $pdf->Write(8, \Carbon\Carbon::parse($billingDate)->format('Y年m月d日'));

        // 請求書送付先
        $pdf->SetXY(20, 66);
        $pdf->Write(8, $organization->name);

        // お支払い期限
        $pdf->SetXY(35, 94);
        $pdf->Write(8, \Carbon\Carbon::parse($dueDate)->format('Y年m月d日'));

        // ご請求額（税込）
        $pdf->SetXY(10, 106);
        $pdf->Cell(55, 14, number_format($total) . '円', 0, 0, 'R');

        // 明細行
        $y = 133;
        foreach ($items as $item) {
            $pdf->SetXY(17, $y);
            $pdf->Write(8, $item[0]);
            $pdf->SetXY(128, $y);
            $pdf->Cell(15, 10, number_format($item[1]), 0, 0, 'R');
            $pdf->SetXY(165, $y);
            $pdf->Cell(25, 10, number_format($item[2]), 0, 0, 'R');
            $y += 10;
        }

        // 小計・消費税・合計
        $pdf->SetXY(165, 204);
        $pdf->Cell(25, 10, number_format($amount), 0, 0, 'R');
        $pdf->SetXY(165, 213);
        $pdf->Cell(25, 10, number_format($tax), 0, 0, 'R');
        $pdf->SetXY(165, 222);
        $pdf->Cell(25, 10, number_format($total), 0, 0, 'R');
        // 保存
        $fileName = 'invoices/invoice_' . $invoiceNo . '.pdf';

        $tmpPath = tempnam(sys_get_temp_dir(), 'pdf_');
        $pdf->Output($tmpPath, 'F');
        Storage::disk($this->disk)->put($fileName, file_get_contents($tmpPath));
        unlink($tmpPath);
 
        return $fileName;
    }

    // ──────────────────────────────────────────
    // 請求書PDF生成
    // ──────────────────────────────────────────

    public function createLicensePdf($organization, string $dueDate, string $displayName = null): string
    {
        \Log::info('display_name:', ['value' => $displayName]);
        $pdf = new Fpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage('L');

        // テンプレート読み込み
        $templatePath = storage_path('app/templates/License.pdf');

        $pdf->setSourceFile($templatePath);
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl);

        // 日本語フォント
        $pdf->SetFont('kozminproregular', '', 12);

        // 契約日
        $pdf->SetXY(168, 153);
        $pdf->Write(8, $dueDate);

        // 日本語フォント
        $pdf->SetFont('kozminproregular', '', 21);
        // 契約先名
        $text = $organization->name;
        $text = $organization->name;
        $pageWidth = $pdf->GetPageWidth(); // 297
        $cellWidth = 140;
        $x = ($pageWidth - $cellWidth) / 2; // 中央揃え

        $text = $displayName ?? $organization->name;

        $text = str_replace([' ', '　'], "\xc2\xa0", $text);
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $pdf->SetXY($x, 50);
        $pdf->MultiCell($cellWidth, 8, $text, 0, 'C');

        // 保存
        $fileName = 'licenses/' . $organization->code . '_' . now()->format('Y-m-d') . '.pdf';

        $tmpPath = tempnam(sys_get_temp_dir(), 'pdf_');
        $pdf->Output($tmpPath, 'F');
        Storage::disk($this->disk)->put($fileName, file_get_contents($tmpPath));
        unlink($tmpPath);
 
        return $fileName;
    }
    // ──────────────────────────────────────────
    // PDF保存（共通）
    // ──────────────────────────────────────────

    private function savePdf(Fpdi $pdf, string $fileName): string
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'pdf_');
        $pdf->Output($tmpPath, 'F');

        Storage::disk($this->disk)->put($fileName, file_get_contents($tmpPath));
        unlink($tmpPath);

        return $fileName;
    }

}
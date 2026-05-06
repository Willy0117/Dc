<?php

namespace App\Http\Controllers;

use setasign\Fpdi\Tcpdf\Fpdi;

class PdfController extends Controller
{
    public function index()
    {
               // FPDI + TCPDF
        $pdf = new Fpdi();
        // ページ追加
        $pdf->AddPage();

        // 既存PDFテンプレート読み込み
        $templatePath = storage_path('app/templates/order_sheet/poem.pdf');
        $pageCount = $pdf->setSourceFile($templatePath);
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl);

        $pdf->Cell(0,10,'PDF TEST OK',0,1);
        $pdf->Cell(0,10,'Laravel PDF Test',0,1);

        return response($pdf->Output('','I'))
            ->header('Content-Type', 'application/pdf');
    }
}
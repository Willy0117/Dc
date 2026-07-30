<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;

class CertificatePdf
{
    /**
     * 証明書PDFを生成する。IPAexGothicフォントをdompdfに実行時登録してから返す。
     *
     * 注意：barryvdh/laravel-dompdf は、vendor付属の load_font.php スクリプトが
     * 最近のバージョンでは同梱されていない。代わりに Dompdf\FontMetrics::registerFont() を
     * 実行時に呼び出す方式で、storage/fonts 配下のTTFをフォントキャッシュに登録する。
     */
    public static function render(string $view, array $data)
    {
        $pdf = Pdf::loadView($view, $data);

        $fontMetrics = $pdf->getDomPDF()->getFontMetrics();
        $ttfPath = storage_path('fonts/ipaexg.ttf');

        // normal・bold 両方を同じフォントファイルで登録（IPAexGothicには太字版が無いため代用）
        $fontMetrics->registerFont(
            ['family' => 'IPAexGothic', 'style' => 'normal', 'weight' => 'normal'],
            $ttfPath
        );
        $fontMetrics->registerFont(
            ['family' => 'IPAexGothic', 'style' => 'normal', 'weight' => 'bold'],
            $ttfPath
        );

        return $pdf;
    }
}

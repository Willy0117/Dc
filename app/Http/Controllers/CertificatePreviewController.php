<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Support\CertificatePdf;

class CertificatePreviewController extends Controller
{
    /**
     * 証明書デザインの確認専用（購入・視聴・テストを経由せず、その場でPDFを表示する）。
     * ダミーの氏名・日付を使い、ブラウザにそのまま表示する（ダウンロードではなく閲覧）。
     *
     * 本番公開後は、このルートは無効化するか、管理者ログイン必須にすることを推奨します。
     */
    public function show(Video $video)
    {
        $order = new \stdClass();
        $order->customer_name = '山田 太郎（プレビュー）';
        $order->videoSet = $video->videoSet;

        $pdf = CertificatePdf::render('certificate.pdf', [
            'order' => $order,
            'video' => $video,
            'certificateNumber' => 'PREVIEW-0000',
            'issuedAt' => now(),
        ]);

        // ダウンロードではなく、ブラウザ上にそのまま表示する
        return $pdf->stream('certificate-preview.pdf');
    }
}

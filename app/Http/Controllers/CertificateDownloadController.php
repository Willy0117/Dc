<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateDownloadController extends Controller
{
    /**
     * メール内リンクからアクセスされる、購入者本人向けの証明書ダウンロード（動画=講義単位）
     */
    public function download(Request $request, string $token, Video $video)
    {
        $order = $request->attributes->get('order');

        $certificate = Certificate::where('order_id', $order->id)
            ->where('video_id', $video->id)
            ->firstOrFail();

        return Storage::disk('local')->download(
            $certificate->pdf_path,
            "{$certificate->certificate_number}.pdf"
        );
    }
}

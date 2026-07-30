<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoDownloadController extends Controller
{
    /**
     * 視聴完了済みの動画について、S3の署名付き一時URLを発行しリダイレクトする。
     * order.token ミドルウェアで、有効な注文の購入者本人であることを確認済み。
     */
    public function download(Request $request, string $token, Video $video)
    {
        $order = $request->attributes->get('order');

        // 購入したセットに含まれる動画かを確認
        abort_unless($video->video_set_id === $order->video_set_id, 404);

        // 視聴完了しているかを確認（視聴完了後のみダウンロード可）
        $completed = $order->videoViews()
            ->where('video_id', $video->id)
            ->whereNotNull('completed_at')
            ->exists();

        abort_unless($completed, 403, '先にこの動画を視聴してください。');

        abort_if(empty($video->s3_key), 404, 'ダウンロード用のファイルが準備中です。しばらくお待ちください。');

        abort_unless(Storage::disk('s3')->exists($video->s3_key), 404, 'ファイルが見つかりませんでした。');

        $asciiFilename = pathinfo($video->s3_key, PATHINFO_BASENAME);
        $utf8Filename = rawurlencode($video->title . '.mp4');

        $contentDisposition = 'attachment; filename="' . $asciiFilename . '"; filename*=UTF-8\'\'' . $utf8Filename;

        $url = Storage::disk('s3')->temporaryUrl(
            $video->s3_key,
            now()->addMinutes(15),
            [
                'ResponseContentDisposition' => $contentDisposition,
            ]
        );

        return redirect($url);
    }

    /**
     * 資料PDFのS3署名付き一時URLを発行しリダイレクトする。
     * 視聴完了状況に関わらず、購入者であればいつでもダウンロード可能。
     * order.token ミドルウェアで、有効な注文の購入者本人であることを確認済み。
     */
    public function material(Request $request, string $token, Video $video)
    {
        $order = $request->attributes->get('order');

        // 購入したセットに含まれる動画かを確認
        abort_unless($video->video_set_id === $order->video_set_id, 404);

        // 資料は視聴完了を問わず、いつでもダウンロード可
        abort_if(empty($video->material_s3_key), 404, '資料は準備中です。しばらくお待ちください。');

        abort_unless(Storage::disk('s3')->exists($video->material_s3_key), 404, 'ファイルが見つかりませんでした。');

        $asciiFilename = pathinfo($video->material_s3_key, PATHINFO_BASENAME);
        $utf8Filename = rawurlencode($video->title . '.pdf');

        $contentDisposition = 'attachment; filename="' . $asciiFilename . '"; filename*=UTF-8\'\'' . $utf8Filename;

        $url = Storage::disk('s3')->temporaryUrl(
            $video->material_s3_key,
            now()->addMinutes(15),
            [
                'ResponseContentDisposition' => $contentDisposition,
            ]
        );

        return redirect($url);
    }
}
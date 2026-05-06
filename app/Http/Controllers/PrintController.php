<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver; // 追加

// ...
class PrintController extends Controller
{
    public function composeImage(Request $request)
    {
        $file = $request->query('file');       
        $bg   = $request->query('bg', 'none'); 

        // V3 の初期化方法
        $manager = new ImageManager(Driver::class);

        // make ではなく read
        $userImg = $manager->read(Storage::path($file));

        $targetW = $userImg->width();
        $targetH = $userImg->height();

/*        if ($w >= 3400 && $h >= 4900) {
            $targetW = 3508; $targetH = 4961;
        } elseif ($w >= 2400 && $h >= 3400) {
            $targetW = 2480; $targetH = 3508;
        } elseif ($w >= 1500 && $h >= 2100) {
            $targetW = 1748; $targetH = 2480;
        } else {
            $targetW = 1200; $targetH = 1800;
        }
*/
        if ($bg !== 'none') {
            $bgImg = $manager->read(Storage::path("templates/bg/{$bg}.png"));
            $bgImg->resize($targetW, $targetH);

            $userImg->cover($targetW, $targetH); // V3ではfitの代わりにcoverも使えます

            // insert ではなく place
            $bgImg->place($userImg, 'top-left');

            $output = $bgImg;
        } else {
            $userImg->cover($targetW, $targetH);
            $output = $userImg;
        }

        // encode('png') の戻り値を文字列として出力
        return response($output->encode(new \Intervention\Image\Encoders\PngEncoder())->toString(), 200)
            ->header('Content-Type', 'image/png');
    }
}

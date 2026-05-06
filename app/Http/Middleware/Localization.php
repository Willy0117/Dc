<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App; // Appファサードをインポート
use Illuminate\Support\Facades\URL; // URLファサードをインポート (for Ziggy routes)
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale'); // URLからロケールを取得
        $supportedLocales = ['ja', 'en']; // サポートする言語のリスト

        if (in_array($locale, $supportedLocales)) {
            App::setLocale($locale); // アプリケーションのロケールを設定
            // Ziggy ルートヘルパーが現在のロケールを認識できるように設定
            URL::defaults(['locale' => $locale]); 
        } else {
            // サポートされていないロケールの場合は、デフォルトロケールにリダイレクトするか、エラーを返す
            // 今回は、ミドルウェアグループのルートでチェックされることを想定し、
            // ここでは単にデフォルトロケールを設定するだけにします。
            App::setLocale(config('app.locale'));
            URL::defaults(['locale' => config('app.locale')]);
        }

        return $next($request);
    }
}
<?php

use App\Http\Middleware\Authenticate;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        $middleware->trustProxies(at: '*');
        //2026.02.17 追加
        $middleware->redirectUsersTo(function () {
            if (auth()->guard('admin')->check()) {
                return '/admin/dashboard'; // 管理者ならここ
            }
            return '/dashboard'; // 一般ユーザーならここ
        });

        // --- 追記: 未認証（ゲスト）がアクセスした時のリダイレクト先（必要な場合） ---
        $middleware->redirectGuestsTo(function () {
            if (request()->is('admin*')) {
                return route('admin.login'); // 管理者URLなら管理用ログインへ
            }
            return route('login'); // それ以外は一般ログインへ
        });
        // ここまで
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\SetLocale::class,
        ]);
        
        // ❗ Spatie Permission ミドルウェアを正しく全て登録する
        $middleware->alias([
            'auth' => Authenticate::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

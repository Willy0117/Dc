<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {

            // admin 配下 → admin/login
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }

            // 通常ユーザー
            return route('login');
        }
    }
}

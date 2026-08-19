<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetSessionCookieByGuard
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('admin', 'admin/*')) {
            config(['session.cookie' => env('SESSION_COOKIE_ADMIN', 'okuno_admin_session')]);
        } else {
            config(['session.cookie' => env('SESSION_COOKIE_WEB', 'okuno_web_session')]);
        }

        return $next($request);
    }
}
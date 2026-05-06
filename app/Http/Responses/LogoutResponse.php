<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        // 管理者ガードかどうかで分岐
        if ($request->routeIs('admin.*')) {
            return redirect()->route('admin.login');
        }

        return redirect('/login'); // 通常ユーザー用
    }
}

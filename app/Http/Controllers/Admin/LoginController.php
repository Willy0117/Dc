<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyLogin;

class LoginController extends FortifyLogin
{
    protected function authenticated(Request $request, $user)
    {
        return redirect()->route('admin.dashboard');
    }
}

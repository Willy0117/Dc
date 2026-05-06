<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Admin/Login'); // Jetstream Login.vue を複製して使用
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        // 認証（Jetstreamと同じ）
        if (!Auth::guard('admin')->attempt($credentials)) {
            return back()->withErrors(['email' => 'ログイン情報が正しくありません。']);
        }

        $request->session()->regenerate();

        $request->session()->forget('url.intended');

        return redirect()->route('admin.dashboard');

        //return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        // ★ Jetstream の intended URL を削除
        $request->session()->forget('url.intended');

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}

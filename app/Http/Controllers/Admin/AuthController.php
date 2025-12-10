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
        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors(['email' => 'ログイン情報が正しくありません。']);
        }

        // Spatie で管理者チェック
        if (! $request->user()->hasRole(['super_admin','admin'])) {
            Auth::logout();
            return back()->withErrors(['email' => '管理者権限がありません。']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        // ★ Jetstream の intended URL を削除
        $request->session()->forget('url.intended');

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}

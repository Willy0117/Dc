<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if ($user && $user->email) {
            $token = Password::broker('users')->createToken($user);
            $user->notify(new ResetPasswordNotification($token));
        }

        return back()->with('status', 'パスワード再設定用のリンクを送信しました(該当するIDが存在する場合)。');
    }
}
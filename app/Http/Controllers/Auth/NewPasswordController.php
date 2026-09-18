<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    public function create(Request $request): Response
    {
        $username = $request->username;
        $token = $request->route('token');

        $user = User::where('username', $username)->first();

        // ユーザーが存在しない、またはトークンが無効(期限切れ・使用済み含む)ならエラー扱いにする
        if (!$user || !Password::broker('users')->getRepository()->exists($user, $token)) {
            return Inertia::render('Auth/ResetPassword', [
                'username'    => $username,
                'token'       => $token,
                'tokenInvalid' => true,
            ]);
        }

        return Inertia::render('Auth/ResetPassword', [
            'username'     => $username,
            'token'        => $token,
            'tokenInvalid' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => 'required',
            'username' => 'required|string',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'username' => [__('passwords.user')],
            ]);
        }

        $broker = Password::broker('users');

        if (!$broker->getRepository()->exists($user, $request->token)) {
            throw ValidationException::withMessages([
                'username' => [__('passwords.token')],
            ]);
        }

        $user->forceFill([
            'password'       => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        $broker->getRepository()->delete($user);

        return to_route('login')->with('status', __('passwords.reset'));
    }
}
<?php

namespace App\Http\Controllers\PreRegister;

use App\Http\Controllers\Controller;
use App\Models\PreUser;
use App\Mail\PreRegisterMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PreRegisterController extends Controller
{
    public function store()
    {
        request()->validate([
            'email' => ['required', 'email'],
        ]);

        $preUser = PreUser::where('email', request('email'))->first();

        if ($preUser && $preUser->isVerified()) {
            throw ValidationException::withMessages([
                'email' => 'このメールアドレスはすでに確認済みです。',
            ]);
        }


        $preUser = PreUser::updateOrCreate(
            ['email' => request('email')],
            [
                'token' => Str::uuid(),
                'expires_at' => now()->addHours(24),
                'verified_at' => null,
            ]
        );

        Mail::to($preUser->email)
            ->send(new PreRegisterMail($preUser));
        // thans画面
        return redirect()->route('pre-register.thanks');

        //return back()->with('success', '確認メールを送信しました');
    }
}

<?php

namespace App\Http\Controllers\PreRegister;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PreUser;
use App\Mail\PreRegisterMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PreRegisterController extends Controller
{
    public function store(Request $request)
    {
        request()->validate([
            'email' => ['required', 'email'],
        ]);
        // ★ agent 判定（GET / POST どちらでも）
        $isAgent = $request->has('agent') || $request->boolean('is_agent');

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
                'agent' => $isAgent,
            ]
        );
        // 代理人申請の場合は?agentをurlに追加する
        Mail::to($preUser->email)
            ->send(new PreRegisterMail($preUser, $isAgent));
        // thans画面
        return redirect()->route('pre-register.thanks');

        //return back()->with('success', '確認メールを送信しました');
    }
}

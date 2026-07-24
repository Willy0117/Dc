<?php

namespace App\Listeners;

use Illuminate\Auth\Events\PasswordReset;

class RecordPasswordSetAt
{
    /**
     * パスワード設定(reset-password画面での更新)が完了したタイミングで発火する
     * Illuminate\Auth\Events\PasswordReset をハンドルする。
     *
     * これにより、以後は「初回パスワード設定済み」とみなし、
     * 更新請求(Stripe/請求書)のたびにパスワード設定メールが
     * 再送されないようにする。
     */
    public function handle(PasswordReset $event): void
    {
        $event->user->forceFill([
            'password_set_at' => now(),
        ])->save();
    }
}
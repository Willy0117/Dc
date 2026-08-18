<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordBase;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPasswordBase
{
    /**
     * MyPage(病院・先生)ユーザー向けのパスワード設定メール。
     * Fortify標準の文面(Laravelロゴ等)を、自院のブランディングに差し替える。
     */
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token'    => $this->token,
            'username' => $notifiable->username,
        ], false));

        return (new MailMessage)
            ->subject('【' . config('app.name') . '】パスワード設定のご案内')
            ->view('emails.reset-password', [
                'notifiable' => $notifiable,
                'url'        => $url,
            ]);
    }
}
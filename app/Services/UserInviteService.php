<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Password;

class UserInviteService
{
    /**
     * 病院(organization)のMyPageユーザー、および
     * emailを持つ先生(member)のMyPageユーザーに対して、
     * パスワード設定用リンクをメール送信する。
     *
     * 支払い完了(Stripe Webhook / 請求書の支払済み操作)後に
     * どちらからも呼び出せるよう共通化している。
     *
     * 病院用・先生用で同一メールアドレスになるケースがあるため、
     * Password::sendResetLink() の標準スロットリング(60秒)を回避し、
     * createToken() で直接トークンを発行して個別に送信する。
     */
    public function sendPasswordSetupMail(Organization $organization): void
    {
        // 1. 組織(病院)側のMyPageユーザー(初回=password_set_at未設定の場合のみ送る)
        $orgUser = User::where('organization_id', $organization->id)
            ->where('type', 1) // 1:病院(organization)
            ->first();

        if (!$orgUser) {
            report(new \RuntimeException(
                "MyPage user not found for organization_id={$organization->id}"
            ));
        } elseif (!$orgUser->password_set_at) {
            $this->forceSendResetLink($orgUser);
        }

        // 2. 先生(member)側のMyPageユーザー(emailが登録済み かつ 初回のみ)
        $memberUsers = User::whereIn('member_id', $organization->members()->pluck('id'))
            ->where('type', 2) // 2:先生(member)
            ->whereNotNull('email')
            ->whereNull('password_set_at')
            ->get();

        foreach ($memberUsers as $memberUser) {
            $this->forceSendResetLink($memberUser);
        }
    }

    /**
     * Password::sendResetLink() のスロットリングを回避して
     * 個別にパスワード設定メールを送信する。
     */
    private function forceSendResetLink(User $user): void
    {
        $token = Password::broker()->createToken($user);
        $user->notify(new ResetPasswordNotification($token));
    }
}
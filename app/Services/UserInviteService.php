<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use App\Models\ElearningInvitation;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;

class UserInviteService
{
    /**
     * 病院(organization)のMyPageユーザーに対してのみ、
     * パスワード設定用リンクをメール送信する。
     *
     * 支払い完了(Stripe Webhook / 請求書の支払済み操作)後に
     * どちらからも呼び出せるよう共通化している。
     *
     * 【変更点】従来ここで先生(member)側にも同時にPWメールを送っていたが、
     * 先生への送信は「簡易e-ラーニング受講完了後」に切り離した。
     * 先生への送信は sendElearningInvitationsForOrganization() /
     * sendMemberPasswordSetupMailIfEligible() を参照。
     */
    public function sendPasswordSetupMail(Organization $organization): void
    {
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
    }

    /**
     * 【新設】契約締結・入金確認後、所属する先生全員に
     * 簡易e-ラーニングの受講案内メールを送信する。
     * 既にinvitation発行済み・受講済みの先生は対象外（重複送信しない）。
     * 変更点4：後から追加された先生にも、同じ経路で個別に呼び出せる想定
     * （syncMembers()の新規member作成時にこちらを呼ぶよう変更する）。
     */
    public function sendElearningInvitationsForOrganization(Organization $organization): void
    {
        $members = $organization->members()->whereNotNull('email')->get();

        foreach ($members as $member) {
            $this->sendElearningInvitationIfNeeded($member);
        }
    }

    /**
     * 【新設】1名の先生に対して、必要であれば簡易e-ラーニング受講案内を送る。
     * 既に受講完了している場合、または送信済み(sent_at設定済み)で
     * まだ未受講の場合は再送しない（多重送信防止）。
     */
    public function sendElearningInvitationIfNeeded(Member $member): void
    {
        if (empty($member->email)) {
            return;
        }

        if ($member->hasCompletedElearning()) {
            return;
        }

        // 既にinvitationが1件でもあれば（未受講でも）再送しない
        if ($member->elearningInvitations()->exists()) {
            return;
        }

        $invitation = ElearningInvitation::issueFor($member);
        $url = route('elearning-invitations.show', $invitation->token);

        Mail::to($member->email)
            ->send(new \App\Mail\ElearningInvitationMail($invitation, $url));

        \Log::info('UserInviteService: 簡易e-ラーニング受講案内を送信', [
            'member_id'     => $member->id,
            'invitation_id' => $invitation->id,
        ]);
    }

    /**
     * 【新設】先生が簡易e-ラーニングを受講完了した際に呼ぶ。
     * その先生のMyPageユーザーに対して、PWメールを送信する
     * （病院側と同じロジックだが、対象を1名の先生に限定したもの）。
     */
    public function sendMemberPasswordSetupMailIfEligible(Member $member): void
    {
        $memberUser = User::where('member_id', $member->id)
            ->where('type', 2) // 2:先生(member)
            ->whereNotNull('email')
            ->whereNull('password_set_at')
            ->first();

        if (!$memberUser) {
            return;
        }

        $this->forceSendResetLink($memberUser);

        \Log::info('UserInviteService: 受講完了に伴いPW設定メールを送信', [
            'member_id' => $member->id,
            'user_id'   => $memberUser->id,
        ]);
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
<?php

namespace App\Mail;

use App\Models\PreUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PreRegisterMail extends Mailable
{
    use Queueable, SerializesModels;

    public PreUser $preUser;

    /**
     * Create a new message instance.
     */
    public function __construct(PreUser $preUser)
    {
        $this->preUser = $preUser;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->subject('全国中小建設工事業団体連合会 メールアドレスの確認')
            ->view('emails.pre_register')
            ->with([
                'url' => route('members.register', ['token' => $this->preUser->token]),
                'email' => $this->preUser->email,
            ]);
    }
}


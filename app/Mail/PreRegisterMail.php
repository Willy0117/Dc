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
    public bool $isAgent;

    /**
     * Create a new message instance.
     */
    public function __construct(PreUser $preUser, bool $isAgent = false)
    {
        $this->preUser = $preUser;
        $this->isAgent = $isAgent;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // ★ URL をここで組み立てる
        $url = route('members.register', [
            'token' => $this->preUser->token,
        ]);

        if ($this->isAgent) {
            $url .= '?agent';
        }

        return $this
            ->subject('全国中小建設工事業団体連合会 メールアドレスの確認')
            ->view('emails.pre_register')
            ->with([
                'url'   => $url,
                'email' => $this->preUser->email,
            ]);
    }    
}


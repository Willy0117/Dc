<?php

namespace App\Mail;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AgentRegistrationCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public $member;

    /**
     * Create a new message instance.
     */
    public function __construct($member)
    {
        $this->member = $member;
    }

    public function build()
    {
        return $this
            ->subject('【一般社団法人 全国中小建設工事業団体連合会】代理人による入会申込完了のお知らせ')
            ->view('emails.agent_registration_completed'); // ←ここが正しい
    }
}


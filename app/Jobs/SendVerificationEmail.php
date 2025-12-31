<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationMail; // メールクラス

class SendVerificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    // コンストラクタでユーザー情報を受け取る
    public function __construct($user)
    {
        $this->user = $user;
    }

    // キューで実行される処理
    public function handle()
    {
        Mail::to($this->user->email)->send(new VerificationMail($this->user));
    }
}


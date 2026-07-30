<?php

namespace App\Mail;

use App\Models\Certificate;
use App\Models\Order;
use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CertificateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public Video $video, public Certificate $certificate)
    {
    }

    public function build()
    {
        $downloadUrl = route('certificate.download', [$this->order->access_token, $this->video->id]);

        return $this->subject("【医療の質・安全学会】オンデマンドセミナー「{$this->video->title}」受講証明書のご案内　※配信専用")
            ->view('emails.certificate')
            ->with([
                'order' => $this->order,
                'video' => $this->video,
                'certificate' => $this->certificate,
                'downloadUrl' => $downloadUrl,
            ]);
    }
}

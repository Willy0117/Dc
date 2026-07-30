<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function build()
    {
        $watchUrl = route('watch.index', $this->order->access_token);

        $this->order->loadMissing('videoSet.videos');
        $videos = $this->order->videoSet->videos;

        return $this->subject('【医療の質・安全学会】オンデマンドセミナー　視聴用URLのご案内　※配信専用')
            ->view('emails.order_confirmation')
            ->with([
                'order' => $this->order,
                'watchUrl' => $watchUrl,
                'lectureCount' => $videos->count(),
                'totalMinutes' => $videos->sum('duration_minutes'),
            ]);
    }
}
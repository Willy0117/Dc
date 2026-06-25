<?php

namespace App\Mail;

use App\Models\Organization;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StripePaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Organization $organization,
        public Invoice $invoice,
    ) {}

    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: '【Alivio JAPAN】ライセンス料お支払いのご案内',
        );
    }

    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.stripe_payment',
            with: [
                'organization'  => $this->organization,
                'invoice'       => $this->invoice,
                'payment_link'  => $this->invoice->stripe_payment_link,
            ],
        );
    }
}
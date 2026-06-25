<?php

namespace App\Mail;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Organization $organization,
        public string $url,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【株式会社Alivio JAPAN / 医療法人社団祐優会N】ライセンス契約お申込みのご案内',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation',
            with: [
                'organization' => $this->organization,
                'url'          => $this->url,
            ],
        );
    }
}

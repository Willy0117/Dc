<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class LicenseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $organization,
        public string $fileName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ライセンス証のご送付',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.license',
            with: [
                'organization' => $this->organization,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk(config('filesystems.default'), $this->fileName)
                ->as('ライセンス証.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
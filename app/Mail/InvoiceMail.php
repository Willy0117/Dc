<?php

namespace App\Mail;

use App\Models\Organization;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Organization $organization,
        public Invoice $invoice,
        public string $pdfPath,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【株式会社Alivio JAPAN / 医療法人社団祐優会】請求書送付のご案内',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'organization' => $this->organization,
                'invoice'      => $this->invoice,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('public', $this->pdfPath)
                ->as('請求書_' . $this->invoice->invoice_no . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
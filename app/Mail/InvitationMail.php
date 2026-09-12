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
        // 変更点：件名がハードコードされており、常に特定の1病院名（テストデータ）が
        // 表示されてしまうバグがあった。本文（emails.invitation）と同じく
        // $this->organization->name を使って動的に組み立てるよう修正。
        return new Envelope(
            subject: '【' . config('mail.from.name') . ' / ' . $this->organization->name . '】ライセンス契約お申込みのご案内',
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
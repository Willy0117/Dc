<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BulkMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $mailSubject;
    public string $mailBody;
    public string $organizationName;

    public function __construct(
        string $mailSubject,
        string $mailBody,
        string $organizationName,
    ) {
        $this->mailSubject     = $mailSubject;
        $this->mailBody        = $mailBody;
        $this->organizationName = $organizationName;
    }

    public function build(): self
    {
        return $this->subject($this->mailSubject)
                    ->view('emails.bulk')
                    ->with([
                        'subject'           => $this->mailSubject,
                        'body'              => $this->mailBody,
                        'organization_name' => $this->organizationName,
                    ]);
    }
}
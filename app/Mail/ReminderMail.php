<?php
namespace App\Mail;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Organization $organization;

    public function __construct(Organization $organization)
    {
        $this->organization = $organization;
    }

    public function build(): self
    {
        return $this->subject('【更新のご案内】ライセンス契約更新日が近づいております')
                    ->view('emails.reminder')
                    ->with([
                        'organization'      => $this->organization,
                        'new_contract_date' => $this->organization->new_contract_date,
                    ]);
    }
}
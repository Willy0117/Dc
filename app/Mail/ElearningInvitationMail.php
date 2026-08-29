<?php
 
namespace App\Mail;
 
use App\Models\ElearningInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
 
class ElearningInvitationMail extends Mailable
{
    use Queueable, SerializesModels;
 
    public function __construct(
        public ElearningInvitation $invitation,
        public string $url,
    ) {}
 
    public function build()
    {
        $organizationName = $this->invitation->member->organization?->name ?? '';
        $companyName      = config('mail.from.name');
 
        return $this->subject("【{$companyName} / {$organizationName}】動注ライセンスe-ラーニング受講のお願い")
            ->view('emails.elearning-invitation', [
                'memberName' => $this->invitation->member->full_name,
                'url'        => $this->url,
            ]);
    }
}
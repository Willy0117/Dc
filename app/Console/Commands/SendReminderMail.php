<?php
namespace App\Console\Commands;

use App\Models\Organization;
use App\Mail\ReminderMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendReminderMail extends Command
{
    protected $signature   = 'reminder:send {--dry-run : メール送信せずに対象組織を表示}';
    protected $description = '45日前・15日前のリマインダーメールを自動送信';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if (!$dryRun && !config('app.reminder_mail_enabled')) {
            $this->info('リマインダーメール送信は無効です。');
            return;
        }
        
        $today  = Carbon::today();

        Organization::with('locationAddress')
            ->whereNotIn('contract_status', [2, 5])
            ->whereNotNull('contract_date')
            ->get()
            ->each(function ($org) use ($today, $dryRun) {
                $renewalDate = $org->new_contract_date
                    ? Carbon::parse($org->new_contract_date)
                    : Carbon::parse($org->contract_date)->addYear();

                $daysUntil = $today->diffInDays($renewalDate, false);

                $email = $org->locationAddress?->email;
                if (!$email) return;

                // 45日前：reminder_sent_atがNULLの場合のみ送信
                if ($daysUntil === 45 && !$org->reminder_sent_at) {
                    $this->info("45日前対象: {$org->name} ({$email})");
                    if (!$dryRun) {
                        Mail::to($email)->send(new ReminderMail($org, 45));
                        $org->update(['reminder_sent_at' => now()]);
                    }
                    return;
                }

                // 15日前：reminder_sent_atがある（45日前送信済み）場合のみ送信
                if ($daysUntil === 15 && $org->reminder_sent_at) {
                    $this->info("15日前対象: {$org->name} ({$email})");
                    if (!$dryRun) {
                        Mail::to($email)->send(new ReminderMail($org, 15));
                    }
                }
            });

        $this->info('完了');
    }
}
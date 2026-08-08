<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\User;
use App\Services\Notification\EmailService;
use App\Services\Notification\WhatsAppService;
use Illuminate\Console\Command;

class SendSubscriptionReminders extends Command
{
    protected $signature   = 'subscription:send-reminders {--days=7 : Warn schools expiring within N days}';
    protected $description = 'Send subscription expiry reminders to school admins';

    public function __construct(private EmailService $email, private WhatsAppService $whatsapp)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $days    = (int) $this->option('days');
        $cutoff  = now()->addDays($days);

        $schools = School::with('plan')
            ->where('is_active', true)
            ->whereBetween('plan_expires_at', [now(), $cutoff])
            ->get();

        $this->info("Found {$schools->count()} school(s) expiring within {$days} days.");

        foreach ($schools as $school) {
            $admin = User::where('school_id', $school->id)
                ->whereHas('roles', fn($q) => $q->where('name', 'admin'))
                ->first();

            if (!$admin) {
                continue;
            }

            $expiresAt = $school->plan_expires_at->toDateString();
            $planName  = $school->plan?->name ?? 'Unknown';

            if ($admin->email) {
                $this->email->sendSubscriptionExpiry($admin->email, $school->name, $expiresAt, $planName);
                $this->line("  Email sent to {$admin->email} for {$school->name}");
            }

            if ($admin->phone) {
                $msg = "Sikad Pro: Langganan *{$school->name}* (Plan {$planName}) akan berakhir pada *{$expiresAt}*. Perpanjang sekarang di panel super admin.";
                $this->whatsapp->send($admin->phone, $msg);
            }
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Communication\ReminderSchedule;
use App\Services\ReminderService;
use Illuminate\Console\Command;

class SendScheduledReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send scheduled reminders based on active reminder schedules';

    public function handle(ReminderService $service): int
    {
        $schedules = ReminderSchedule::where('is_active', true)->get();

        $totalSent = 0;

        foreach ($schedules as $schedule) {
            $this->info("Processing: {$schedule->name} (school_id={$schedule->school_id})");
            $sent = $service->processSchedule($schedule);
            $totalSent += $sent;
            $this->info("  Sent: {$sent} reminders");
        }

        $this->info("Total reminders sent: {$totalSent}");
        return self::SUCCESS;
    }
}

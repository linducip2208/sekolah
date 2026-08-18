<?php

namespace App\Console\Commands;

use App\Models\Academic\PtmSchedule;
use App\Models\User;
use App\Services\Notification\FcmService;
use Illuminate\Console\Command;

class SendPtReminders extends Command
{
    protected $signature = 'ptm:send-reminders {--days=7}';
    protected $description = 'Kirim pengingat PTM (Parent-Teacher Meeting) H-7 untuk guru dan orang tua';

    public function handle(FcmService $fcm): int
    {
        $days = (int) $this->option('days');

        $schedules = PtmSchedule::where('status', 'scheduled')
            ->where('meeting_date', '<=', now()->addDays($days)->toDateString())
            ->where('reminder_sent', false)
            ->with(['student.user', 'parent', 'teacher'])
            ->get();

        if ($schedules->isEmpty()) {
            $this->info("Tidak ada PTM yang perlu diingatkan dalam {$days} hari.");
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$schedules->count()} jadwal PTM:");

        $sentCount = 0;

        foreach ($schedules as $schedule) {
            $dateFormatted = $schedule->meeting_date->format('d M Y');
            $timeFormatted = $schedule->start_time;
            $studentName = $schedule->student?->user?->name ?? 'Siswa';
            $teacherName = $schedule->teacher?->name ?? 'Guru';
            $parentName  = $schedule->parent?->name ?? 'Orang Tua';
            $schoolId    = $schedule->school_id;

            $teacherTitle = "Ingat: PTM dengan {$parentName} (siswa {$studentName})";
            $teacherBody  = "Jadwal PTM pada {$dateFormatted} pukul {$timeFormatted}";

            $parentTitle = "Ingat: PTM dengan Guru {$teacherName}";
            $parentBody  = "Jadwal PTM {$studentName} pada {$dateFormatted} pukul {$timeFormatted}";

            $fcm->logAndSend($schoolId, [$schedule->teacher_id], 'ptm_reminder', $teacherTitle, $teacherBody, [
                'type' => 'ptm_reminder',
                'ptm_schedule_id' => $schedule->id,
            ]);

            $fcm->logAndSend($schoolId, [$schedule->parent_user_id], 'ptm_reminder', $parentTitle, $parentBody, [
                'type' => 'ptm_reminder',
                'ptm_schedule_id' => $schedule->id,
            ]);

            $schedule->update(['reminder_sent' => true]);
            $sentCount++;

            $this->line("  ✓ {$teacherName} ↔ {$parentName} ({$studentName}) — {$dateFormatted} {$timeFormatted}");
        }

        $this->info("Ringkasan: {$sentCount} pengingat PTM berhasil dikirim.");
        return self::SUCCESS;
    }
}

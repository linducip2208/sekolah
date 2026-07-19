<?php

namespace App\Services;

use App\Models\Academic\Student;
use App\Models\Academic\Staff;
use App\Models\Communication\ReminderLog;
use App\Models\Communication\ReminderSchedule;
use App\Services\Communication\WhatsAppNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReminderService
{
    public function generateMessage(ReminderSchedule $schedule, array $variables): string
    {
        $message = $schedule->message_template;

        $replacements = [
            '{nama}'       => $variables['nama'] ?? 'Pengguna',
            '{jumlah}'     => $variables['jumlah'] ?? '-',
            '{jatuh_tempo}' => $variables['jatuh_tempo'] ?? '-',
            '{link_bayar}'  => $variables['link_bayar'] ?? '#',
            '{sekolah}'    => $variables['sekolah'] ?? 'Sekolah',
            '{kelas}'      => $variables['kelas'] ?? '-',
            '{nis}'        => $variables['nis'] ?? '-',
            '{tanggal}'    => Carbon::now()->format('d M Y'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    public function sendReminder(ReminderSchedule $schedule, array $variables, string $phone, ?string $email = null): array
    {
        $message = $this->generateMessage($schedule, $variables);

        $result = ['success' => false, 'error' => 'No channel sent'];

        try {
            if ($schedule->reminder_type === 'wa') {
                $wa = app(WhatsAppNotificationService::class);
                $result = $wa->send($phone, $message, $schedule->school_id);
            } elseif ($schedule->reminder_type === 'sms') {
                $result = $this->sendSms($phone, $message, $schedule->school_id);
            } elseif ($schedule->reminder_type === 'email' && $email) {
                $result = $this->sendEmail($email, $schedule->name, $message);
            }

            ReminderLog::create([
                'school_id'            => $schedule->school_id,
                'reminder_schedule_id' => $schedule->id,
                'target_id'            => $variables['target_id'] ?? 0,
                'target_phone'         => $phone,
                'target_email'         => $email,
                'message_sent'         => $message,
                'channel'              => $schedule->reminder_type,
                'sent_at'              => now(),
                'status'               => $result['success'] ? 'success' : 'failed',
                'error_message'        => $result['error'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Reminder send failed', [
                'schedule_id' => $schedule->id,
                'error'       => $e->getMessage(),
            ]);
            $result = ['success' => false, 'error' => $e->getMessage()];

            ReminderLog::create([
                'school_id'            => $schedule->school_id,
                'reminder_schedule_id' => $schedule->id,
                'target_id'            => $variables['target_id'] ?? 0,
                'target_phone'         => $phone,
                'target_email'         => $email,
                'message_sent'         => $message,
                'channel'              => $schedule->reminder_type,
                'sent_at'              => now(),
                'status'               => 'failed',
                'error_message'        => $e->getMessage(),
            ]);
        }

        return $result;
    }

    public function processSchedule(ReminderSchedule $schedule): int
    {
        $daysBefore = (array) ($schedule->trigger_days_before ?? []);
        $sent = 0;

        if (!in_array('today', $daysBefore) && !in_array('on_due', $daysBefore)) {
            $today = Carbon::today();
            $shouldSend = false;

            foreach ($daysBefore as $day) {
                $targetDate = $today->copy()->addDays((int)$day);
                $hasItems = $this->hasItemsDue($schedule, $targetDate);
                if ($hasItems) {
                    $shouldSend = true;
                    break;
                }
            }

            if (!$shouldSend) {
                return 0;
            }
        }

        $targets = $this->getTargets($schedule);

        foreach ($targets as $target) {
            $variables = $this->buildVariables($schedule, $target);

            $alreadySent = ReminderLog::where('reminder_schedule_id', $schedule->id)
                ->where('target_id', $target['id'])
                ->where('sent_at', '>=', Carbon::today())
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $result = $this->sendReminder($schedule, $variables, $target['phone'], $target['email'] ?? null);
            if ($result['success']) {
                $sent++;
            }

            usleep(200000); // rate limit 5/sec
        }

        if ($sent > 0) {
            $schedule->update(['last_triggered_at' => now()]);
        }

        return $sent;
    }

    private function getTargets(ReminderSchedule $schedule): array
    {
        $schoolId = $schedule->school_id;
        $targets = [];

        match ($schedule->recipient_type) {
            'parent' => $targets = Student::where('school_id', $schoolId)
                ->select('id', 'user_id', 'guardian_name', 'guardian_phone', 'whatsapp_phone', 'admission_no')
                ->get()
                ->map(fn($s) => [
                    'id'    => $s->id,
                    'nama'  => $s->guardian_name ?? $s->user?->name ?? 'Orang Tua',
                    'phone' => $s->whatsapp_phone ?? $s->guardian_phone ?? $s->user?->phone ?? '',
                    'nis'   => $s->admission_no ?? '',
                ])
                ->filter(fn($t) => !empty($t['phone']))
                ->values()
                ->toArray(),
            'student' => $targets = Student::where('school_id', $schoolId)
                ->with('user:id,name,phone')
                ->get()
                ->map(fn($s) => [
                    'id'    => $s->id,
                    'nama'  => $s->user?->name ?? 'Siswa',
                    'phone' => $s->whatsapp_phone ?? $s->guardian_phone ?? $s->user?->phone ?? '',
                    'nis'   => $s->admission_no ?? '',
                ])
                ->filter(fn($t) => !empty($t['phone']))
                ->values()
                ->toArray(),
            'staff' => $targets = Staff::where('school_id', $schoolId)
                ->with('user:id,name,phone')
                ->get()
                ->map(fn($s) => [
                    'id'    => $s->id,
                    'nama'  => $s->user?->name ?? 'Staff',
                    'phone' => $s->whatsapp_phone ?? $s->user?->phone ?? '',
                ])
                ->filter(fn($t) => !empty($t['phone']))
                ->values()
                ->toArray(),
        };

        return $targets;
    }

    private function buildVariables(ReminderSchedule $schedule, array $target): array
    {
        $variables = [
            'nama'        => $target['nama'] ?? 'Pengguna',
            'target_id'   => $target['id'] ?? 0,
            'nis'         => $target['nis'] ?? '-',
            'jumlah'      => 'Rp -',
            'jatuh_tempo'  => Carbon::now()->addDays(7)->format('d M Y'),
            'link_bayar'   => '#',
            'sekolah'     => config('app.name', 'Sekolah'),
            'kelas'       => '-',
        ];

        if ($schedule->recipient_type === 'parent' || $schedule->recipient_type === 'student') {
            $student = Student::with(['classSection.classRoom', 'classSection.section'])->find($target['id']);
            if ($student) {
                $variables['kelas'] = $student->classSection?->display_name ?? '-';
                $variables['nis'] = $student->admission_no ?? '-';
            }
        }

        return $variables;
    }

    private function hasItemsDue(ReminderSchedule $schedule, Carbon $targetDate): bool
    {
        if ($schedule->recipient_type === 'staff') {
            return true;
        }

        return \App\Models\Finance\FeeInvoice::where('school_id', $schedule->school_id)
            ->whereDate('due_date', $targetDate)
            ->whereIn('status', ['unpaid', 'partial'])
            ->exists();
    }

    private function sendSms(string $phone, string $message, int $schoolId): array
    {
        // Delegate to SMS provider if configured
        $provider = \App\Models\Communication\NotificationProvider::where('school_id', $schoolId)
            ->where('transport', 'sms')
            ->where('is_active', true)
            ->first();

        if (!$provider) {
            return ['success' => false, 'error' => 'No active SMS provider'];
        }

        $adapter = new \App\Services\Communication\Adapters\WhatsAppAdapter($provider);
        return $adapter->send($phone, $message);
    }

    private function sendEmail(string $email, string $subject, string $message): array
    {
        try {
            \Illuminate\Support\Facades\Mail::raw($message, function ($mail) use ($email, $subject) {
                $mail->to($email)->subject($subject);
            });
            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

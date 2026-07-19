<?php

namespace App\Console\Commands;

use App\Models\Academic\Student;
use App\Jobs\SendWhatsAppNotification;
use Illuminate\Console\Command;

class SendPendingWhatsApp extends Command
{
    protected $signature = 'whatsapp:send-pending {--class_section_id= : Filter by class section} {--test : Dry run do not actually send}';
    protected $description = 'Send pending WhatsApp notifications to students/parents';

    public function handle(): int
    {
        $query = Student::with('user')->whereNotNull('whatsapp_phone');

        if ($classSectionId = $this->option('class_section_id')) {
            $query->where('class_section_id', $classSectionId);
        }

        $students = $query->get();
        $count = 0;
        $schoolId = null;

        foreach ($students as $student) {
            $phone = $student->whatsapp_phone ?? $student->guardian_phone;
            if (!$phone) continue;

            $sekolah = config('app.name', 'Sekolah');
            $nama = $student->user?->name ?? 'Siswa';

            $message = "Yth. Wali dari *{$nama}*,\n\n";
            $message .= "Pengumuman penting dari {$sekolah}.\n";
            $message .= "Silakan cek portal orang tua untuk informasi lengkap.\n\n";
            $message .= "Terima kasih.";

            if (!$this->option('test')) {
                SendWhatsAppNotification::dispatch($phone, $message, $student->school_id);
            }

            $schoolId = $student->school_id;
            $count++;
        }

        if ($this->option('test')) {
            $this->info("DRY RUN: Akan mengirim {$count} notifikasi WhatsApp.");
        } else {
            $this->info("{$count} notifikasi WhatsApp telah dijadwalkan. School ID: {$schoolId}");
        }

        return self::SUCCESS;
    }
}

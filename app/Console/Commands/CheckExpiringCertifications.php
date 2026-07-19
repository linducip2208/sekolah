<?php

namespace App\Console\Commands;

use App\Models\Academic\TeacherCertification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpiringCertifications extends Command
{
    protected $signature = 'certification:check-expiring {--days=30}';
    protected $description = 'Cek sertifikasi guru yang akan kadaluarsa dan kirim notifikasi';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $threshold = Carbon::now()->addDays($days);

        $expiring = TeacherCertification::where('expiry_date', '<=', $threshold)
            ->where('expiry_date', '>=', Carbon::now())
            ->with(['staff', 'staff.school'])
            ->get();

        if ($expiring->isEmpty()) {
            $this->info('Tidak ada sertifikasi yang akan kadaluarsa dalam ' . $days . ' hari.');
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$expiring->count()} sertifikasi yang akan kadaluarsa:");

        foreach ($expiring as $cert) {
            $remaining = Carbon::now()->diffInDays($cert->expiry_date, false);
            $teacherName = $cert->staff->name ?? 'Unknown';
            $schoolName = $cert->staff->school->name ?? 'Unknown';

            $this->line("  - {$cert->certification_name} — {$teacherName} ({$schoolName})");
            $this->line("    Kadaluarsa: {$cert->expiry_date->format('d M Y')} ({$remaining} hari lagi)");
            $this->line("    No. Sertifikat: {$cert->certificate_number}");

            // Notification dispatch would go here
            // event(new CertificationExpiring($cert));
        }

        $this->newLine();
        $this->info("Ringkasan: {$expiring->count()} sertifikasi perlu perpanjangan dalam {$days} hari.");
        $this->info("Sekolah terdampak: " . $expiring->pluck('staff.school.name')->unique()->implode(', '));

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Academic\Staff;
use App\Models\Academic\TeacherCertification;
use App\Models\Hr\EmploymentContract;
use App\Models\User;
use App\Services\Notification\FcmService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpiringDocuments extends Command
{
    protected $signature = 'documents:check-expiring {--days=30}';
    protected $description = 'Cek sertifikasi & kontrak kerja yang akan kadaluarsa, kirim notifikasi ke admin/principal';

    public function handle(FcmService $fcm): int
    {
        $days = (int) $this->option('days');
        $threshold = Carbon::now()->addDays($days);

        $certCount = $this->checkCertifications($threshold, $days, $fcm);
        $contractCount = $this->checkContracts($threshold, $days, $fcm);

        $total = $certCount + $contractCount;

        if ($total === 0) {
            $this->info("Tidak ada dokumen yang akan kadaluarsa dalam {$days} hari.");
            return self::SUCCESS;
        }

        $this->newLine();
        $this->info("Ringkasan: {$certCount} sertifikasi + {$contractCount} kontrak perlu perpanjangan dalam {$days} hari.");

        return self::SUCCESS;
    }

    private function checkCertifications(Carbon $threshold, int $days, FcmService $fcm): int
    {
        $expiring = TeacherCertification::withoutGlobalScopes()
            ->where('expiry_date', '<=', $threshold)
            ->where('expiry_date', '>=', Carbon::now())
            ->with(['staff', 'staff.school'])
            ->get();

        if ($expiring->isEmpty()) {
            $this->info("Tidak ada sertifikasi yang akan kadaluarsa.");
            return 0;
        }

        $this->info("Ditemukan {$expiring->count()} sertifikasi yang akan kadaluarsa:");

        $grouped = $expiring->groupBy(fn ($c) => $c->staff->school_id);

        foreach ($grouped as $schoolId => $certs) {
            $schoolName = $certs->first()->staff->school->name ?? 'Unknown';
            $adminIds = User::where('school_id', $schoolId)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'principal']))
                ->pluck('id')
                ->toArray();

            if (empty($adminIds)) continue;

            $lines = [];
            foreach ($certs as $cert) {
                $teacherName = $cert->staff->user->name ?? 'Unknown';
                $remaining = Carbon::now()->diffInDays($cert->expiry_date, false);
                $this->line("  - {$cert->certification_name} — {$teacherName} ({$schoolName})");
                $this->line("    Kadaluarsa: {$cert->expiry_date->format('d M Y')} ({$remaining} hari lagi)");
                $lines[] = "{$cert->certification_name} ({$teacherName}) — {$remaining} hari lagi";
            }

            $title = "⚠️ {$expiring->count()} sertifikasi akan kadaluarsa";
            $body = "Sertifikasi yang perlu perpanjangan:\n" . implode("\n", $lines);
            $fcm->logAndSend($schoolId, $adminIds, 'certification_expiry', $title, $body, [
                'type' => 'certification_expiry',
                'count' => $expiring->count(),
            ]);
        }

        return $expiring->count();
    }

    private function checkContracts(Carbon $threshold, int $days, FcmService $fcm): int
    {
        $expiring = EmploymentContract::withoutGlobalScopes()
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<=', $threshold)
            ->whereDate('end_date', '>=', Carbon::now())
            ->with(['staff', 'staff.school', 'staff.user'])
            ->get();

        if ($expiring->isEmpty()) {
            $this->info("Tidak ada kontrak kerja yang akan kadaluarsa.");
            return 0;
        }

        $this->info("Ditemukan {$expiring->count()} kontrak kerja yang akan kadaluarsa:");

        $grouped = $expiring->groupBy(fn ($c) => $c->staff->school_id);

        foreach ($grouped as $schoolId => $contracts) {
            $schoolName = $contracts->first()->staff->school->name ?? 'Unknown';
            $adminIds = User::where('school_id', $schoolId)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'principal']))
                ->pluck('id')
                ->toArray();

            if (empty($adminIds)) continue;

            $lines = [];
            foreach ($contracts as $contract) {
                $staffName = $contract->staff->user->name ?? 'Unknown';
                $remaining = Carbon::now()->diffInDays($contract->end_date, false);
                $this->line("  - {$staffName} — {$contract->type} ({$schoolName})");
                $this->line("    Berakhir: {$contract->end_date->format('d M Y')} ({$remaining} hari lagi)");
                $lines[] = "{$staffName} ({$contract->type}) — {$remaining} hari lagi";
            }

            $title = "⚠️ {$expiring->count()} kontrak kerja akan berakhir";
            $body = "Kontrak yang perlu diperpanjang:\n" . implode("\n", $lines);
            $fcm->logAndSend($schoolId, $adminIds, 'contract_expiry', $title, $body, [
                'type' => 'contract_expiry',
                'count' => $expiring->count(),
            ]);
        }

        return $expiring->count();
    }
}

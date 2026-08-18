<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Services\Automation\AutomationService;
use Illuminate\Console\Command;

class EvaluateAutomationRules extends Command
{
    protected $signature = 'automation:evaluate {school_id? : ID sekolah (default: semua)}';

    protected $description = 'Evaluasi aturan otomasi (pengingat SPP, absen beruntun, ulang tahun, kontrak, sertifikasi, PTM)';

    public function handle(AutomationService $service): int
    {
        $schools = $this->argument('school_id')
            ? collect([School::findOrFail($this->argument('school_id'))])
            : School::where('is_active', true)->get();

        $total = 0;

        foreach ($schools as $school) {
            $schoolId = $school->id;

            $total += $service->run($schoolId, 'fee_due_soon', $service->feeDueSoonEvents($schoolId));
            $total += $service->run($schoolId, 'fee_overdue', $service->feeOverdueEvents($schoolId));
            $total += $service->run($schoolId, 'student_absent_streak', $service->absentStreakEvents($schoolId));
            $total += $service->run($schoolId, 'birthday', $service->birthdayEvents($schoolId));
            $total += $service->run($schoolId, 'contract_expiry', $service->contractExpiryEvents($schoolId));
            $total += $service->run($schoolId, 'certification_expiry', $service->certificationExpiryEvents($schoolId));
            $total += $service->run($schoolId, 'ptm_reminder', $service->ptmReminderEvents($schoolId));
        }

        $this->info("Otomasi dievaluasi: {$total} aksi diproses.");

        return self::SUCCESS;
    }
}

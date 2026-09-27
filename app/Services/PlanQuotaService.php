<?php

namespace App\Services;

use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Enforce plan seat quotas (max_students / max_teachers).
 * Zero (or null) quota on the plan means unlimited.
 */
class PlanQuotaService
{
    public function assertCanAddStudents(int $schoolId, int $count = 1): void
    {
        $this->assertSeats($schoolId, 'max_students', ['student'], $count, 'siswa');
    }

    public function assertCanAddTeachers(int $schoolId, int $count = 1): void
    {
        $this->assertSeats($schoolId, 'max_teachers', ['teacher', 'homeroom_teacher'], $count, 'guru');
    }

    protected function assertSeats(int $schoolId, string $quotaColumn, array $roles, int $count, string $label): void
    {
        DB::transaction(function () use ($schoolId, $quotaColumn, $roles, $count, $label) {
            $school = School::withoutGlobalScopes()->lockForUpdate()->findOrFail($schoolId);
            $max = (int) ($school->plan?->{$quotaColumn} ?? 0);

            if ($max <= 0) {
                return;
            }

            $used = User::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->where('is_active', true)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', $roles))
                ->lockForUpdate()
                ->count();

            abort_if($used + $count > $max, 422, "Kuota {$label} paket {$school->plan->name} penuh ({$used}/{$max}). Upgrade paket untuk menambah.");
        });
    }
}

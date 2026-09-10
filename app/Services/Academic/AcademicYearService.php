<?php

namespace App\Services\Academic;

use App\Models\Academic\AcademicYear;
use App\Models\Academic\Semester;
use Illuminate\Support\Facades\DB;

class AcademicYearService
{
    public function activate(int $academicYearId): AcademicYear
    {
        $schoolId = $this->schoolId();

        return DB::transaction(function () use ($academicYearId, $schoolId) {
            AcademicYear::where('school_id', $schoolId)
                ->update(['is_active' => false]);

            $year = AcademicYear::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->findOrFail($academicYearId);
            $year->update(['is_active' => true]);

            if (! $year->semesters()->where('is_active', true)->exists()) {
                $year->semesters()->oldest('start_date')->first()
                    ?->update(['is_active' => true]);
            }

            return $year->fresh('semesters');
        });
    }

    public function activateSemester(int $semesterId): Semester
    {
        $schoolId = $this->schoolId();

        return DB::transaction(function () use ($semesterId, $schoolId) {
            $semester = Semester::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->findOrFail($semesterId);

            Semester::where('school_id', $schoolId)
                ->where('academic_year_id', $semester->academic_year_id)
                ->update(['is_active' => false]);

            $semester->update(['is_active' => true]);

            return $semester->fresh();
        });
    }

    private function schoolId(): int
    {
        return (int) auth()->user()->school_id;
    }
}

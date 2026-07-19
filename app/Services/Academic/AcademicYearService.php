<?php

namespace App\Services\Academic;

use App\Models\Academic\AcademicYear;
use Illuminate\Support\Facades\DB;

class AcademicYearService
{
    public function activate(int $academicYearId): AcademicYear
    {
        return DB::transaction(function () use ($academicYearId) {
            AcademicYear::where('school_id', auth()->user()->school_id)
                ->update(['is_active' => false]);

            $year = AcademicYear::findOrFail($academicYearId);
            $year->update(['is_active' => true]);

            if (!$year->semesters()->where('is_active', true)->exists()) {
                $year->semesters()->oldest('start_date')->first()
                    ?->update(['is_active' => true]);
            }

            return $year->fresh('semesters');
        });
    }

    public function activateSemester(int $semesterId): \App\Models\Academic\Semester
    {
        return DB::transaction(function () use ($semesterId) {
            $semester = \App\Models\Academic\Semester::findOrFail($semesterId);

            \App\Models\Academic\Semester::where('academic_year_id', $semester->academic_year_id)
                ->update(['is_active' => false]);

            $semester->update(['is_active' => true]);
            return $semester->fresh();
        });
    }
}

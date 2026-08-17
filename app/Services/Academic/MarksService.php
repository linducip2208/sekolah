<?php

namespace App\Services\Academic;

use App\Models\Academic\GradeRule;
use App\Models\Academic\GradeSystem;
use App\Models\Academic\Mark;
use App\Models\Academic\ReportCard;
use App\Models\Academic\Semester;
use App\Models\Academic\Student;

class MarksService
{
    public function bulkSave(array $marksData): int
    {
        $schoolId = auth()->user()->school_id;
        $count    = 0;

        foreach ($marksData as $row) {
            $percentage = ($row['obtained_marks'] / $row['total_marks']) * 100;
            $grade      = $this->resolveGrade($schoolId, $percentage);

            Mark::updateOrCreate(
                [
                    'school_id'  => $schoolId,
                    'student_id' => $row['student_id'],
                    'subject_id' => $row['subject_id'],
                    'semester_id'=> $row['semester_id'],
                    'exam_id'    => $row['exam_id'] ?? null,
                ],
                [
                    'obtained_marks' => $row['obtained_marks'],
                    'total_marks'    => $row['total_marks'],
                    'grade'          => $grade,
                ]
            );
            $count++;
        }

        return $count;
    }

    public function resolveGrade(int $schoolId, float $percentage): ?string
    {
        $system = GradeSystem::where('school_id', $schoolId)
            ->where('is_active', true)
            ->with('rules')
            ->first();

        if (!$system) {
            return null;
        }

        $rule = $system->rules
            ->where('min_percent', '<=', $percentage)
            ->where('max_percent', '>=', $percentage)
            ->first();

        return $rule?->grade;
    }

    public function generateReportCards(int $semesterId): int
    {
        $schoolId = auth()->user()->school_id;
        $semester = Semester::findOrFail($semesterId);

        $students = Student::where('school_id', $schoolId)->get();
        $count    = 0;

        foreach ($students as $student) {
            $marks = Mark::where('student_id', $student->id)
                ->where('semester_id', $semesterId)
                ->where('school_id', $schoolId)
                ->get();

            if ($marks->isEmpty()) {
                continue;
            }

            $avgPct = round($marks->avg('percentage'), 2);
            $grade  = $this->resolveGrade($schoolId, $avgPct);

            ReportCard::updateOrCreate(
                [
                    'school_id'  => $schoolId,
                    'student_id' => $student->id,
                    'semester_id'=> $semesterId,
                ],
                [
                    'total_percentage' => $avgPct,
                    'overall_grade'    => $grade,
                    'is_published'     => false,
                    'verification_token' => \Illuminate\Support\Str::random(40),
                ]
            );
            $count++;
        }

        $this->calculateRankings($semesterId, $schoolId);

        return $count;
    }

    private function calculateRankings(int $semesterId, int $schoolId): void
    {
        $cards = ReportCard::where('semester_id', $semesterId)
            ->where('school_id', $schoolId)
            ->orderByDesc('total_percentage')
            ->get();

        $cards->each(function ($card, $index) use ($cards) {
            $card->update(['rank' => $index + 1]);
        });
    }
}

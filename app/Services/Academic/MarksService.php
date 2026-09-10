<?php

namespace App\Services\Academic;

use App\Models\Academic\Exam;
use App\Models\Academic\GradeSystem;
use App\Models\Academic\Mark;
use App\Models\Academic\ReportCard;
use App\Models\Academic\Semester;
use App\Models\Academic\Student;
use App\Models\Academic\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarksService
{
    public function bulkSave(array $marksData): int
    {
        $schoolId = (int) auth()->user()->school_id;
        $this->validateRows($schoolId, $marksData);

        return DB::transaction(function () use ($schoolId, $marksData): int {
            foreach ($marksData as $row) {
                $this->persistMark($schoolId, $row);
            }

            return count($marksData);
        });
    }

    public function saveSystemMark(int $schoolId, array $row): Mark
    {
        $this->validateRows($schoolId, [$row]);

        return DB::transaction(fn (): Mark => $this->persistMark($schoolId, $row));
    }

    public function update(Mark $mark, array $data): Mark
    {
        $schoolId = (int) auth()->user()->school_id;
        abort_unless((int) $mark->school_id === $schoolId, 404);
        $obtained = (int) ($data['obtained_marks'] ?? $mark->obtained_marks);
        $total = (int) ($data['total_marks'] ?? $mark->total_marks);
        abort_if($obtained > $total, 422, 'Nilai diperoleh tidak boleh melebihi nilai maksimum.');
        $this->assertReportCardIsEditable($schoolId, (int) $mark->student_id, (int) $mark->semester_id);

        $mark->update([
            'obtained_marks' => $obtained,
            'total_marks' => $total,
            'grade' => $this->resolveGrade($schoolId, ($obtained / $total) * 100),
        ]);

        return $mark->fresh();
    }

    public function resolveGrade(int $schoolId, float $percentage): ?string
    {
        $system = GradeSystem::where('school_id', $schoolId)
            ->where('is_active', true)
            ->with('rules')
            ->first();

        if (! $system) {
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
        $semester = Semester::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->findOrFail($semesterId);

        $students = Student::where('school_id', $schoolId)->get();
        $count = 0;

        foreach ($students as $student) {
            $marks = Mark::where('student_id', $student->id)
                ->where('semester_id', $semesterId)
                ->where('school_id', $schoolId)
                ->get();

            if ($marks->isEmpty()) {
                continue;
            }

            $avgPct = round($marks->avg('percentage'), 2);
            $grade = $this->resolveGrade($schoolId, $avgPct);

            $card = ReportCard::firstOrNew(
                [
                    'school_id' => $schoolId,
                    'student_id' => $student->id,
                    'semester_id' => $semesterId,
                ]
            );

            if ($card->exists && in_array($card->status, ['submitted', 'approved', 'locked'], true)) {
                continue;
            }

            $card->fill([
                'total_percentage' => $avgPct,
                'overall_grade' => $grade,
                'is_published' => false,
                'status' => $card->status ?: 'draft',
                'verification_token' => $card->verification_token ?: Str::random(40),
            ])->save();
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

        $cards->each(function ($card, $index) {
            if ($card->status !== 'locked') {
                $card->update(['rank' => $index + 1]);
            }
        });
    }

    private function validateRows(int $schoolId, array $rows): void
    {
        $keys = [];

        foreach ($rows as $row) {
            $key = implode(':', [
                $row['student_id'],
                $row['subject_id'],
                $row['semester_id'],
                $row['exam_id'] ?? 'null',
            ]);
            abort_if(isset($keys[$key]), 422, 'Baris nilai duplikat dalam satu permintaan.');
            $keys[$key] = true;

            abort_if((int) $row['obtained_marks'] > (int) $row['total_marks'], 422, 'Nilai diperoleh tidak boleh melebihi nilai maksimum.');
            abort_unless(Student::withoutGlobalScopes()->where('school_id', $schoolId)->whereKey($row['student_id'])->exists(), 422, 'Siswa tidak berasal dari sekolah aktif.');
            abort_unless(Subject::withoutGlobalScopes()->where('school_id', $schoolId)->whereKey($row['subject_id'])->exists(), 422, 'Mata pelajaran tidak berasal dari sekolah aktif.');
            abort_unless(Semester::withoutGlobalScopes()->where('school_id', $schoolId)->whereKey($row['semester_id'])->exists(), 422, 'Semester tidak berasal dari sekolah aktif.');

            if (! empty($row['exam_id'])) {
                abort_unless(Exam::withoutGlobalScopes()->where('school_id', $schoolId)->whereKey($row['exam_id'])->exists(), 422, 'Ujian tidak berasal dari sekolah aktif.');
            }
        }
    }

    private function persistMark(int $schoolId, array $row): Mark
    {
        $this->assertReportCardIsEditable($schoolId, (int) $row['student_id'], (int) $row['semester_id']);
        $percentage = ($row['obtained_marks'] / $row['total_marks']) * 100;
        $grade = $row['grade'] ?? $this->resolveGrade($schoolId, $percentage);

        return Mark::updateOrCreate(
            [
                'school_id' => $schoolId,
                'student_id' => $row['student_id'],
                'subject_id' => $row['subject_id'],
                'semester_id' => $row['semester_id'],
                'exam_id' => $row['exam_id'] ?? null,
            ],
            [
                'obtained_marks' => $row['obtained_marks'],
                'total_marks' => $row['total_marks'],
                'grade' => $grade,
            ]
        );
    }

    private function assertReportCardIsEditable(int $schoolId, int $studentId, int $semesterId): void
    {
        $locked = ReportCard::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->where('semester_id', $semesterId)
            ->where('status', 'locked')
            ->exists();

        abort_if($locked, 423, 'Nilai berada pada rapor yang sudah dikunci. Ajukan pembukaan kembali melalui workflow.');
    }
}

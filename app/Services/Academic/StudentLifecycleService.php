<?php

namespace App\Services\Academic;

use App\Models\Academic\Student;
use App\Models\Academic\StudentStatusHistory;
use App\Models\Academic\ClassSection;
use Illuminate\Support\Facades\DB;

class StudentLifecycleService
{
    /** Allowed status transitions. */
    public const TRANSITIONS = [
        'applicant'   => ['enrolled', 'withdrawn'],
        'enrolled'    => ['active', 'withdrawn'],
        'active'      => ['transferred', 'graduated', 'withdrawn'],
        'graduated'   => ['alumni'],
        'transferred' => [],
        'alumni'      => [],
        'withdrawn'   => ['active'], // re-admission
    ];

    public const LABELS = [
        'applicant' => 'Pendaftar',
        'enrolled'  => 'Terdaftar',
        'active'    => 'Aktif',
        'transferred' => 'Pindah',
        'graduated' => 'Lulus',
        'alumni'    => 'Alumni',
        'withdrawn' => 'Mengundurkan Diri',
    ];

    /** Transition a student status with validation + audit history + notification. */
    public function transition(Student $student, string $toStatus, ?string $note = null, ?int $changedBy = null): Student
    {
        $from = $student->status;

        abort_if(
            !in_array($toStatus, self::TRANSITIONS[$from] ?? [], true),
            422,
            "Transisi status '{$from}' → '{$toStatus}' tidak diizinkan."
        );

        DB::transaction(function () use ($student, $from, $toStatus, $note, $changedBy) {
            $updates = ['status' => $toStatus];

            if ($toStatus === 'enrolled' && !$student->enrolled_at) {
                $updates['enrolled_at'] = now()->toDateString();
            }
            if ($toStatus === 'graduated') {
                $updates['graduated_at'] = now()->toDateString();
            }
            if ($toStatus === 'transferred') {
                $updates['transferred_at'] = now()->toDateString();
            }

            $student->update($updates);

            StudentStatusHistory::create([
                'school_id'  => $student->school_id,
                'student_id' => $student->id,
                'from_status'=> $from,
                'to_status'  => $toStatus,
                'changed_by' => $changedBy ?? auth()->id(),
                'note'       => $note,
            ]);

            activity('student_lifecycle')
                ->causedBy($changedBy ?? auth()->user())
                ->performedOn($student)
                ->withProperties(['from' => $from, 'to' => $toStatus])
                ->log("Status siswa berubah: {$from} → {$toStatus}");
        });

        return $student->fresh();
    }

    /** Promote a student to a new class section (recorded as a lifecycle event). */
    public function promote(Student $student, int $newClassSectionId, ?string $note = null): Student
    {
        $section = ClassSection::where('school_id', $student->school_id)
            ->where('id', $newClassSectionId)
            ->firstOrFail();

        DB::transaction(function () use ($student, $section, $note) {
            $student->update(['class_section_id' => $section->id]);

            StudentStatusHistory::create([
                'school_id'  => $student->school_id,
                'student_id' => $student->id,
                'from_status'=> $student->status,
                'to_status'  => $student->status,
                'changed_by' => auth()->id(),
                'note'       => $note ?: 'Promosi ke ' . ($section->classRoom?->name ?? '') . ' ' . ($section->section?->name ?? ''),
            ]);
        });

        return $student->fresh();
    }

    /** Batch promote all active students of a class to the next class section. */
    public function bulkPromote(int $schoolId, int $fromClassSectionId, int $toClassSectionId): int
    {
        $students = Student::where('school_id', $schoolId)
            ->where('class_section_id', $fromClassSectionId)
            ->where('status', 'active')
            ->get();

        $count = 0;
        foreach ($students as $student) {
            $this->promote($student, $toClassSectionId, 'Kenaikan kelas massal');
            $count++;
        }

        return $count;
    }
}

<?php

namespace App\Services\Academic;

use App\Models\Academic\Attendance;
use App\Models\Academic\ClassSection;
use App\Models\User;
use Illuminate\Support\Collection;

class AttendanceService
{
    public function bulkMark(int $classSectionId, string $date, array $records, User $teacher): array
    {
        $this->authorizeTeacher($classSectionId, $teacher);

        $upsertData = collect($records)->map(fn($r) => [
            'school_id'        => $teacher->school_id,
            'student_id'       => $r['student_id'],
            'class_section_id' => $classSectionId,
            'marked_by'        => $teacher->id,
            'date'             => $date,
            'status'           => $r['status'],
            'note'             => $r['note'] ?? null,
            'updated_at'       => now(),
            'created_at'       => now(),
        ])->toArray();

        Attendance::upsert(
            $upsertData,
            ['school_id', 'student_id', 'date'],
            ['status', 'note', 'marked_by', 'updated_at']
        );

        $absentIds = collect($records)
            ->where('status', 'absent')
            ->pluck('student_id')
            ->toArray();

        if (!empty($absentIds)) {
            \App\Jobs\NotifyAbsenceJob::dispatch($absentIds, $date, $teacher->school_id);
        }

        return $this->getSummary($classSectionId, $date);
    }

    public function getSummary(int $classSectionId, string $date): array
    {
        $records = Attendance::where('class_section_id', $classSectionId)
            ->where('date', $date)
            ->get();

        return [
            'present'  => $records->where('status', 'present')->count(),
            'absent'   => $records->where('status', 'absent')->count(),
            'late'     => $records->where('status', 'late')->count(),
            'half_day' => $records->where('status', 'half_day')->count(),
            'on_leave' => $records->where('status', 'on_leave')->count(),
        ];
    }

    public function getStudentSummary(int $studentId, string $fromDate, string $toDate): array
    {
        $records = Attendance::where('student_id', $studentId)
            ->whereBetween('date', [$fromDate, $toDate])
            ->get();

        $total   = $records->count();
        $present = $records->whereIn('status', ['present', 'late', 'half_day'])->count();

        return [
            'total_days'     => $total,
            'present'        => $records->where('status', 'present')->count(),
            'absent'         => $records->where('status', 'absent')->count(),
            'late'           => $records->where('status', 'late')->count(),
            'half_day'       => $records->where('status', 'half_day')->count(),
            'on_leave'       => $records->where('status', 'on_leave')->count(),
            'attendance_pct' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
        ];
    }

    private function authorizeTeacher(int $classSectionId, User $teacher): void
    {
        if ($teacher->hasRole(['admin', 'super_admin'])) {
            return;
        }

        $isTeacherOfClass = ClassSection::where('id', $classSectionId)
            ->where(function ($q) use ($teacher) {
                $q->where('class_teacher_id', $teacher->id)
                  ->orWhereHas('subjects', fn($s) => $s->where('teacher_id', $teacher->id));
            })
            ->exists();

        if (!$isTeacherOfClass) {
            abort(403, 'You are not authorized to mark attendance for this class.');
        }
    }
}

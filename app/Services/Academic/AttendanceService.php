<?php

namespace App\Services\Academic;

use App\Jobs\NotifyAbsenceJob;
use App\Models\Academic\Attendance;
use App\Models\Academic\AttendanceLock;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Student;
use App\Models\User;
use App\Models\Workflow\WorkflowRequest;
use App\Services\Workflow\WorkflowService;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function bulkMark(int $classSectionId, string $date, array $records, User $teacher): array
    {
        $classSection = $this->authorizeTeacher($classSectionId, $teacher);
        $this->assertUnlocked($classSection, $date);
        $records = $this->validateStudentRecords($classSection, $records);

        $now = now();
        $upsertData = collect($records)->map(fn ($r) => [
            'school_id' => $teacher->school_id,
            'student_id' => $r['student_id'],
            'class_section_id' => $classSection->id,
            'marked_by' => $teacher->id,
            'date' => $date,
            'status' => $r['status'],
            'note' => $r['note'] ?? null,
            'updated_at' => $now,
            'created_at' => $now,
        ])->toArray();

        DB::transaction(function () use ($upsertData) {
            Attendance::upsert(
                $upsertData,
                ['school_id', 'student_id', 'date'],
                ['status', 'note', 'marked_by', 'class_section_id', 'updated_at'],
            );
        });

        $absentIds = collect($records)
            ->where('status', 'absent')
            ->pluck('student_id')
            ->toArray();

        if (! empty($absentIds)) {
            NotifyAbsenceJob::dispatch($absentIds, $date, $teacher->school_id)->afterCommit();
        }

        return $this->getSummary($classSection->id, $date, (int) $teacher->school_id);
    }

    public function getSummary(int $classSectionId, string $date, ?int $schoolId = null): array
    {
        $records = Attendance::where('class_section_id', $classSectionId)
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->where('date', $date)
            ->get();

        return [
            'present' => $records->where('status', 'present')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'late' => $records->where('status', 'late')->count(),
            'half_day' => $records->where('status', 'half_day')->count(),
            'on_leave' => $records->where('status', 'on_leave')->count(),
        ];
    }

    public function getStudentSummary(int $studentId, string $fromDate, string $toDate): array
    {
        $records = Attendance::where('student_id', $studentId)
            ->whereBetween('date', [$fromDate, $toDate])
            ->get();

        $total = $records->count();
        $present = $records->whereIn('status', ['present', 'late', 'half_day'])->count();

        return [
            'total_days' => $total,
            'present' => $records->where('status', 'present')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'late' => $records->where('status', 'late')->count(),
            'half_day' => $records->where('status', 'half_day')->count(),
            'on_leave' => $records->where('status', 'on_leave')->count(),
            'attendance_pct' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
        ];
    }

    public function update(Attendance $attendance, array $data, User $teacher): Attendance
    {
        $classSection = $this->authorizeTeacher((int) $attendance->class_section_id, $teacher);
        abort_unless((int) $attendance->school_id === (int) $teacher->school_id, 403);
        $this->assertUnlocked($classSection, $attendance->date->toDateString());
        abort_unless(
            Student::withoutGlobalScopes()
                ->where('school_id', $classSection->school_id)
                ->where('class_section_id', $classSection->id)
                ->whereKey($attendance->student_id)
                ->exists(),
            422,
            'Siswa tidak berasal dari rombel dan sekolah aktif.',
        );

        $attendance->update($data);

        return $attendance->fresh();
    }

    public function lockDate(int $classSectionId, string $date, User $actor): AttendanceLock
    {
        $classSection = $this->authorizeTeacher($classSectionId, $actor);

        return AttendanceLock::updateOrCreate(
            [
                'school_id' => $actor->school_id,
                'class_section_id' => $classSection->id,
                'date' => $date,
            ],
            [
                'is_locked' => true,
                'locked_by' => $actor->id,
                'locked_at' => now(),
                'reopened_by' => null,
                'reopened_at' => null,
                'reopen_reason' => null,
            ],
        );
    }

    public function reopenDate(int $classSectionId, string $date, string $reason, User $actor): AttendanceLock
    {
        $this->requirePermission($actor, 'attendance.reopen');
        $classSection = ClassSection::withoutGlobalScopes()
            ->where('school_id', $actor->school_id)
            ->findOrFail($classSectionId);

        $lock = AttendanceLock::withoutGlobalScopes()
            ->where('school_id', $actor->school_id)
            ->where('class_section_id', $classSection->id)
            ->whereDate('date', $date)
            ->firstOrFail();
        $lock->update([
            'is_locked' => false,
            'reopened_by' => $actor->id,
            'reopened_at' => now(),
            'reopen_reason' => $reason,
        ]);

        return $lock->fresh();
    }

    public function requestCorrection(Attendance $attendance, array $data, User $actor): WorkflowRequest
    {
        abort_unless((int) $attendance->school_id === (int) $actor->school_id, 403);
        $classSection = ClassSection::withoutGlobalScopes()
            ->where('school_id', $actor->school_id)
            ->findOrFail($attendance->class_section_id);
        $this->assertStudentBelongsToClass($classSection, (int) $attendance->student_id);

        $hasPending = WorkflowRequest::where('school_id', $actor->school_id)
            ->where('type', 'attendance_correction')
            ->whereIn('status', ['submitted', 'under_review'])
            ->where('payload->attendance_id', $attendance->id)
            ->exists();
        abort_if($hasPending, 422, 'Koreksi absensi ini sudah menunggu persetujuan.');

        return app(WorkflowService::class)->create($actor->school_id, $actor->id, [
            'type' => 'attendance_correction',
            'title' => "Koreksi absensi {$attendance->date->format('Y-m-d')}",
            'description' => $data['reason'],
            'payload' => [
                'attendance_id' => $attendance->id,
                'old_status' => $attendance->status,
                'old_note' => $attendance->note,
                'requested_status' => $data['status'],
                'requested_note' => $data['note'] ?? null,
            ],
        ]);
    }

    public function applyApprovedCorrection(WorkflowRequest $request): Attendance
    {
        $payload = (array) $request->payload;

        return DB::transaction(function () use ($request, $payload) {
            $attendance = Attendance::withoutGlobalScopes()
                ->where('school_id', $request->school_id)
                ->whereKey($payload['attendance_id'] ?? 0)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($attendance->status === ($payload['old_status'] ?? null), 409, 'Absensi sudah berubah sebelum koreksi disetujui.');
            $attendance->update([
                'status' => $payload['requested_status'],
                'note' => $payload['requested_note'] ?? null,
                'marked_by' => $request->approver_id ?? $attendance->marked_by,
            ]);

            return $attendance->fresh();
        });
    }

    private function assertUnlocked(ClassSection $classSection, string $date): void
    {
        $locked = AttendanceLock::where('school_id', $classSection->school_id)
            ->where('class_section_id', $classSection->id)
            ->whereDate('date', $date)
            ->where('is_locked', true)
            ->exists();
        abort_if($locked, 423, 'Absensi pada tanggal ini sudah dikunci. Ajukan koreksi melalui workflow.');
    }

    private function assertStudentBelongsToClass(ClassSection $classSection, int $studentId): void
    {
        abort_unless(Student::withoutGlobalScopes()
            ->where('school_id', $classSection->school_id)
            ->where('class_section_id', $classSection->id)
            ->whereKey($studentId)
            ->exists(), 422, 'Siswa tidak berasal dari rombel aktif.');
    }

    private function requirePermission(User $actor, string $permission): void
    {
        abort_unless($actor->can($permission), 403, 'Tidak memiliki izin untuk aksi absensi ini.');
    }

    private function validateStudentRecords(ClassSection $classSection, array $records): array
    {
        $studentIds = collect($records)->pluck('student_id')->map(fn ($id) => (int) $id)->unique()->values();
        $validCount = Student::withoutGlobalScopes()
            ->where('school_id', $classSection->school_id)
            ->where('class_section_id', $classSection->id)
            ->whereIn('id', $studentIds)
            ->count();

        abort_unless($validCount === $studentIds->count(), 422, 'Semua siswa harus berasal dari rombel dan sekolah aktif.');

        return collect($records)->map(fn ($record) => [
            'student_id' => (int) $record['student_id'],
            'status' => $record['status'],
            'note' => $record['note'] ?? null,
        ])->values()->all();
    }

    private function authorizeTeacher(int $classSectionId, User $teacher): ClassSection
    {
        $classSection = ClassSection::withoutGlobalScopes()
            ->where('school_id', $teacher->school_id)
            ->findOrFail($classSectionId);

        if ($teacher->hasRole(['admin', 'super_admin']) || $teacher->can('attendance.manage')) {
            return $classSection;
        }

        $isTeacherOfClass = ClassSection::withoutGlobalScopes()
            ->where('school_id', $teacher->school_id)
            ->where('id', $classSectionId)
            ->where(function ($q) use ($teacher) {
                $q->where('class_teacher_id', $teacher->id)
                    ->orWhereHas('subjects', fn ($s) => $s->where('teacher_id', $teacher->id));
            })
            ->exists();

        if (! $isTeacherOfClass) {
            abort(403, 'You are not authorized to mark attendance for this class.');
        }

        return $classSection;
    }
}

<?php

namespace App\Services;

use App\Models\Academic\Attendance;
use App\Models\Academic\Mark;
use App\Models\Academic\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfflineSyncService
{
    private int $schoolId;

    public function __construct()
    {
        $this->schoolId = auth()->user()->school_id;
    }

    public function processBatch(array $records): array
    {
        $processed = 0;
        $failed = 0;
        $errors = [];
        $results = [];

        DB::beginTransaction();

        try {
            foreach ($records as $index => $record) {
                try {
                    $result = $this->processRecord($record);
                    if ($result['success']) {
                        $processed++;
                        $results[] = [
                            'index'      => $index,
                            'status'     => 'processed',
                            'local_id'   => $record['local_id'] ?? null,
                            'server_id'  => $result['id'] ?? null,
                        ];
                    } else {
                        $failed++;
                        $errors[] = [
                            'index'  => $index,
                            'error'  => $result['error'] ?? 'Unknown error',
                            'local_id' => $record['local_id'] ?? null,
                        ];
                        $results[] = [
                            'index'      => $index,
                            'status'     => 'failed',
                            'local_id'   => $record['local_id'] ?? null,
                            'error'      => $result['error'] ?? 'Unknown error',
                        ];
                    }
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('Offline sync record failed', [
                        'index' => $index,
                        'record'=> $record,
                        'error' => $e->getMessage(),
                    ]);

                    $errors[] = [
                        'index'  => $index,
                        'error'  => $e->getMessage(),
                        'local_id' => $record['local_id'] ?? null,
                    ];
                    $results[] = [
                        'index'      => $index,
                        'status'     => 'failed',
                        'local_id'   => $record['local_id'] ?? null,
                        'error'      => $e->getMessage(),
                    ];
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Offline sync batch failed', ['error' => $e->getMessage()]);
            throw $e;
        }

        return [
            'processed' => $processed,
            'failed'    => $failed,
            'total'     => count($records),
            'errors'    => $errors,
            'results'   => $results,
        ];
    }

    private function processRecord(array $record): array
    {
        $type = $record['type'] ?? '';

        return match ($type) {
            'attendance' => $this->syncAttendance($record),
            'mark'       => $this->syncMark($record),
            default      => ['success' => false, 'error' => "Unknown record type: {$type}"],
        };
    }

    private function syncAttendance(array $record): array
    {
        $studentId = $record['student_id'] ?? null;
        $classSectionId = $record['class_section_id'] ?? null;
        $date = $record['date'] ?? now()->toDateString();
        $status = $record['status'] ?? 'present';
        $note = $record['note'] ?? null;
        $localId = $record['local_id'] ?? null;

        $validStatuses = ['present', 'absent', 'late', 'on_leave', 'sick'];
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'error' => "Invalid status: {$status}"];
        }

        $student = Student::find($studentId);
        if (!$student) {
            return ['success' => false, 'error' => "Student not found: {$studentId}"];
        }

        $existing = Attendance::where('student_id', $studentId)
            ->where('date', $date)
            ->first();

        if ($existing) {
            $existing->update([
                'status'           => $status,
                'note'             => $note,
                'class_section_id' => $classSectionId,
                'marked_by'        => auth()->id(),
            ]);

            return ['success' => true, 'id' => $existing->id, 'action' => 'updated'];
        }

        $attendance = Attendance::create([
            'school_id'        => $this->schoolId,
            'student_id'       => $studentId,
            'class_section_id' => $classSectionId,
            'marked_by'        => auth()->id(),
            'date'             => $date,
            'status'           => $status,
            'note'             => $note,
        ]);

        return ['success' => true, 'id' => $attendance->id, 'action' => 'created'];
    }

    private function syncMark(array $record): array
    {
        $studentId = $record['student_id'] ?? null;
        $subjectId = $record['subject_id'] ?? null;
        $examId = $record['exam_id'] ?? null;
        $semesterId = $record['semester_id'] ?? null;
        $obtainedMarks = $record['obtained_marks'] ?? 0;
        $totalMarks = $record['total_marks'] ?? 100;
        $grade = $record['grade'] ?? null;

        $student = Student::find($studentId);
        if (!$student) {
            return ['success' => false, 'error' => "Student not found: {$studentId}"];
        }

        $existing = Mark::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('exam_id', $examId)
            ->first();

        if ($existing) {
            $existing->update([
                'obtained_marks' => $obtainedMarks,
                'total_marks'    => $totalMarks,
                'grade'          => $grade,
                'semester_id'    => $semesterId,
            ]);

            return ['success' => true, 'id' => $existing->id, 'action' => 'updated'];
        }

        $mark = Mark::create([
            'school_id'      => $this->schoolId,
            'student_id'     => $studentId,
            'subject_id'     => $subjectId,
            'exam_id'        => $examId,
            'semester_id'    => $semesterId,
            'obtained_marks' => $obtainedMarks,
            'total_marks'    => $totalMarks,
            'grade'          => $grade,
        ]);

        return ['success' => true, 'id' => $mark->id, 'action' => 'created'];
    }
}

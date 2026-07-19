<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Attendance;
use App\Models\Academic\ClassSection;
use App\Models\Academic\QrAttendanceRecord;
use App\Models\Academic\QrAttendanceSession;
use App\Models\Academic\Student;
use App\Models\Academic\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QrAttendanceController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function show(): View
    {
        $classSections = ClassSection::where('school_id', $this->schoolId())
            ->with(['classRoom', 'section'])
            ->orderBy('class_room_id')
            ->get();

        $subjects = Subject::where('school_id', $this->schoolId())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('school-admin.attendance.qr', compact('classSections', 'subjects'));
    }

    public function generate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
            'subject_id'       => 'nullable|exists:subjects,id',
        ]);

        $token = Str::random(32);

        $session = QrAttendanceSession::create([
            'school_id'        => $this->schoolId(),
            'class_section_id' => $data['class_section_id'],
            'subject_id'       => $data['subject_id'] ?? null,
            'teacher_id'       => auth()->id(),
            'session_date'     => now()->toDateString(),
            'qr_code'          => $token,
            'qr_expires_at'    => now()->addMinutes(5),
            'is_active'        => true,
        ]);

        $students = Student::where('class_section_id', $data['class_section_id'])
            ->with('user:id,name')
            ->orderBy('admission_no')
            ->get();

        return response()->json([
            'session_id'  => $session->id,
            'qr_token'    => $token,
            'expires_at'  => $session->qr_expires_at->toISOString(),
            'students'    => $students,
            'scanned'     => [],
        ]);
    }

    public function status(QrAttendanceSession $session): JsonResponse
    {
        $this->authorizeOwn($session);

        $records = QrAttendanceRecord::where('qr_attendance_session_id', $session->id)
            ->with('student.user:id,name')
            ->orderBy('scanned_at')
            ->get();

        $allStudents = Student::where('class_section_id', $session->class_section_id)
            ->with('user:id,name')
            ->orderBy('admission_no')
            ->get();

        $scannedIds = $records->pluck('student_id')->toArray();

        return response()->json([
            'session'       => $session,
            'is_expired'    => $session->qr_expires_at->isPast() || !$session->is_active,
            'records'       => $records,
            'all_students'  => $allStudents,
            'scanned_ids'   => $scannedIds,
            'total'         => $allStudents->count(),
            'scanned_count' => count($scannedIds),
        ]);
    }

    public function manualOverride(Request $request, QrAttendanceSession $session): RedirectResponse
    {
        $this->authorizeOwn($session);

        $data = $request->validate([
            'student_id'  => 'required|exists:students,id',
            'status'      => 'required|in:present,late',
            'late_minutes'=> 'nullable|integer|min:0',
        ]);

        $exists = QrAttendanceRecord::where('qr_attendance_session_id', $session->id)
            ->where('student_id', $data['student_id'])
            ->exists();

        if (!$exists) {
            QrAttendanceRecord::create([
                'school_id'                => $this->schoolId(),
                'qr_attendance_session_id' => $session->id,
                'student_id'               => $data['student_id'],
                'scanned_at'               => now(),
                'ip_address'               => $request->ip(),
                'device_info'              => 'Manual override',
                'status'                   => $data['status'],
                'late_minutes'             => (int) ($data['late_minutes'] ?? 0),
            ]);

            $this->syncToAttendance($session, $data['student_id'], $data['status']);
        }

        return back()->with('success', 'Absensi manual berhasil dicatat.');
    }

    public function deactivate(QrAttendanceSession $session): RedirectResponse
    {
        $this->authorizeOwn($session);
        $session->update(['is_active' => false]);
        return back()->with('success', 'Sesi QR dinonaktifkan.');
    }

    public function history(): View
    {
        $sessions = QrAttendanceSession::where('school_id', $this->schoolId())
            ->with(['classSection.classRoom', 'classSection.section', 'subject', 'teacher:id,name'])
            ->withCount('records')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('school-admin.attendance.qr-history', compact('sessions'));
    }

    public function sessionDetail(QrAttendanceSession $session): View
    {
        $this->authorizeOwn($session);

        $records = QrAttendanceRecord::where('qr_attendance_session_id', $session->id)
            ->with('student.user:id,name')
            ->orderBy('scanned_at')
            ->get();

        $allStudents = Student::where('class_section_id', $session->class_section_id)
            ->with('user:id,name')
            ->orderBy('admission_no')
            ->get();

        $scannedIds = $records->pluck('student_id')->toArray();

        return view('school-admin.attendance.qr-session-detail', compact(
            'session', 'records', 'allStudents', 'scannedIds'
        ));
    }

    private function authorizeOwn(QrAttendanceSession $session): void
    {
        abort_unless($session->school_id === $this->schoolId(), 403);
    }

    private function syncToAttendance(QrAttendanceSession $session, int $studentId, string $status): void
    {
        DB::transaction(function () use ($session, $studentId, $status) {
            $exists = Attendance::where('school_id', $session->school_id)
                ->where('student_id', $studentId)
                ->where('class_section_id', $session->class_section_id)
                ->where('date', $session->session_date)
                ->exists();

            if ($exists) {
                return;
            }

            Attendance::create([
                'school_id'        => $session->school_id,
                'student_id'       => $studentId,
                'class_section_id' => $session->class_section_id,
                'marked_by'        => $session->teacher_id,
                'date'             => $session->session_date,
                'status'           => $status,
                'note'             => 'Manual QR override: ' . ($status === 'late' ? 'terlambat' : 'hadir'),
            ]);
        });
    }
}

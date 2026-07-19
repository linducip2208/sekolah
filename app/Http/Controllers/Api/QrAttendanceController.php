<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Academic\Attendance;
use App\Models\Academic\QrAttendanceRecord;
use App\Models\Academic\QrAttendanceSession;
use App\Models\Academic\Student;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class QrAttendanceController extends Controller
{
    public function scan(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token'       => 'required|string',
            'device_info' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $token = $request->input('token');

        $session = QrAttendanceSession::where('qr_code', $token)
            ->where('is_active', true)
            ->first();

        if (!$session) {
            return response()->json(['message' => 'QR Code tidak valid.'], 404);
        }

        if ($session->qr_expires_at->isPast()) {
            return response()->json(['message' => 'QR Code sudah kadaluarsa.'], 410);
        }

        $userId = auth()->id();
        $student = Student::where('user_id', $userId)->first();

        if (!$student) {
            return response()->json(['message' => 'Profil siswa tidak ditemukan.'], 404);
        }

        if ($student->class_section_id !== $session->class_section_id) {
            return response()->json(['message' => 'Anda bukan anggota kelas ini.'], 403);
        }

        $existing = QrAttendanceRecord::where('qr_attendance_session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message'       => 'Anda sudah tercatat hadir.',
                'already_scanned' => true,
                'scanned_at'    => $existing->scanned_at->toISOString(),
                'status'        => $existing->status,
            ]);
        }

        $sessionStartTime = Carbon::parse($session->session_date->toDateString() . ' ' . $session->created_at->toTimeString());
        $status = 'present';
        $lateMinutes = 0;

        if (now()->diffInMinutes($sessionStartTime) > 15) {
            $status = 'late';
            $lateMinutes = now()->diffInMinutes($sessionStartTime);
        }

        $record = QrAttendanceRecord::create([
            'school_id'                => $session->school_id,
            'qr_attendance_session_id' => $session->id,
            'student_id'               => $student->id,
            'scanned_at'               => now(),
            'ip_address'               => $request->ip(),
            'device_info'              => $request->input('device_info') ?? $request->userAgent(),
            'status'                   => $status,
            'late_minutes'             => $lateMinutes,
        ]);

        $this->syncToAttendance($session, $student->id, $status);

        return response()->json([
            'message'      => 'Absensi berhasil!',
            'status'       => $record->status,
            'scanned_at'   => $record->scanned_at->toISOString(),
            'student_name' => $student->user->name,
        ], 201);
    }

    protected function syncToAttendance(QrAttendanceSession $session, int $studentId, string $status): void
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
                'note'             => 'QR scan: ' . ($status === 'late' ? 'terlambat' : 'hadir'),
            ]);
        });
    }
}

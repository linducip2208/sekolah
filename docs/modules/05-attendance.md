# Module 05 — Attendance Management

## Depends On
Module 04 (academic structure — classes, sections, students must exist)

## What to Build
Absensi harian siswa per kelas. Teacher tandai via app.
Laporan per siswa, kelas, rentang tanggal. Parent/student lihat milik sendiri.
Status: present, absent, late, half-day, on-leave.

---

## Database Schema (MySQL)

```php
// Migration: create_attendances_table
Schema::create('attendances', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
    $table->foreignId('marked_by')->constrained('users');  // teacher
    $table->date('date');
    $table->enum('status', ['present', 'absent', 'late', 'half_day', 'on_leave'])
          ->default('present');
    $table->string('note')->nullable();
    $table->timestamps();
    $table->softDeletes();

    // Satu siswa hanya boleh satu record absensi per hari
    $table->unique(['school_id', 'student_id', 'date']);
    $table->index(['school_id', 'class_section_id', 'date']);
    $table->index(['school_id', 'student_id', 'date']);
});
```

---

## API Endpoints

| Method | URI | Role | Description |
|---|---|---|---|
| GET | `/api/v1/attendance/class/{classSectionId}` | teacher, admin | Absensi kelas untuk tanggal tertentu |
| POST | `/api/v1/attendance/class/{classSectionId}` | teacher, admin | Bulk mark absensi |
| PUT | `/api/v1/attendance/{id}` | teacher, admin | Edit satu record |
| GET | `/api/v1/attendance/student/{studentId}` | teacher, admin, student (own), parent (child) | Riwayat absensi siswa |
| GET | `/api/v1/attendance/report` | admin | Laporan seluruh sekolah |
| GET | `/api/v1/attendance/summary/{studentId}` | all | Ringkasan statistik (%, jumlah) |

---

## Bulk Mark Request & Response

### POST `/api/v1/attendance/class/{classSectionId}`

```json
// Request
{
  "date": "2025-07-14",
  "attendances": [
    { "student_id": 101, "status": "present" },
    { "student_id": 102, "status": "absent", "note": "Sakit" },
    { "student_id": 103, "status": "late" }
  ]
}

// Response 200
{
  "message": "Attendance marked for 3 students",
  "date": "2025-07-14",
  "class_section_id": 5,
  "summary": {
    "present": 1,
    "absent": 1,
    "late": 1,
    "half_day": 0,
    "on_leave": 0
  }
}
```

---

## Files to Create

```
Modules/Academic/
  Http/
    Controllers/AttendanceController.php
    Requests/BulkAttendanceRequest.php
    Requests/UpdateAttendanceRequest.php
    Resources/AttendanceResource.php
    Resources/AttendanceSummaryResource.php
  Models/Attendance.php
  Services/AttendanceService.php
  Repositories/AttendanceRepository.php
  Policies/AttendancePolicy.php
```

---

## Service Implementation

```php
// Modules/Academic/Services/AttendanceService.php
class AttendanceService
{
    public function __construct(
        private AttendanceRepository $repo,
        private NotificationService  $notifications
    ) {}

    public function bulkMark(int $classSectionId, string $date, array $records, User $teacher): array
    {
        // 1. Verify teacher owns this class
        $this->authorizeTeacher($classSectionId, $teacher);

        // 2. Upsert semua record dalam satu query (MySQL upsert)
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
            ['school_id', 'student_id', 'date'],          // unique keys
            ['status', 'note', 'marked_by', 'updated_at'] // columns to update on conflict
        );

        // 3. Notify parents of absent students (queued job)
        $absentIds = collect($records)
            ->where('status', 'absent')
            ->pluck('student_id');

        if ($absentIds->isNotEmpty()) {
            NotifyAbsenceJob::dispatch($absentIds->toArray(), $date, $teacher->school_id);
        }

        return $this->repo->getSummary($classSectionId, $date);
    }

    public function getStudentSummary(int $studentId, string $fromDate, string $toDate): array
    {
        $records = $this->repo->getStudentAttendance($studentId, $fromDate, $toDate);

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
        if ($teacher->hasRole('admin') || $teacher->hasRole('super_admin')) {
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
```

---

## Flutter Widget: Attendance Marker

```dart
// lib/features/attendance/presentation/pages/mark_attendance_page.dart

class MarkAttendancePage extends StatefulWidget {
  final int classSectionId;
  final DateTime date;
}

// UI: ListView siswa, setiap baris:
//   - Foto + nama siswa
//   - SegmentedButton: P | A | L | H | OL
//   - Optional note field (tampil saat A atau OL dipilih)
// Bottom: "Submit Absensi" button
// On submit: POST bulk attendance → tampilkan snackbar dengan summary
```

---

## Notifications (Absence Alert)

```php
// app/Jobs/NotifyAbsenceJob.php
// Dispatch setelah bulk mark jika ada siswa absent
// Kirim FCM push ke device orang tua
// Pesan: "{nama_anak} tidak hadir hari ini ({tanggal})"
// Catat ke tabel notifications untuk in-app inbox
```

---

## Reports

```php
// GET /api/v1/attendance/report
// Query params: class_section_id, from_date, to_date, status (optional filter)
// Response: paginated list + opsi export CSV (streamed)
// Termasuk summary per siswa untuk rentang tanggal
```

---

## Acceptance Criteria

- [ ] Teacher bisa bulk mark absensi untuk kelasnya sendiri saja
- [ ] Teacher tidak bisa mark absensi kelas orang lain (403)
- [ ] Mark ulang tanggal yang sama → update record (upsert, tidak duplikat)
- [ ] Parent dapat FCM notification saat anaknya di-mark absent
- [ ] Student dan parent bisa lihat riwayat absensi dengan filter tanggal
- [ ] Persentase kehadiran dihitung dengan benar
- [ ] Export laporan menghasilkan CSV valid dengan header

## Tests to Write

```
tests/Feature/Attendance/
  BulkMarkTest.php
  CrossClassAccessTest.php
  StudentViewOwnTest.php
  ParentViewChildTest.php
  SummaryCalculationTest.php
  AbsenceNotificationTest.php   (uses Queue::fake())
  UpsertNoDuplicateTest.php     (MySQL upsert test)
```

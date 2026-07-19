# Module 06 — Timetable & Scheduling

## Depends On
Module 04 (academic structure — class_sections, subjects, teachers must exist)
Module 03 (school setup — working_days, school time from settings)

## What to Build
Jadwal pelajaran per rombel per hari. Admin membuat, teacher & student melihat.
Validasi konflik guru (guru tidak bisa mengajar 2 kelas di waktu sama).
Tampilan kalender mingguan di Flutter & web.

---

## Database Schema

```php
// timetables table
Schema::create('timetables', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
    $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
    $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
    $table->tinyInteger('day_of_week');      // 1=Senin, 2=Selasa, ..., 7=Minggu
    $table->time('start_time');
    $table->time('end_time');
    $table->string('room')->nullable();      // ruangan kelas
    $table->timestamps();
    $table->softDeletes();

    // Prevent double booking: teacher/class/slot
    $table->unique(['school_id', 'class_section_id', 'day_of_week', 'start_time'], 'unique_class_slot');
    $table->index(['school_id', 'teacher_id', 'day_of_week']);
    $table->index(['school_id', 'class_section_id', 'day_of_week']);
});

// timetable_breaks table (istirahat, sholat, dll)
Schema::create('timetable_breaks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('name');                  // "Istirahat 1", "Sholat Dzuhur"
    $table->tinyInteger('day_of_week');      // 0 = berlaku semua hari
    $table->time('start_time');
    $table->time('end_time');
    $table->timestamps();
    $table->index(['school_id', 'day_of_week']);
});
```

---

## API Endpoints

| Method | URI                                              | Role           | Deskripsi                              |
|--------|--------------------------------------------------|----------------|----------------------------------------|
| GET    | `/api/v1/timetable/class/{classSectionId}`       | all            | Jadwal mingguan satu rombel            |
| GET    | `/api/v1/timetable/teacher/{teacherId}`          | admin, teacher | Jadwal mingguan satu guru              |
| GET    | `/api/v1/timetable/my`                           | teacher        | Jadwal minggu ini (teacher yg login)   |
| GET    | `/api/v1/timetable/student/my`                   | student        | Jadwal minggu ini (student yg login)   |
| POST   | `/api/v1/timetable`                              | admin          | Tambah satu slot jadwal                |
| POST   | `/api/v1/timetable/bulk`                         | admin          | Import jadwal lengkap satu rombel      |
| PUT    | `/api/v1/timetable/{id}`                         | admin          | Update satu slot                       |
| DELETE | `/api/v1/timetable/{id}`                         | admin          | Hapus slot                             |
| GET    | `/api/v1/timetable/check-conflict`               | admin          | Cek konflik sebelum simpan             |
| GET    | `/api/v1/timetable/breaks`                       | all            | List waktu istirahat                   |
| POST   | `/api/v1/timetable/breaks`                       | admin          | Tambah waktu istirahat                 |

---

## Timetable Response Contract

```json
GET /api/v1/timetable/class/5
{
  "class_section_id": 5,
  "class": "Kelas 10 A",
  "academic_year": "2024/2025",
  "schedule": {
    "1": [
      {
        "id": 101,
        "subject": "Matematika",
        "subject_code": "MTK",
        "teacher": "Budi Santoso",
        "start_time": "07:00",
        "end_time": "08:30",
        "room": "Ruang 10A",
        "day_name": "Senin"
      },
      {
        "type": "break",
        "name": "Istirahat 1",
        "start_time": "09:45",
        "end_time": "10:00"
      }
    ],
    "2": [ ... ],
    "3": [ ... ],
    "4": [ ... ],
    "5": [ ... ]
  },
  "breaks": [
    { "name": "Istirahat 1", "start_time": "09:45", "end_time": "10:00", "day_of_week": 0 },
    { "name": "Sholat Dzuhur", "start_time": "12:00", "end_time": "12:30", "day_of_week": 0 }
  ]
}
```

---

## Files to Create

```
Modules/Academic/
  Http/
    Controllers/TimetableController.php
    Requests/TimetableRequest.php
    Requests/BulkTimetableRequest.php
    Resources/TimetableResource.php
    Resources/WeeklyTimetableResource.php
  Models/
    Timetable.php
    TimetableBreak.php
  Services/
    TimetableService.php
  Repositories/
    TimetableRepository.php
  Policies/
    TimetablePolicy.php
```

---

## TimetableService — Conflict Detection

```php
// Modules/Academic/Services/TimetableService.php
class TimetableService
{
    public function checkConflict(array $data): array
    {
        $conflicts = [];

        // 1. Konflik guru: guru sudah mengajar di slot yang sama
        $teacherConflict = Timetable::where('school_id', auth()->user()->school_id)
            ->where('teacher_id', $data['teacher_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where(function ($q) use ($data) {
                $q->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                  ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
                  ->orWhere(function ($q) use ($data) {
                      $q->where('start_time', '<=', $data['start_time'])
                        ->where('end_time', '>=', $data['end_time']);
                  });
            })
            ->when(isset($data['exclude_id']), fn($q) => $q->where('id', '!=', $data['exclude_id']))
            ->with('clasSection.classRoom', 'clasSection.section', 'subject')
            ->first();

        if ($teacherConflict) {
            $conflicts[] = [
                'type'    => 'teacher_conflict',
                'message' => "Guru sudah mengajar {$teacherConflict->subject->name} di kelas lain pada waktu ini.",
                'slot'    => $teacherConflict,
            ];
        }

        // 2. Konflik rombel: rombel sudah ada jadwal di slot yang sama
        $classConflict = Timetable::where('school_id', auth()->user()->school_id)
            ->where('class_section_id', $data['class_section_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where(function ($q) use ($data) {
                $q->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                  ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']]);
            })
            ->when(isset($data['exclude_id']), fn($q) => $q->where('id', '!=', $data['exclude_id']))
            ->first();

        if ($classConflict) {
            $conflicts[] = [
                'type'    => 'class_conflict',
                'message' => "Rombel sudah memiliki jadwal pada waktu ini.",
                'slot'    => $classConflict,
            ];
        }

        return $conflicts;
    }

    public function getWeeklyForClass(int $classSectionId): array
    {
        $slots = Timetable::where('class_section_id', $classSectionId)
            ->with('subject', 'teacher')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        $breaks = TimetableBreak::all();

        return compact('slots', 'breaks');
    }
}
```

---

## Flutter Widget: Weekly Timetable

```dart
// lib/features/timetable/presentation/pages/timetable_page.dart

class TimetablePage extends StatelessWidget {
  // TabBar: Senin | Selasa | Rabu | Kamis | Jumat
  // Setiap tab: ListView slot pelajaran
  //   - Card per slot:
  //     - Warna sesuai subject.bg_color
  //     - Nama mata pelajaran + kode
  //     - Nama guru (untuk student) atau nama kelas (untuk guru)
  //     - Jam mulai - jam selesai
  //     - Ruangan
  //   - Break card (abu-abu) untuk istirahat
  // Pull-to-refresh
}
```

---

## Acceptance Criteria

- [ ] Tidak bisa buat jadwal jika guru sudah ada jadwal di waktu yang sama (konflik terdeteksi)
- [ ] Tidak bisa buat jadwal jika rombel sudah ada jadwal di waktu yang sama
- [ ] Teacher hanya bisa melihat jadwalnya sendiri
- [ ] Student melihat jadwal rombelnya
- [ ] Response diformat per hari (1=Senin s.d. 7=Minggu)
- [ ] Break tampil di antara slot pelajaran sesuai waktu

## Tests to Write

```
tests/Feature/Timetable/
  CreateTimetableTest.php
  TeacherConflictTest.php
  ClassConflictTest.php
  WeeklyViewTest.php
  StudentViewOwnClassTest.php
  TeacherViewOwnScheduleTest.php
```

# Module 03 — School Setup & Configuration

## Depends On
Module 02 (auth — admin role must exist)

## What to Build
Manajemen profil sekolah, tahun ajaran, semester, hari libur, konfigurasi sistem.
Admin mengelola semua pengaturan dasar sekolah sebelum modul akademik digunakan.

---

## Database Schema

```php
// academic_years table
Schema::create('academic_years', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('name');                     // "2024/2025"
    $table->date('start_date');
    $table->date('end_date');
    $table->boolean('is_active')->default(false);
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['school_id', 'name']);
    $table->index(['school_id', 'is_active']);
});

// semesters table
Schema::create('semesters', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
    $table->string('name');                     // "Semester 1", "Semester 2"
    $table->date('start_date');
    $table->date('end_date');
    $table->boolean('is_active')->default(false);
    $table->timestamps();
    $table->softDeletes();
    $table->index(['school_id', 'academic_year_id']);
});

// school_holidays table
Schema::create('school_holidays', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->date('date');
    $table->date('end_date')->nullable();       // untuk libur multi-hari
    $table->string('type')->default('national');// national, school, regional
    $table->timestamps();
    $table->index(['school_id', 'date']);
});

// school_settings sudah ada di schools.settings (JSON column)
// field tambahan yang dikelola di sini:
// {
//   "working_days": ["Mon","Tue","Wed","Thu","Fri"],
//   "school_start_time": "07:00",
//   "school_end_time": "15:00",
//   "currency": "IDR",
//   "currency_symbol": "Rp",
//   "date_format": "d/m/Y",
//   "time_format": "H:i",
//   "attendance_type": "daily",   // daily | subject-wise
//   "grading_system": "percentage" // percentage | gpa | custom
// }
```

---

## API Endpoints

| Method | URI                                   | Role         | Deskripsi                      |
|--------|---------------------------------------|--------------|--------------------------------|
| GET    | `/api/v1/school/profile`              | all          | Info profil sekolah            |
| PUT    | `/api/v1/school/profile`              | admin        | Update profil sekolah          |
| POST   | `/api/v1/school/logo`                 | admin        | Upload logo sekolah            |
| GET    | `/api/v1/school/settings`             | admin        | Ambil semua pengaturan         |
| PUT    | `/api/v1/school/settings`             | admin        | Update pengaturan sekolah      |
| GET    | `/api/v1/academic-years`              | admin        | List tahun ajaran              |
| POST   | `/api/v1/academic-years`              | admin        | Buat tahun ajaran              |
| PUT    | `/api/v1/academic-years/{id}`         | admin        | Update tahun ajaran            |
| POST   | `/api/v1/academic-years/{id}/activate`| admin        | Aktifkan tahun ajaran          |
| GET    | `/api/v1/semesters`                   | admin        | List semester (by tahun ajaran)|
| POST   | `/api/v1/semesters`                   | admin        | Buat semester                  |
| PUT    | `/api/v1/semesters/{id}`              | admin        | Update semester                |
| POST   | `/api/v1/semesters/{id}/activate`     | admin        | Aktifkan semester              |
| GET    | `/api/v1/holidays`                    | all          | Kalender hari libur            |
| POST   | `/api/v1/holidays`                    | admin        | Tambah hari libur              |
| DELETE | `/api/v1/holidays/{id}`               | admin        | Hapus hari libur               |

---

## Files to Create

```
app/
  Http/
    Controllers/Api/SchoolController.php
    Requests/School/UpdateProfileRequest.php
    Requests/School/UpdateSettingsRequest.php
    Resources/SchoolProfileResource.php

Modules/Academic/
  Http/
    Controllers/AcademicYearController.php
    Controllers/SemesterController.php
    Controllers/HolidayController.php
    Requests/AcademicYearRequest.php
    Requests/SemesterRequest.php
    Resources/AcademicYearResource.php
    Resources/SemesterResource.php
    Resources/HolidayResource.php
  Models/
    AcademicYear.php
    Semester.php
    SchoolHoliday.php
  Services/
    AcademicYearService.php
    SchoolSettingsService.php
  Repositories/
    AcademicYearRepository.php
  Policies/
    AcademicYearPolicy.php
```

---

## SchoolSettingsService Implementation

```php
// app/Services/SchoolSettingsService.php
class SchoolSettingsService
{
    public function get(School $school, string $key = null): mixed
    {
        $settings = $school->settings ?? [];
        return $key ? data_get($settings, $key) : $settings;
    }

    public function update(School $school, array $data): School
    {
        $settings = array_merge($school->settings ?? [], $data);
        $school->update(['settings' => $settings]);
        Cache::tags("school:{$school->id}")->flush();
        return $school->fresh();
    }

    public function getCurrency(School $school): array
    {
        return [
            'code'   => $this->get($school, 'currency') ?? 'IDR',
            'symbol' => $this->get($school, 'currency_symbol') ?? 'Rp',
        ];
    }

    public function getWorkingDays(School $school): array
    {
        return $this->get($school, 'working_days')
            ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
    }
}
```

---

## AcademicYearService Implementation

```php
// Modules/Academic/Services/AcademicYearService.php
class AcademicYearService
{
    public function activate(int $academicYearId): AcademicYear
    {
        return DB::transaction(function () use ($academicYearId) {
            // Nonaktifkan semua tahun ajaran lain
            AcademicYear::where('school_id', auth()->user()->school_id)
                ->update(['is_active' => false]);

            $year = AcademicYear::findOrFail($academicYearId);
            $year->update(['is_active' => true]);

            // Otomatis aktifkan semester pertama jika belum ada
            if (!$year->semesters()->where('is_active', true)->exists()) {
                $year->semesters()->oldest('start_date')->first()
                    ?->update(['is_active' => true]);
            }

            return $year->fresh('semesters');
        });
    }
}
```

---

## Acceptance Criteria

- [ ] Admin dapat update profil dan logo sekolah
- [ ] Hanya satu tahun ajaran aktif dalam satu waktu
- [ ] Aktivasi tahun ajaran otomatis menonaktifkan yang lain
- [ ] Kalender hari libur tampil di semua role (view)
- [ ] Setting school (hari kerja, waktu, currency) tersimpan di schools.settings JSON
- [ ] PHPUnit: `php artisan test --filter=SchoolSetupTest`

## Tests to Write

```
tests/Feature/SchoolSetup/
  SchoolProfileTest.php
  AcademicYearActivationTest.php
  SemesterTest.php
  HolidayCalendarTest.php
  SchoolSettingsTest.php
```

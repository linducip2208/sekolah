# Module 04 — Academic Structure

## Depends On
Module 03 (school setup — academic_years must exist)

## What to Build
Struktur akademik lengkap: kelas, jurusan/program, seksi, mata pelajaran,
penugasan guru ke kelas. Ini adalah backbone untuk semua modul akademik lainnya.

---

## Database Schema

```php
// mediums table (bahasa pengantar: Indonesia, English, dll)
Schema::create('mediums', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('name');                 // "Bahasa Indonesia", "English"
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['school_id', 'name']);
});

// class_rooms table (kelas: 10, 11, 12 atau Kelas 1 - Kelas 6)
Schema::create('class_rooms', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('medium_id')->nullable()->constrained();
    $table->string('name');                 // "Kelas 10", "Grade 10"
    $table->unsignedTinyInteger('order');   // urutan tampil
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['school_id', 'name']);
    $table->index(['school_id', 'order']);
});

// sections table (seksi/rombel: A, B, IPA, IPS)
Schema::create('sections', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('name');                 // "A", "B", "IPA", "IPS"
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['school_id', 'name']);
});

// subjects table (mata pelajaran)
Schema::create('subjects', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('code')->nullable();     // "MTK", "BIO", "ENG"
    $table->string('type')->default('theory'); // theory | practical | elective
    $table->string('bg_color')->nullable(); // warna card di UI
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['school_id', 'name']);
    $table->index(['school_id', 'code']);
});

// class_sections table (kelas + seksi = rombel aktual per tahun ajaran)
Schema::create('class_sections', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
    $table->foreignId('class_room_id')->constrained()->cascadeOnDelete();
    $table->foreignId('section_id')->constrained()->cascadeOnDelete();
    $table->foreignId('class_teacher_id')->nullable()->constrained('users'); // wali kelas
    $table->unsignedSmallInteger('capacity')->default(30);
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['school_id', 'academic_year_id', 'class_room_id', 'section_id']);
    $table->index(['school_id', 'academic_year_id']);
});

// class_section_subjects table (mata pelajaran per rombel + guru pengajar)
Schema::create('class_section_subjects', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
    $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
    $table->timestamps();
    $table->unique(['school_id', 'class_section_id', 'subject_id']);
    $table->index(['school_id', 'teacher_id']);
});

// students table (profil siswa, extend dari users)
Schema::create('students', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('class_section_id')->constrained();
    $table->foreignId('academic_year_id')->constrained();
    $table->string('admission_no')->nullable();
    $table->string('roll_number')->nullable();
    $table->date('admission_date')->nullable();
    $table->date('date_of_birth')->nullable();
    $table->string('gender')->nullable();       // male | female | other
    $table->string('blood_group')->nullable();
    $table->string('religion')->nullable();
    $table->text('address')->nullable();
    $table->string('city')->nullable();
    $table->string('state')->nullable();
    $table->string('zip_code')->nullable();
    $table->string('guardian_name')->nullable();
    $table->string('guardian_phone')->nullable();
    $table->string('guardian_email')->nullable();
    $table->string('guardian_relation')->nullable(); // father|mother|guardian
    $table->boolean('has_transport')->default(false);
    $table->boolean('has_hostel')->default(false);
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['school_id', 'admission_no']);
    $table->index(['school_id', 'class_section_id']);
    $table->index(['school_id', 'academic_year_id']);
});

// parent_student pivot (satu parent bisa punya banyak anak)
Schema::create('parent_student', function (Blueprint $table) {
    $table->id();
    $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->string('relation')->default('parent'); // father|mother|guardian
    $table->timestamps();
    $table->unique(['parent_id', 'student_id']);
});

// staff table (profil guru & staff)
Schema::create('staffs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('staff_id')->nullable();     // ID pegawai
    $table->string('designation')->nullable();  // "Guru Matematika", "Staff TU"
    $table->string('department')->nullable();
    $table->string('qualification')->nullable();
    $table->string('experience')->nullable();
    $table->date('joining_date')->nullable();
    $table->date('date_of_birth')->nullable();
    $table->string('gender')->nullable();
    $table->string('blood_group')->nullable();
    $table->text('address')->nullable();
    $table->string('emergency_contact')->nullable();
    $table->unsignedInteger('basic_salary')->default(0); // integer cents
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['school_id', 'staff_id']);
    $table->index(['school_id', 'user_id']);
});
```

---

## API Endpoints

| Method | URI                                                | Role              | Deskripsi                        |
|--------|----------------------------------------------------|-------------------|----------------------------------|
| GET    | `/api/v1/classes`                                  | admin, teacher    | List semua kelas                 |
| POST   | `/api/v1/classes`                                  | admin             | Buat kelas                       |
| PUT    | `/api/v1/classes/{id}`                             | admin             | Update kelas                     |
| DELETE | `/api/v1/classes/{id}`                             | admin             | Soft delete kelas                |
| GET    | `/api/v1/sections`                                 | admin             | List seksi                       |
| POST   | `/api/v1/sections`                                 | admin             | Buat seksi                       |
| GET    | `/api/v1/subjects`                                 | admin, teacher    | List mata pelajaran              |
| POST   | `/api/v1/subjects`                                 | admin             | Buat mata pelajaran              |
| PUT    | `/api/v1/subjects/{id}`                            | admin             | Update mata pelajaran            |
| GET    | `/api/v1/class-sections`                           | admin             | List rombel per tahun ajaran     |
| POST   | `/api/v1/class-sections`                           | admin             | Buat rombel                      |
| PUT    | `/api/v1/class-sections/{id}`                      | admin             | Update rombel                    |
| POST   | `/api/v1/class-sections/{id}/assign-teacher`       | admin             | Assign guru ke mata pelajaran    |
| GET    | `/api/v1/class-sections/{id}/students`             | admin, teacher    | List siswa dalam rombel          |
| GET    | `/api/v1/students`                                 | admin             | List semua siswa (paginated)     |
| POST   | `/api/v1/students`                                 | admin             | Tambah siswa                     |
| GET    | `/api/v1/students/{id}`                            | admin,teacher,own | Profil siswa                     |
| PUT    | `/api/v1/students/{id}`                            | admin             | Update data siswa                |
| GET    | `/api/v1/students/{id}/promote`                    | admin             | Preview naik kelas               |
| POST   | `/api/v1/students/promote`                         | admin             | Bulk naik kelas                  |
| GET    | `/api/v1/staff`                                    | admin             | List semua staff                 |
| POST   | `/api/v1/staff`                                    | admin             | Tambah staff                     |
| GET    | `/api/v1/staff/{id}`                               | admin, own        | Profil staff                     |
| PUT    | `/api/v1/staff/{id}`                               | admin             | Update data staff                |
| GET    | `/api/v1/teacher/my-classes`                       | teacher           | Rombel yang diajar guru ini      |
| GET    | `/api/v1/mediums`                                  | admin             | List bahasa pengantar            |
| POST   | `/api/v1/mediums`                                  | admin             | Buat bahasa pengantar            |

---

## Files to Create

```
Modules/Academic/
  Http/
    Controllers/
      ClassRoomController.php
      SectionController.php
      SubjectController.php
      ClassSectionController.php
      StudentController.php
      StaffController.php
      MediumController.php
    Requests/
      ClassRoomRequest.php
      SubjectRequest.php
      ClassSectionRequest.php
      CreateStudentRequest.php
      UpdateStudentRequest.php
      StaffRequest.php
      AssignTeacherRequest.php
      PromoteStudentsRequest.php
    Resources/
      ClassRoomResource.php
      SectionResource.php
      SubjectResource.php
      ClassSectionResource.php
      StudentResource.php
      StudentListResource.php
      StaffResource.php
  Models/
    ClassRoom.php
    Section.php
    Subject.php
    ClassSection.php
    ClassSectionSubject.php
    Student.php
    Staff.php
    Medium.php
  Services/
    StudentService.php
    StaffService.php
    ClassSectionService.php
    StudentPromotionService.php
  Repositories/
    StudentRepository.php
    StaffRepository.php
    ClassSectionRepository.php
  Policies/
    StudentPolicy.php
    StaffPolicy.php
    ClassRoomPolicy.php
```

---

## Student Promotion Service

```php
// Modules/Academic/Services/StudentPromotionService.php
class StudentPromotionService
{
    // Naik kelas bulk: pindah siswa dari satu rombel ke rombel baru
    public function promote(int $fromClassSectionId, int $toClassSectionId, array $studentIds): array
    {
        return DB::transaction(function () use ($fromClassSectionId, $toClassSectionId, $studentIds) {
            $activeYear = AcademicYear::where('school_id', auth()->user()->school_id)
                ->where('is_active', true)
                ->firstOrFail();

            $updated = 0;
            foreach ($studentIds as $studentId) {
                Student::where('id', $studentId)
                    ->where('class_section_id', $fromClassSectionId)
                    ->update([
                        'class_section_id' => $toClassSectionId,
                        'academic_year_id' => $activeYear->id,
                    ]);
                $updated++;
            }

            return [
                'promoted' => $updated,
                'from_class_section_id' => $fromClassSectionId,
                'to_class_section_id'   => $toClassSectionId,
            ];
        });
    }
}
```

---

## Teacher Subject Assignment Response

```json
GET /api/v1/teacher/my-classes
{
  "data": [
    {
      "class_section_id": 5,
      "class": "Kelas 10",
      "section": "A",
      "subject": "Matematika",
      "subject_code": "MTK",
      "student_count": 32,
      "class_teacher": false
    },
    {
      "class_section_id": 5,
      "class": "Kelas 10",
      "section": "A",
      "subject": "Fisika",
      "subject_code": "FIS",
      "student_count": 32,
      "class_teacher": true
    }
  ]
}
```

---

## Acceptance Criteria

- [ ] Kelas, seksi, mata pelajaran bisa dibuat dan diedit oleh admin
- [ ] Satu rombel hanya ada satu kali per tahun ajaran (unique constraint)
- [ ] Guru bisa diassign ke beberapa mata pelajaran dan kelas
- [ ] Endpoint `/teacher/my-classes` hanya mengembalikan kelas milik guru yang login
- [ ] Naik kelas bulk berhasil memindahkan `class_section_id` dan `academic_year_id`
- [ ] Siswa tidak terlihat lintas sekolah (SchoolScope diterapkan)

## Tests to Write

```
tests/Feature/AcademicStructure/
  ClassRoomTest.php
  SubjectTest.php
  ClassSectionTest.php
  StudentCrudTest.php
  StudentPromotionTest.php
  TeacherMyClassesTest.php
  CrossSchoolIsolationTest.php
```

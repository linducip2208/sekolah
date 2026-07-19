# Module 07 — Online Classroom

## Depends On
Module 04 (academic structure — class_section_subjects, teacher assignments)
Module 03 (school setup — academic year)

## What to Build
Ruang kelas digital: materi pelajaran (lesson topics), tugas (assignments),
pengumpulan tugas (submission), dan materi pendukung (study materials).
Teacher upload, student download & submit, teacher nilai submission.

---

## Database Schema

```php
// lessons table (bab/topik pelajaran)
Schema::create('lessons', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
    $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
    $table->string('name');             // "Bab 1: Himpunan"
    $table->text('description')->nullable();
    $table->date('date')->nullable();
    $table->timestamps();
    $table->softDeletes();
    $table->index(['school_id', 'class_section_id', 'subject_id']);
});

// lesson_topics table (sub-topik + materi)
Schema::create('lesson_topics', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->longText('description')->nullable();
    $table->string('file')->nullable();         // S3 path (PDF, PPT, dll)
    $table->string('file_thumbnail')->nullable();
    $table->string('video_url')->nullable();    // YouTube/Vimeo link
    $table->unsignedTinyInteger('order')->default(0);
    $table->timestamps();
    $table->softDeletes();
});

// study_materials table (dokumen mandiri di luar lesson)
Schema::create('study_materials', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
    $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
    $table->string('title');
    $table->text('description')->nullable();
    $table->string('file');                     // S3 path
    $table->string('file_type');                // pdf | doc | ppt | video | link
    $table->unsignedInteger('file_size')->default(0); // bytes
    $table->timestamps();
    $table->softDeletes();
    $table->index(['school_id', 'class_section_id', 'subject_id']);
});

// assignments table
Schema::create('assignments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
    $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
    $table->string('title');
    $table->longText('instructions')->nullable();
    $table->string('file')->nullable();         // soal/attachment
    $table->datetime('due_date');
    $table->unsignedSmallInteger('total_marks')->default(100);
    $table->boolean('extra_marks_allowed')->default(false);
    $table->timestamps();
    $table->softDeletes();
    $table->index(['school_id', 'class_section_id', 'due_date']);
});

// assignment_submissions table
Schema::create('assignment_submissions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->string('file')->nullable();             // file jawaban
    $table->text('notes')->nullable();              // catatan dari student
    $table->datetime('submitted_at')->nullable();
    $table->boolean('is_late')->default(false);
    $table->enum('status', ['pending', 'submitted', 'graded', 'returned'])->default('pending');
    $table->unsignedSmallInteger('marks')->nullable();
    $table->text('teacher_feedback')->nullable();
    $table->foreignId('graded_by')->nullable()->constrained('users');
    $table->datetime('graded_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['school_id', 'assignment_id', 'student_id']);
    $table->index(['school_id', 'assignment_id', 'status']);
});
```

---

## API Endpoints

| Method | URI                                                    | Role                  | Deskripsi                          |
|--------|--------------------------------------------------------|-----------------------|------------------------------------|
| GET    | `/api/v1/classroom/lessons`                            | teacher, student      | List lesson (filter by class+subj) |
| POST   | `/api/v1/classroom/lessons`                            | teacher               | Buat lesson baru                   |
| PUT    | `/api/v1/classroom/lessons/{id}`                       | teacher               | Update lesson                      |
| DELETE | `/api/v1/classroom/lessons/{id}`                       | teacher, admin        | Hapus lesson                       |
| GET    | `/api/v1/classroom/lessons/{id}/topics`                | teacher, student      | List topics dalam lesson           |
| POST   | `/api/v1/classroom/lessons/{id}/topics`                | teacher               | Tambah topic (+ upload file)       |
| PUT    | `/api/v1/classroom/topics/{id}`                        | teacher               | Update topic                       |
| DELETE | `/api/v1/classroom/topics/{id}`                        | teacher               | Hapus topic                        |
| GET    | `/api/v1/classroom/materials`                          | teacher, student      | List study materials               |
| POST   | `/api/v1/classroom/materials`                          | teacher               | Upload study material              |
| DELETE | `/api/v1/classroom/materials/{id}`                     | teacher, admin        | Hapus material                     |
| GET    | `/api/v1/classroom/assignments`                        | teacher, student      | List tugas (aktif/deadline)        |
| POST   | `/api/v1/classroom/assignments`                        | teacher               | Buat tugas baru                    |
| PUT    | `/api/v1/classroom/assignments/{id}`                   | teacher               | Update tugas                       |
| DELETE | `/api/v1/classroom/assignments/{id}`                   | teacher, admin        | Hapus tugas                        |
| GET    | `/api/v1/classroom/assignments/{id}`                   | teacher, student      | Detail tugas + status submission   |
| POST   | `/api/v1/classroom/assignments/{id}/submit`            | student               | Kumpul tugas                       |
| GET    | `/api/v1/classroom/assignments/{id}/submissions`       | teacher               | List semua submission tugas ini    |
| POST   | `/api/v1/classroom/submissions/{id}/grade`             | teacher               | Beri nilai + feedback              |

---

## Files to Create

```
Modules/Academic/
  Http/
    Controllers/
      LessonController.php
      LessonTopicController.php
      StudyMaterialController.php
      AssignmentController.php
      AssignmentSubmissionController.php
    Requests/
      LessonRequest.php
      LessonTopicRequest.php
      StudyMaterialRequest.php
      AssignmentRequest.php
      SubmitAssignmentRequest.php
      GradeSubmissionRequest.php
    Resources/
      LessonResource.php
      LessonTopicResource.php
      StudyMaterialResource.php
      AssignmentResource.php
      SubmissionResource.php
  Models/
    Lesson.php
    LessonTopic.php
    StudyMaterial.php
    Assignment.php
    AssignmentSubmission.php
  Services/
    ClassroomService.php
    AssignmentService.php
  Repositories/
    AssignmentRepository.php
  Policies/
    AssignmentPolicy.php
    LessonPolicy.php
```

---

## AssignmentService Implementation

```php
// Modules/Academic/Services/AssignmentService.php
class AssignmentService
{
    public function submit(int $assignmentId, int $studentId, array $data): AssignmentSubmission
    {
        $assignment = Assignment::findOrFail($assignmentId);
        $isLate     = now()->isAfter($assignment->due_date);

        $submission = AssignmentSubmission::updateOrCreate(
            [
                'school_id'     => auth()->user()->school_id,
                'assignment_id' => $assignmentId,
                'student_id'    => $studentId,
            ],
            [
                'file'         => $data['file'] ?? null,
                'notes'        => $data['notes'] ?? null,
                'submitted_at' => now(),
                'is_late'      => $isLate,
                'status'       => 'submitted',
            ]
        );

        // Notify teacher
        NotifySubmissionJob::dispatch($submission);

        return $submission->load('student.user', 'assignment');
    }

    public function grade(int $submissionId, int $marks, string $feedback, int $teacherId): AssignmentSubmission
    {
        $submission = AssignmentSubmission::findOrFail($submissionId);

        if ($marks > $submission->assignment->total_marks) {
            if (!$submission->assignment->extra_marks_allowed) {
                throw new \InvalidArgumentException('Nilai melebihi total marks yang diizinkan.');
            }
        }

        $submission->update([
            'marks'            => $marks,
            'teacher_feedback' => $feedback,
            'graded_by'        => $teacherId,
            'graded_at'        => now(),
            'status'           => 'graded',
        ]);

        // Notify student
        NotifyGradedJob::dispatch($submission);

        return $submission->fresh();
    }
}
```

---

## Assignment List Response (Student View)

```json
GET /api/v1/classroom/assignments
{
  "data": [
    {
      "id": 12,
      "title": "Latihan Himpunan Bab 1",
      "subject": "Matematika",
      "class": "Kelas 10 A",
      "due_date": "2025-07-20T23:59:00",
      "is_due_soon": true,
      "total_marks": 100,
      "my_submission": {
        "status": "submitted",
        "submitted_at": "2025-07-18T14:30:00",
        "is_late": false,
        "marks": null,
        "feedback": null
      }
    }
  ]
}
```

---

## Acceptance Criteria

- [ ] Teacher hanya bisa buat lesson/tugas untuk kelasnya sendiri
- [ ] Student hanya bisa submit tugas kelasnya sendiri
- [ ] Submission terlambat ditandai `is_late: true`
- [ ] Nilai tidak bisa melebihi `total_marks` kecuali `extra_marks_allowed = true`
- [ ] Teacher mendapat notifikasi FCM saat siswa submit
- [ ] Student mendapat notifikasi FCM saat tugas dinilai
- [ ] File upload di-store ke S3 `classroom/topics/` dan `classroom/submissions/`

## Tests to Write

```
tests/Feature/Classroom/
  LessonCrudTest.php
  StudyMaterialUploadTest.php
  AssignmentCrudTest.php
  StudentSubmitTest.php
  LateSubmissionTest.php
  TeacherGradeTest.php
  CrossClassAccessTest.php
```

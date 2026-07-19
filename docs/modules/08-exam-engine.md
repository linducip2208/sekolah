# Module 08 — Exam Engine

## Depends On
Module 04 (academic structure), Module 03 (school setup — semester)

## What to Build
Ujian online dan offline. Online: soal multiple choice + essay, siswa kerjakan di app.
Offline: teacher input nilai hasil ujian kertas. Scheduling, durasi, auto-submit.

---

## Database Schema

```php
// exams table
Schema::create('exams', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
    $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->text('instructions')->nullable();
    $table->enum('type', ['online', 'offline'])->default('online');
    $table->datetime('start_datetime');
    $table->datetime('end_datetime');
    $table->unsignedSmallInteger('duration_minutes');
    $table->unsignedSmallInteger('total_marks')->default(100);
    $table->unsignedSmallInteger('passing_marks')->default(50);
    $table->boolean('shuffle_questions')->default(false);
    $table->boolean('shuffle_options')->default(false);
    $table->boolean('show_result_immediately')->default(false);
    $table->enum('status', ['draft', 'published', 'active', 'completed'])->default('draft');
    $table->timestamps();
    $table->softDeletes();
    $table->index(['school_id', 'class_section_id', 'start_datetime']);
    $table->index(['school_id', 'semester_id', 'subject_id']);
});

// exam_questions table
Schema::create('exam_questions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
    $table->text('question');
    $table->enum('type', ['mcq', 'true_false', 'essay'])->default('mcq');
    $table->unsignedSmallInteger('marks')->default(1);
    $table->text('explanation')->nullable();    // penjelasan jawaban benar
    $table->unsignedTinyInteger('order')->default(0);
    $table->string('image')->nullable();        // S3 path gambar soal
    $table->timestamps();
    $table->softDeletes();
});

// exam_question_options table (pilihan jawaban MCQ)
Schema::create('exam_question_options', function (Blueprint $table) {
    $table->id();
    $table->foreignId('exam_question_id')->constrained()->cascadeOnDelete();
    $table->string('option_text');
    $table->string('option_image')->nullable();
    $table->boolean('is_correct')->default(false);
    $table->unsignedTinyInteger('order')->default(0);
    $table->timestamps();
});

// exam_submissions table (sesi ujian per siswa)
Schema::create('exam_submissions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->datetime('started_at')->nullable();
    $table->datetime('submitted_at')->nullable();
    $table->unsignedSmallInteger('obtained_marks')->nullable();
    $table->enum('status', ['not_started', 'in_progress', 'submitted', 'graded'])->default('not_started');
    $table->boolean('is_passed')->nullable();
    $table->timestamps();
    $table->unique(['school_id', 'exam_id', 'student_id']);
    $table->index(['school_id', 'exam_id', 'status']);
});

// exam_answers table (jawaban per soal per siswa)
Schema::create('exam_answers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('exam_submission_id')->constrained()->cascadeOnDelete();
    $table->foreignId('exam_question_id')->constrained()->cascadeOnDelete();
    $table->foreignId('selected_option_id')->nullable()->constrained('exam_question_options');
    $table->text('essay_answer')->nullable();   // untuk tipe essay
    $table->unsignedSmallInteger('marks_awarded')->nullable();
    $table->text('teacher_comment')->nullable();
    $table->timestamps();
    $table->unique(['exam_submission_id', 'exam_question_id']);
});
```

---

## API Endpoints

| Method | URI                                              | Role              | Deskripsi                           |
|--------|--------------------------------------------------|-------------------|-------------------------------------|
| GET    | `/api/v1/exams`                                  | teacher, admin    | List semua ujian                    |
| POST   | `/api/v1/exams`                                  | teacher, admin    | Buat ujian baru                     |
| PUT    | `/api/v1/exams/{id}`                             | teacher, admin    | Update ujian (hanya jika draft)     |
| DELETE | `/api/v1/exams/{id}`                             | teacher, admin    | Hapus ujian                         |
| POST   | `/api/v1/exams/{id}/publish`                     | teacher, admin    | Publish ujian (buka ke siswa)       |
| GET    | `/api/v1/exams/{id}/questions`                   | teacher           | List soal ujian (admin view)        |
| POST   | `/api/v1/exams/{id}/questions`                   | teacher           | Tambah soal                         |
| PUT    | `/api/v1/exams/questions/{id}`                   | teacher           | Edit soal                           |
| DELETE | `/api/v1/exams/questions/{id}`                   | teacher           | Hapus soal                          |
| GET    | `/api/v1/exams/upcoming`                         | student           | Ujian yang akan datang              |
| GET    | `/api/v1/exams/{id}/start`                       | student           | Mulai ujian (buat session)          |
| POST   | `/api/v1/exams/{id}/answer`                      | student           | Simpan jawaban (auto-save)          |
| POST   | `/api/v1/exams/{id}/submit`                      | student           | Submit ujian                        |
| GET    | `/api/v1/exams/{id}/result`                      | student, parent   | Hasil ujian (jika sudah released)   |
| GET    | `/api/v1/exams/{id}/submissions`                 | teacher, admin    | Semua submission ujian ini          |
| POST   | `/api/v1/exams/submissions/{id}/grade-essay`     | teacher           | Nilai jawaban essay                 |

---

## ExamService Implementation

```php
// Modules/Academic/Services/ExamService.php
class ExamService
{
    public function startExam(int $examId, int $studentId): ExamSubmission
    {
        $exam = Exam::where('status', 'published')->findOrFail($examId);

        if (now()->isBefore($exam->start_datetime)) {
            throw new ExamNotStartedException('Ujian belum dimulai.');
        }
        if (now()->isAfter($exam->end_datetime)) {
            throw new ExamExpiredException('Waktu ujian sudah habis.');
        }

        $submission = ExamSubmission::firstOrCreate(
            ['school_id' => auth()->user()->school_id, 'exam_id' => $examId, 'student_id' => $studentId],
            ['status' => 'in_progress', 'started_at' => now()]
        );

        if ($submission->status === 'submitted') {
            throw new AlreadySubmittedException('Ujian sudah dikerjakan.');
        }

        $questions = $exam->questions()->with('options')->get();

        if ($exam->shuffle_questions) {
            $questions = $questions->shuffle();
        }

        if ($exam->shuffle_options) {
            $questions->each(fn($q) => $q->setRelation('options', $q->options->shuffle()));
        }

        return $submission->setRelation('exam', $exam->setRelation('questions', $questions));
    }

    public function autoGrade(ExamSubmission $submission): void
    {
        $totalMarks = 0;

        foreach ($submission->answers as $answer) {
            $question = $answer->question;

            if (in_array($question->type, ['mcq', 'true_false']) && $answer->selected_option_id) {
                $correct = $question->options->where('is_correct', true)->first();
                if ($correct && $answer->selected_option_id === $correct->id) {
                    $answer->update(['marks_awarded' => $question->marks]);
                    $totalMarks += $question->marks;
                } else {
                    $answer->update(['marks_awarded' => 0]);
                }
            }
            // Essay: marks awarded manual by teacher
        }

        $submission->update([
            'obtained_marks' => $totalMarks,
            'status'         => $submission->exam->questions->where('type', 'essay')->isEmpty()
                                ? 'graded'
                                : 'submitted',
            'is_passed'      => $totalMarks >= $submission->exam->passing_marks,
        ]);
    }
}
```

---

## Acceptance Criteria

- [ ] Siswa tidak bisa mulai ujian sebelum `start_datetime` atau setelah `end_datetime`
- [ ] Auto-grade MCQ/true_false setelah submit
- [ ] Essay perlu dinilai manual oleh guru
- [ ] Soal bisa di-shuffle jika `shuffle_questions = true`
- [ ] Hasil hanya tampil jika `show_result_immediately = true` atau guru sudah release
- [ ] Timer di Flutter countdown sesuai `duration_minutes`
- [ ] Auto-submit jika waktu habis (frontend trigger)

## Tests to Write

```
tests/Feature/Exam/
  CreateExamTest.php
  StartExamTest.php
  SubmitExamTest.php
  AutoGradeTest.php
  TimingValidationTest.php
  EssayGradeTest.php
  ShuffleQuestionsTest.php
```

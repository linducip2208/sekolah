# Module 09 — Marks, Grades & Report Cards

## Depends On
Module 08 (exam engine), Module 04 (academic structure), Module 03 (school setup — semester)

## What to Build
Sistem penilaian: input nilai per ujian/semester, konfigurasi sistem grade (A/B/C atau GPA),
report card otomatis, ranking, dan analitik performa siswa.

---

## Database Schema

```php
// grade_systems table
Schema::create('grade_systems', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('name');                    // "Sistem Nilai SMP", "GPA System"
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
});

// grade_rules table (mapping range nilai → grade)
Schema::create('grade_rules', function (Blueprint $table) {
    $table->id();
    $table->foreignId('grade_system_id')->constrained()->cascadeOnDelete();
    $table->string('grade');                   // "A+", "A", "B", "C", "D", "F"
    $table->unsignedTinyInteger('min_percentage');
    $table->unsignedTinyInteger('max_percentage');
    $table->string('description')->nullable(); // "Sangat Baik"
    $table->decimal('gpa_value', 3, 2)->nullable(); // 4.00, 3.00, dll
    $table->timestamps();
});

// marks table (nilai per ujian per siswa)
Schema::create('marks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
    $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
    $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
    $table->unsignedSmallInteger('obtained_marks');
    $table->unsignedSmallInteger('total_marks');
    $table->decimal('percentage', 5, 2)->virtualAs('(obtained_marks / total_marks) * 100');
    $table->string('grade')->nullable();       // A, B, C
    $table->text('teacher_remarks')->nullable();
    $table->timestamps();
    $table->unique(['school_id', 'student_id', 'exam_id', 'subject_id']);
    $table->index(['school_id', 'class_section_id', 'semester_id']);
    $table->index(['school_id', 'student_id', 'semester_id']);
});

// report_cards table (rapor per semester)
Schema::create('report_cards', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
    $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
    $table->json('subject_results');           // [{subject, marks, grade, gpa}, ...]
    $table->decimal('total_percentage', 5, 2)->nullable();
    $table->decimal('gpa', 3, 2)->nullable();
    $table->string('overall_grade')->nullable();
    $table->unsignedSmallInteger('rank')->nullable();         // rank dalam kelas
    $table->unsignedSmallInteger('class_size')->nullable();
    $table->boolean('is_passed')->default(false);
    $table->text('class_teacher_remarks')->nullable();
    $table->text('principal_remarks')->nullable();
    $table->boolean('is_published')->default(false);
    $table->timestamp('published_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['school_id', 'student_id', 'semester_id']);
    $table->index(['school_id', 'class_section_id', 'semester_id', 'is_published']);
});
```

---

## API Endpoints

| Method | URI                                                       | Role              | Deskripsi                            |
|--------|-----------------------------------------------------------|-------------------|--------------------------------------|
| GET    | `/api/v1/marks/class/{classSectionId}`                    | teacher, admin    | Semua nilai per rombel (per mapel)   |
| POST   | `/api/v1/marks/bulk`                                      | teacher, admin    | Input nilai bulk (offline exam)      |
| PUT    | `/api/v1/marks/{id}`                                      | teacher, admin    | Edit satu nilai                      |
| GET    | `/api/v1/marks/student/{studentId}`                       | all (own/child)   | Nilai semua mapel satu siswa         |
| GET    | `/api/v1/marks/student/{studentId}/summary`               | all (own/child)   | Ringkasan performa siswa             |
| GET    | `/api/v1/grade-systems`                                   | admin             | List sistem grade                    |
| POST   | `/api/v1/grade-systems`                                   | admin             | Buat sistem grade baru               |
| GET    | `/api/v1/report-cards/class/{classSectionId}`             | admin, teacher    | Generate rapor semua siswa di rombel |
| POST   | `/api/v1/report-cards/generate`                           | admin             | Generate + publish rapor             |
| GET    | `/api/v1/report-cards/student/{studentId}`                | all (own/child)   | Rapor siswa per semester             |
| GET    | `/api/v1/report-cards/{id}/download`                      | all (own/child)   | Download rapor PDF                   |
| GET    | `/api/v1/analytics/class/{classSectionId}/performance`    | teacher, admin    | Analitik performa kelas              |
| GET    | `/api/v1/analytics/school/performance`                    | admin             | Performa seluruh sekolah             |

---

## Files to Create

```
Modules/Academic/
  Http/
    Controllers/
      MarkController.php
      GradeSystemController.php
      ReportCardController.php
      AnalyticsController.php
    Requests/
      BulkMarkRequest.php
      GradeSystemRequest.php
      GenerateReportCardRequest.php
    Resources/
      MarkResource.php
      StudentMarksResource.php
      ReportCardResource.php
      PerformanceAnalyticsResource.php
  Models/
    GradeSystem.php
    GradeRule.php
    Mark.php
    ReportCard.php
  Services/
    MarkService.php
    ReportCardService.php
    GradingService.php
    AnalyticsService.php
  Repositories/
    MarkRepository.php
    ReportCardRepository.php
  Policies/
    MarkPolicy.php
    ReportCardPolicy.php
  Jobs/
    GenerateReportCardPdfJob.php
```

---

## GradingService Implementation

```php
// Modules/Academic/Services/GradingService.php
class GradingService
{
    public function getGrade(int $schoolId, float $percentage): ?string
    {
        $system = GradeSystem::where('school_id', $schoolId)
            ->where('is_active', true)
            ->with('rules')
            ->first();

        if (!$system) return null;

        $rule = $system->rules
            ->where('min_percentage', '<=', $percentage)
            ->where('max_percentage', '>=', $percentage)
            ->first();

        return $rule?->grade;
    }
}
```

---

## ReportCardService Implementation

```php
// Modules/Academic/Services/ReportCardService.php
class ReportCardService
{
    public function generate(int $classSectionId, int $semesterId): Collection
    {
        $students = Student::where('class_section_id', $classSectionId)->get();
        $results  = collect();

        foreach ($students as $student) {
            $marks = Mark::where('student_id', $student->id)
                ->where('semester_id', $semesterId)
                ->with('subject')
                ->get();

            $subjectResults = $marks->map(fn($m) => [
                'subject'         => $m->subject->name,
                'obtained_marks'  => $m->obtained_marks,
                'total_marks'     => $m->total_marks,
                'percentage'      => $m->percentage,
                'grade'           => $m->grade,
            ]);

            $avgPct = $marks->avg('percentage');

            ReportCard::updateOrCreate(
                ['school_id' => $student->school_id, 'student_id' => $student->id, 'semester_id' => $semesterId],
                [
                    'class_section_id' => $classSectionId,
                    'subject_results'  => $subjectResults->toArray(),
                    'total_percentage' => round($avgPct, 2),
                    'overall_grade'    => $this->grading->getGrade($student->school_id, $avgPct),
                    'is_passed'        => $avgPct >= 60,
                ]
            );
        }

        // Calculate ranking setelah semua siswa diproses
        $this->calculateRanking($classSectionId, $semesterId);

        return ReportCard::where('class_section_id', $classSectionId)
            ->where('semester_id', $semesterId)
            ->with('student.user')
            ->get();
    }

    private function calculateRanking(int $classSectionId, int $semesterId): void
    {
        $cards = ReportCard::where('class_section_id', $classSectionId)
            ->where('semester_id', $semesterId)
            ->orderByDesc('total_percentage')
            ->get();

        $classSize = $cards->count();
        $cards->each(function ($card, $index) use ($classSize) {
            $card->update(['rank' => $index + 1, 'class_size' => $classSize]);
        });
    }
}
```

---

## Acceptance Criteria

- [ ] Nilai bisa di-input manual (offline exam) atau otomatis dari exam engine
- [ ] Grade dihitung berdasarkan grade_system aktif sekolah
- [ ] Report card berisi ranking dalam kelas
- [ ] Report card PDF dapat di-download oleh siswa dan parent
- [ ] Analitik kelas menampilkan rata-rata nilai per mata pelajaran
- [ ] Rapor hanya bisa dilihat setelah di-publish oleh admin

## Tests to Write

```
tests/Feature/Marks/
  BulkMarkInputTest.php
  GradeCalculationTest.php
  ReportCardGenerationTest.php
  RankingCalculationTest.php
  ReportCardPublishTest.php
  StudentViewOwnMarksTest.php
  ParentViewChildMarksTest.php
```

# Module 40 — Curriculum Mapping

## Depends On
Module 04 (Academic Structure), Module 26 (Lesson Plan)

## What to Build
Mapping CP/TP (Capaian Pembelajaran / Tujuan Pembelajaran) Kurikulum Merdeka atau KI-KD K13. Vertical alignment antar tingkat. Audit cakupan kurikulum.

## Database Schema

```php
Schema::create('curriculum_frameworks', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->string('name');                          // "Kurikulum Merdeka 2024"
    $t->enum('type', ['merdeka','k13','cambridge','ib','custom']);
    $t->json('config')->nullable();
    $t->boolean('is_active')->default(true);
    $t->timestamps();
});

Schema::create('curriculum_competencies', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('curriculum_framework_id')->constrained();
    $t->foreignId('subject_id')->constrained();
    $t->foreignId('class_room_id')->constrained();
    $t->string('code', 30);                      // "CP.1", "KD.3.1"
    $t->text('description');
    $t->enum('level_type', ['cp','tp','ki','kd','outcome']);
    $t->foreignId('parent_id')->nullable()->constrained('curriculum_competencies');
    $t->json('indicators')->nullable();
    $t->timestamps();
});

Schema::create('competency_lesson_map', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('curriculum_competency_id')->constrained()->cascadeOnDelete();
    $t->foreignId('lesson_plan_id')->constrained()->cascadeOnDelete();
    $t->timestamps();
    $t->unique(['curriculum_competency_id', 'lesson_plan_id']);
});

Schema::create('competency_assessments', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->constrained();
    $t->foreignId('curriculum_competency_id')->constrained();
    $t->enum('mastery_level', ['emerging','developing','meets','exceeds']);
    $t->foreignId('assessed_by')->constrained('users');
    $t->date('assessed_at');
    $t->text('evidence')->nullable();
    $t->timestamps();
});
```

## Acceptance Criteria
- [ ] Import CP/TP standar Kemdikbud (CSV)
- [ ] Map RPP ke CP/TP
- [ ] Coverage report: berapa % CP sudah dibahas semester ini
- [ ] Mastery report per siswa per CP

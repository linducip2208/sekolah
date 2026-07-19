# Module 26 — Lesson Plan / RPP (Rencana Pelaksanaan Pembelajaran)

## Depends On
Module 04 (Academic Structure), Module 06 (Timetable), Module 07 (Online Classroom)

## What to Build
Guru bikin RPP per pertemuan, kepsek/wakasek approve, tracking realisasi vs rencana, audit kurikulum, export PDF format K13/Kurikulum Merdeka.

## Database Schema

```php
Schema::create('lesson_plans', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('class_section_id')->constrained();
    $t->foreignId('subject_id')->constrained();
    $t->foreignId('teacher_id')->constrained('users');
    $t->foreignId('semester_id')->constrained('semesters')->nullable();
    $t->string('title');
    $t->date('lesson_date'); $t->unsignedSmallInteger('duration_minutes');
    $t->json('learning_objectives');                 // CP/TP atau KI/KD
    $t->text('material_summary');
    $t->json('activities');                          // [{phase: 'pendahuluan', minutes: 10, description: ...}]
    $t->json('assessment_methods')->nullable();
    $t->json('resources')->nullable();
    $t->string('curriculum_type', 30)->default('merdeka'); // merdeka|k13|cambridge|ib
    $t->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'completed'])->default('draft');
    $t->foreignId('reviewer_id')->nullable()->constrained('users');
    $t->timestamp('reviewed_at')->nullable();
    $t->text('reviewer_feedback')->nullable();
    $t->boolean('actually_executed')->default(false);
    $t->text('execution_note')->nullable();
    $t->timestamps(); $t->softDeletes();
    $t->index(['school_id', 'teacher_id', 'lesson_date']);
});

Schema::create('lesson_plan_attachments', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('lesson_plan_id')->constrained()->cascadeOnDelete();
    $t->string('file_path'); $t->string('file_name'); $t->string('mime');
    $t->unsignedInteger('size_bytes');
    $t->timestamps();
});
```

## API Endpoints
| Method | URI | Role |
|---|---|---|
| GET/POST | `/api/v1/lesson-plans` | teacher, admin |
| PUT | `/api/v1/lesson-plans/{id}` | teacher (own) |
| POST | `/api/v1/lesson-plans/{id}/submit` | teacher |
| POST | `/api/v1/lesson-plans/{id}/approve` | admin, headmaster |
| POST | `/api/v1/lesson-plans/{id}/reject` | admin |
| POST | `/api/v1/lesson-plans/{id}/mark-executed` | teacher |
| GET | `/api/v1/lesson-plans/{id}/pdf` | teacher, admin |
| POST | `/api/v1/lesson-plans/ai-generate` | teacher | (AI assist, dynamic provider per global rule) |

## Acceptance Criteria
- [ ] Template per kurikulum (Merdeka, K13, Cambridge)
- [ ] AI-assist generate draft RPP — pakai dynamic AI provider config (no hardcode)
- [ ] Approval workflow dengan notif
- [ ] Export PDF dengan kop sekolah dari branding
- [ ] Tracking: executed vs planned per teacher per semester

# Module 45 — Learning Analytics & Predictive Drop-Out

## Depends On
Module 05 (Attendance), 09 (Marks), 25 (BP/BK)

## What to Build
Heatmap performa per siswa, early warning siswa berisiko, predictive ML (basic logistic regression cukup), dashboard kepsek.

## Database Schema

```php
Schema::create('student_risk_scores', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->constrained();
    $t->date('snapshot_date');
    $t->decimal('attendance_score', 5, 2);
    $t->decimal('academic_score', 5, 2);
    $t->decimal('behavior_score', 5, 2);
    $t->decimal('engagement_score', 5, 2);
    $t->decimal('overall_risk', 5, 2);        // 0-100
    $t->enum('risk_level', ['low','medium','high','critical'])->default('low');
    $t->json('top_risk_factors')->nullable();
    $t->json('recommendations')->nullable();
    $t->timestamps();
    $t->unique(['student_id', 'snapshot_date']);
});

Schema::create('learning_analytics_reports', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->enum('scope', ['school','class','subject','student']);
    $t->foreignId('class_section_id')->nullable()->constrained();
    $t->foreignId('subject_id')->nullable()->constrained();
    $t->foreignId('student_id')->nullable()->constrained();
    $t->date('period_start'); $t->date('period_end');
    $t->json('metrics');
    $t->text('narrative')->nullable();         // AI-generated insight
    $t->foreignId('generated_by')->constrained('users');
    $t->timestamps();
});
```

## Acceptance Criteria
- [ ] Daily cron compute risk score
- [ ] High/critical risk → notify counselor + class teacher
- [ ] Dashboard: list top-N at-risk students
- [ ] AI narrative pakai dynamic provider (module 31)

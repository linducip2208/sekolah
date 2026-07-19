# Module 25 — BP/BK + Discipline (Counseling & Tata Tertib)

## Depends On
Module 02, 04, 19 (Notifications)

## What to Build
- BP/BK: sesi konseling, kasus, follow-up, rujukan psikolog
- Tata Tertib: poin pelanggaran/prestasi (merit-demerit), sanksi otomatis
- Anti-Bullying: anonymous reporting + escalation

## Database Schema

```php
Schema::create('counseling_sessions', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->constrained();
    $t->foreignId('counselor_id')->constrained('users');
    $t->dateTime('scheduled_at');
    $t->unsignedSmallInteger('duration_minutes')->default(45);
    $t->enum('type', ['academic', 'behavior', 'mental_health', 'career', 'family', 'social']);
    $t->enum('status', ['scheduled', 'completed', 'no_show', 'cancelled', 'rescheduled'])->default('scheduled');
    $t->text('notes')->nullable();                   // private to counselor
    $t->boolean('refer_external')->default(false);
    $t->string('referred_to')->nullable();
    $t->timestamps(); $t->softDeletes();
});

Schema::create('discipline_categories', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->string('name');                               // "Terlambat", "Tidak rapi", dll.
    $t->enum('type', ['violation', 'achievement']);
    $t->integer('point_value');                       // negative for violations, positive for achievements
    $t->text('description')->nullable();
    $t->boolean('auto_sanction')->default(false);
    $t->json('sanction_thresholds')->nullable();      // [{at_points: -10, action: 'parent_call'}]
    $t->timestamps();
});

Schema::create('discipline_records', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->constrained();
    $t->foreignId('discipline_category_id')->constrained();
    $t->foreignId('reported_by')->constrained('users');
    $t->date('incident_date');
    $t->text('description');
    $t->json('evidence_files')->nullable();
    $t->integer('points');                            // snapshot at time of report
    $t->enum('status', ['reported', 'reviewed', 'sanctioned', 'closed'])->default('reported');
    $t->text('sanction_applied')->nullable();
    $t->boolean('parent_notified')->default(false);
    $t->timestamps();
});

Schema::create('bullying_reports', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('reporter_id')->nullable()->constrained('users'); // null = anonymous
    $t->boolean('is_anonymous')->default(false);
    $t->json('victims_described')->nullable();       // free text + optional student tags
    $t->json('perpetrators_described')->nullable();
    $t->enum('type', ['verbal', 'physical', 'cyber', 'social', 'other']);
    $t->date('incident_date')->nullable();
    $t->string('location')->nullable();
    $t->text('description');
    $t->json('evidence_files')->nullable();
    $t->enum('status', ['received', 'investigating', 'action_taken', 'closed', 'unfounded'])->default('received');
    $t->foreignId('assigned_to')->nullable()->constrained('users');
    $t->text('investigation_notes')->nullable();
    $t->text('action_summary')->nullable();
    $t->timestamps();
});

Schema::create('wellness_checkins', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->constrained();
    $t->date('checkin_date');
    $t->unsignedTinyInteger('mood_score');           // 1-10
    $t->json('feeling_tags')->nullable();
    $t->text('note')->nullable();
    $t->boolean('flagged_for_review')->default(false);
    $t->timestamps();
    $t->unique(['student_id', 'checkin_date']);
});
```

## Acceptance Criteria
- [ ] BP/BK session privat antara student & counselor (parent tidak akses note)
- [ ] Auto-trigger parent call kalau poin ≤ threshold
- [ ] Anti-bullying report: mode anonymous tetap menjaga reporter identity
- [ ] Wellness check-in mingguan: mood ≤3 → flag ke counselor
- [ ] Dashboard counselor: list students at-risk

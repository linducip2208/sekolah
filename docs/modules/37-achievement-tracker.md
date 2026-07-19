# Module 37 — Achievement Tracker & Sertifikat

## Depends On
Module 02, 04

## What to Build
Tracking prestasi siswa (lomba, olimpiade, ekskul), generate sertifikat otomatis dengan layout custom, leaderboard kelas, badge digital.

## Database Schema

```php
Schema::create('achievement_categories', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->string('name');                        // "Lomba Matematika", "Tahfidz"
    $t->enum('scope', ['internal','district','province','national','international']);
    $t->unsignedSmallInteger('points')->default(10);
    $t->timestamps();
});

Schema::create('student_achievements', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->constrained();
    $t->foreignId('achievement_category_id')->constrained();
    $t->string('title');                       // "Juara 1 Olimpiade Sains"
    $t->date('achieved_at');
    $t->string('issuer')->nullable();
    $t->string('certificate_path')->nullable();
    $t->text('description')->nullable();
    $t->boolean('verified')->default(false);
    $t->foreignId('verified_by')->nullable()->constrained('users');
    $t->timestamps();
});

Schema::create('certificate_templates', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->string('name');
    $t->string('layout_path');                 // HTML template path
    $t->json('placeholders');                  // ['{{ student_name }}', '{{ achievement }}', ...]
    $t->boolean('is_default')->default(false);
    $t->timestamps();
});

Schema::create('digital_badges', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->string('name'); $t->string('icon_path');
    $t->text('description');
    $t->json('award_criteria');                // {min_attendance: 95, min_avg_score: 80}
    $t->timestamps();
});

Schema::create('student_badges', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->constrained();
    $t->foreignId('digital_badge_id')->constrained();
    $t->date('awarded_at');
    $t->timestamps();
    $t->unique(['student_id', 'digital_badge_id']);
});
```

## Acceptance Criteria
- [ ] Auto-generate sertifikat dari template + data prestasi
- [ ] Badge auto-award berdasar kriteria
- [ ] Leaderboard kelas/sekolah berdasar total points
- [ ] Public profile siswa (opsional, with parent consent) dengan portfolio prestasi

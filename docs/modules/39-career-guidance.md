# Module 39 — Career Guidance (BK Karir)

## Depends On
Module 02, 25 (BP/BK)

## What to Build
Test minat-bakat, rekomendasi jurusan kuliah, college matching, magang/PKL untuk SMK, sertifikasi industri.

## Database Schema

```php
Schema::create('career_assessments', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->constrained();
    $t->enum('test_type', ['holland_riasec','mbti','cliftonstrengths','custom']);
    $t->json('responses');
    $t->json('result');                    // {dominant_traits, recommended_majors}
    $t->date('taken_at');
    $t->timestamps();
});

Schema::create('college_database', function (Blueprint $t) {
    $t->id();
    $t->string('name'); $t->string('country', 5);
    $t->enum('type', ['ptn','pts','international','vocational']);
    $t->string('city')->nullable();
    $t->json('majors_offered')->nullable();
    $t->decimal('passing_grade_avg', 5, 2)->nullable();
    $t->string('website')->nullable();
    $t->timestamps();
});

Schema::create('internship_placements', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->constrained();
    $t->string('company_name');
    $t->string('position');
    $t->string('mentor_name')->nullable(); $t->string('mentor_phone')->nullable();
    $t->date('start_date'); $t->date('end_date');
    $t->enum('status', ['planned','active','completed','dropped']);
    $t->json('daily_logs')->nullable();
    $t->json('evaluation')->nullable();
    $t->string('certificate_path')->nullable();
    $t->timestamps();
});

Schema::create('industry_certifications', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->constrained();
    $t->string('cert_name');                  // "BNSP Junior Web Dev", "Cisco CCNA"
    $t->string('issuer');
    $t->date('issued_at'); $t->date('expires_at')->nullable();
    $t->string('cert_number')->nullable();
    $t->string('file_path')->nullable();
    $t->timestamps();
});
```

## Acceptance Criteria
- [ ] Test online → analytics → rekomendasi jurusan
- [ ] Database 4000+ universitas (seedable)
- [ ] PKL: log harian wajib, mentor industri evaluation
- [ ] Sertifikasi industri tracker untuk SMK

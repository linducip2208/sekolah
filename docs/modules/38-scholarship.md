# Module 38 — Scholarship Management (Beasiswa)

## Depends On
Module 02, 11 (Fee), 11b (Payment)

## What to Build
Daftar beasiswa internal & eksternal, aplikasi siswa, auto-discount SPP saat granted, tracking commitments.

## Database Schema

```php
Schema::create('scholarship_programs', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->string('name');                              // "Beasiswa Yatim Piatu", "Olimpiade"
    $t->enum('source', ['internal_school','external_donor','government','foundation']);
    $t->enum('discount_type', ['percentage','fixed','full']);
    $t->unsignedInteger('discount_value');           // pct*100 or cents
    $t->json('eligibility_criteria');
    $t->date('open_date'); $t->date('close_date');
    $t->unsignedInteger('quota')->nullable();
    $t->json('required_documents')->nullable();
    $t->boolean('is_active')->default(true);
    $t->timestamps();
});

Schema::create('scholarship_applications', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('scholarship_program_id')->constrained();
    $t->foreignId('student_id')->constrained();
    $t->json('documents')->nullable();
    $t->text('motivation')->nullable();
    $t->enum('status', ['draft','submitted','review','interview','granted','rejected','withdrawn'])->default('draft');
    $t->foreignId('reviewer_id')->nullable()->constrained('users');
    $t->text('reviewer_note')->nullable();
    $t->date('granted_from')->nullable(); $t->date('granted_until')->nullable();
    $t->timestamps();
});

Schema::create('scholarship_grants', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('scholarship_application_id')->constrained();
    $t->foreignId('student_id')->constrained();
    $t->foreignId('fee_invoice_id')->nullable()->constrained();
    $t->unsignedInteger('discount_applied');
    $t->date('applied_at');
    $t->timestamps();
});
```

## Acceptance Criteria
- [ ] Apply beasiswa via portal siswa/parent
- [ ] Granted → auto-apply discount ke invoice SPP
- [ ] Tracking utilization quota
- [ ] Renewal flow per semester/tahun

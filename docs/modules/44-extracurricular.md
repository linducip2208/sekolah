# Module 44 — Extracurricular (Ekstrakurikuler)

## Depends On
Module 02, 04

## What to Build
Manajemen ekskul: pilih ekskul, jadwal, absensi ekskul, prestasi tim, leveling siswa.

## Database Schema

```php
Schema::create('extracurriculars', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->string('name');                       // "Pramuka", "Basket", "Robotik"
    $t->string('icon')->nullable();
    $t->text('description')->nullable();
    $t->foreignId('coach_id')->nullable()->constrained('users');
    $t->json('schedule')->nullable();          // [{day: 'tue', start: '15:00', end: '17:00', venue: 'Lapangan'}]
    $t->unsignedInteger('capacity')->nullable();
    $t->unsignedInteger('fee_per_month')->default(0);
    $t->boolean('is_active')->default(true);
    $t->timestamps();
});

Schema::create('student_extracurriculars', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('extracurricular_id')->constrained();
    $t->foreignId('student_id')->constrained();
    $t->date('joined_at'); $t->date('left_at')->nullable();
    $t->string('level')->nullable();          // "Pemula","Menengah","Mahir"
    $t->json('achievements')->nullable();
    $t->boolean('is_active')->default(true);
    $t->timestamps();
});

Schema::create('extracurricular_attendances', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('extracurricular_id')->constrained();
    $t->foreignId('student_id')->constrained();
    $t->date('session_date');
    $t->enum('status', ['present','absent','late','excused']);
    $t->foreignId('marked_by')->constrained('users');
    $t->timestamps();
    $t->unique(['extracurricular_id','student_id','session_date']);
});
```

## Acceptance Criteria
- [ ] Siswa pilih ekskul (limit X per siswa per semester)
- [ ] Coach mark attendance
- [ ] Auto-charge fee bulanan kalau berbayar
- [ ] Sertifikat keikutsertaan akhir tahun

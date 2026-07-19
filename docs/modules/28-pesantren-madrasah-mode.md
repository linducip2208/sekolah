# Module 28 — Pesantren / Madrasah Mode

## Depends On
Module 02, 04, 05 (Attendance)

## What to Build
Toggle "religious mode" untuk pesantren / madrasah / sekolah Islam — fitur tahfidz, hafalan tracker, mutaba'ah ibadah harian, kitab kuning, jadwal sholat lokal.

## Database Schema

```php
Schema::create('religious_mode_config', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->unique()->constrained()->cascadeOnDelete();
    $t->boolean('enabled')->default(false);
    $t->enum('religion', ['islam', 'christian', 'catholic', 'hindu', 'buddha', 'confucian'])->default('islam');
    $t->enum('institution_type', ['pesantren', 'madrasah', 'sekolah_islam', 'seminari', 'pasraman', 'other'])->nullable();
    $t->json('hijri_holidays')->nullable();
    $t->boolean('use_hijri_calendar')->default(false);
    $t->json('prayer_times_config')->nullable();  // {method: 'kemenag_id', timezone: 'Asia/Jakarta'}
    $t->timestamps();
});

Schema::create('hafalan_targets', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('class_section_id')->nullable()->constrained();
    $t->string('name');                              // "Target Juz 1 Semester 1"
    $t->json('target_ranges');                       // [{start: 'AlBaqarah:1', end: 'AlBaqarah:50'}]
    $t->date('start_date'); $t->date('deadline');
    $t->timestamps();
});

Schema::create('hafalan_progress', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->constrained();
    $t->foreignId('hafalan_target_id')->nullable()->constrained();
    $t->foreignId('verified_by')->constrained('users'); // ustadz/musyrif
    $t->string('surah'); $t->unsignedSmallInteger('ayah_start'); $t->unsignedSmallInteger('ayah_end');
    $t->date('memorized_at');
    $t->enum('quality', ['excellent', 'good', 'fair', 'needs_review']);
    $t->text('note')->nullable();
    $t->json('audio_path')->nullable();             // optional setoran audio
    $t->timestamps();
    $t->index(['school_id', 'student_id']);
});

Schema::create('ibadah_logs', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->constrained();
    $t->date('log_date');
    $t->enum('subuh', ['done', 'late', 'missed', 'jamaah'])->nullable();
    $t->enum('dzuhur', ['done', 'late', 'missed', 'jamaah'])->nullable();
    $t->enum('ashar', ['done', 'late', 'missed', 'jamaah'])->nullable();
    $t->enum('maghrib', ['done', 'late', 'missed', 'jamaah'])->nullable();
    $t->enum('isya', ['done', 'late', 'missed', 'jamaah'])->nullable();
    $t->boolean('puasa_sunnah')->default(false);
    $t->boolean('tilawah_done')->default(false);
    $t->unsignedSmallInteger('tilawah_ayah_count')->default(0);
    $t->json('extra_amalan')->nullable();
    $t->foreignId('verified_by')->nullable()->constrained('users');
    $t->timestamps();
    $t->unique(['student_id', 'log_date']);
});

Schema::create('kitab_kuning_progress', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->constrained();
    $t->foreignId('teacher_id')->constrained('users');
    $t->string('kitab_name');                       // "Fathul Mu'in", "Aqidatul Awam"
    $t->string('current_bab')->nullable();
    $t->unsignedInteger('halaman_terakhir')->default(0);
    $t->date('last_session');
    $t->text('catatan_ustadz')->nullable();
    $t->timestamps();
});
```

## Acceptance Criteria
- [ ] Toggle "Religious Mode" di school setting → fitur muncul
- [ ] Hafalan: ustadz verify setoran ayat → progress siswa naik
- [ ] Mutaba'ah ibadah harian: input cepat per siswa via Flutter
- [ ] Parent lihat progress hafalan + ibadah anak
- [ ] Jadwal sholat auto fetch dari API Kemenag (configurable, no hardcode key)
- [ ] Hijri calendar overlay di kalender akademik
- [ ] Rapor tahfidz auto-generate

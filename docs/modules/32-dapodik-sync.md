# Module 32 — Dapodik Sync (Indonesia Compliance)

## Depends On
Module 04 (Academic Structure), Module 02 (Auth)

## What to Build
Sinkronisasi data sekolah ke Dapodik Kemdikbud (NPSN, NISN siswa, NIK guru, mata pelajaran, rombel). Bidirectional: import dari Dapodik untuk verifikasi, export ke Dapodik.

## Database Schema

```php
Schema::create('dapodik_config', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->unique()->constrained()->cascadeOnDelete();
    $t->string('npsn', 15);                          // Nomor Pokok Sekolah Nasional
    $t->string('username_encrypted')->nullable();
    $t->string('password_encrypted')->nullable();
    $t->string('endpoint_url', 500)->nullable();
    $t->json('field_mappings')->nullable();
    $t->timestamp('last_sync_at')->nullable();
    $t->timestamps();
});

Schema::create('dapodik_sync_logs', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->enum('direction', ['import', 'export']);
    $t->string('entity', 30);                        // students, teachers, classes, subjects
    $t->unsignedInteger('records_total')->default(0);
    $t->unsignedInteger('records_success')->default(0);
    $t->unsignedInteger('records_failed')->default(0);
    $t->json('errors')->nullable();
    $t->enum('status', ['running','completed','failed'])->default('running');
    $t->foreignId('triggered_by')->constrained('users');
    $t->timestamps();
});
```

## Notes
- API Dapodik di-handle via generic adapter — admin input endpoint sendiri (no hardcode URL)
- Mapping field configurable (NPSN, NISN, NIK, kode mapel)
- Validation rules sesuai juknis terbaru
- CSV export juga didukung untuk submit manual

## Acceptance Criteria
- [ ] Dapat import siswa dari file Dapodik (CSV)
- [ ] Dapat export ke format Dapodik
- [ ] Validation NIK 16 digit, NISN 10 digit
- [ ] Conflict resolution UI saat ada data berbeda

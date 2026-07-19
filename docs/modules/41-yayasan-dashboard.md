# Module 41 — Yayasan Dashboard (Multi-School / Foundation Level)

## Depends On
Module 21 (SaaS Panel)

## What to Build
Layer di atas school admin, di bawah super admin. Yayasan/foundation dengan banyak sekolah dapat lihat agregat semua sekolah binaan.

## Database Schema

```php
Schema::create('foundations', function (Blueprint $t) {
    $t->id();
    $t->string('name'); $t->string('slug')->unique();
    $t->string('logo_path')->nullable();
    $t->text('address')->nullable();
    $t->string('npwp')->nullable();
    $t->json('contact')->nullable();
    $t->boolean('is_active')->default(true);
    $t->timestamps(); $t->softDeletes();
});

Schema::create('foundation_school_links', function (Blueprint $t) {
    $t->id();
    $t->foreignId('foundation_id')->constrained()->cascadeOnDelete();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->date('joined_at');
    $t->boolean('is_primary_school')->default(false);
    $t->timestamps();
    $t->unique(['foundation_id', 'school_id']);
});

Schema::create('foundation_admins', function (Blueprint $t) {
    $t->id();
    $t->foreignId('foundation_id')->constrained()->cascadeOnDelete();
    $t->foreignId('user_id')->constrained()->cascadeOnDelete();
    $t->enum('role', ['ketua_yayasan','pengurus','bendahara','sekretaris']);
    $t->timestamps();
});
```

## Acceptance Criteria
- [ ] Login as foundation_admin → lihat dashboard semua sekolah binaan
- [ ] Aggregate metrics: total siswa, total revenue, total guru, dll.
- [ ] Drill-down ke per-sekolah view (read-only)
- [ ] Cross-school comparison
- [ ] Foundation-level branding (logo yayasan)

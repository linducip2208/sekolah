# Module 22 — PPDB Online (Penerimaan Peserta Didik Baru)

## Depends On
- Module 02 (Auth — public registration page), Module 03 (School Setup), Module 10 (Admission), Module 11b (Payment Gateway)

## What to Build
Online admission portal: pendaftaran calon siswa via web (subdomain sekolah / public link), workflow zonasi, jalur prestasi, jalur undian, pembayaran formulir, seleksi otomatis, pengumuman.

## Database Schema

```php
Schema::create('ppdb_periods', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('academic_year_id')->constrained();
    $t->string('name');                                    // "PPDB Gelombang 1 2025/2026"
    $t->date('open_date'); $t->date('close_date');
    $t->date('announcement_date')->nullable();
    $t->date('reregistration_deadline')->nullable();
    $t->unsignedInteger('form_fee')->default(0);           // biaya formulir (cents)
    $t->json('jalur_config')->nullable();                  // ['zonasi' => quota, 'prestasi' => ..., 'afirmasi' => ..., 'undian' => ...]
    $t->json('document_requirements')->nullable();         // ['kk', 'akta', 'rapor', ...]
    $t->boolean('is_published')->default(false);
    $t->timestamps(); $t->softDeletes();
});

Schema::create('ppdb_applications', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('ppdb_period_id')->constrained();
    $t->string('registration_no', 30)->unique();           // PPDB-2025-XXXXX
    $t->string('jalur', 20);                               // zonasi|prestasi|afirmasi|undian|reguler
    $t->string('student_name'); $t->string('nisn', 20)->nullable();
    $t->date('date_of_birth'); $t->string('gender', 10);
    $t->text('address'); $t->string('district'); $t->string('city');
    $t->decimal('home_lat', 10, 7)->nullable(); $t->decimal('home_lng', 10, 7)->nullable();
    $t->decimal('distance_km', 8, 3)->nullable();          // auto-calc dari sekolah
    $t->string('previous_school')->nullable();
    $t->string('parent_name'); $t->string('parent_phone'); $t->string('parent_email');
    $t->json('documents')->nullable();                     // {kk: path, akta: path, ...}
    $t->json('achievements')->nullable();                  // [{title, level, year, file}]
    $t->decimal('average_score', 5, 2)->nullable();        // rata-rata rapor
    $t->decimal('ranking_score', 8, 3)->nullable();        // skor seleksi auto
    $t->unsignedSmallInteger('rank_position')->nullable();
    $t->string('status', 30)->default('draft');            // draft|submitted|verified|accepted|rejected|enrolled|withdrew
    $t->foreignId('reviewer_id')->nullable()->constrained('users');
    $t->text('reviewer_note')->nullable();
    $t->foreignId('form_payment_id')->nullable()->constrained('fee_payments');
    $t->timestamp('submitted_at')->nullable();
    $t->timestamp('verified_at')->nullable();
    $t->timestamp('accepted_at')->nullable();
    $t->timestamps(); $t->softDeletes();
    $t->index(['school_id', 'ppdb_period_id', 'status']);
});

Schema::create('ppdb_zonasi_zones', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->string('district'); $t->string('subdistrict')->nullable();
    $t->decimal('priority_score', 5, 2)->default(100);
    $t->timestamps();
});
```

## API Endpoints

| Method | URI | Role |
|---|---|---|
| GET | `/api/v1/public/ppdb/{subdomain}/periods` | public |
| POST | `/api/v1/public/ppdb/{subdomain}/register` | public |
| GET | `/api/v1/ppdb/applications/me` | applicant |
| POST | `/api/v1/ppdb/applications/{id}/submit` | applicant |
| POST | `/api/v1/ppdb/applications/{id}/upload-doc` | applicant |
| GET | `/api/v1/admin/ppdb/periods` | admin |
| POST | `/api/v1/admin/ppdb/periods` | admin |
| GET | `/api/v1/admin/ppdb/applications` | admin, receptionist |
| POST | `/api/v1/admin/ppdb/applications/{id}/verify` | admin |
| POST | `/api/v1/admin/ppdb/applications/{id}/accept` | admin |
| POST | `/api/v1/admin/ppdb/applications/{id}/reject` | admin |
| POST | `/api/v1/admin/ppdb/{periodId}/run-selection` | admin |
| POST | `/api/v1/admin/ppdb/{periodId}/announce` | admin |

## Programmatic SEO (Wajib per global rule)

Generate halaman:
- `/ppdb/{city}` — list sekolah buka PPDB di kota
- `/ppdb/{school-slug}` — detail sekolah + tombol daftar
- `/best-schools-{city}-{year}` — top sekolah listing
- `/compare/{school-a}-vs-{school-b}` — bandingkan 2 sekolah
- JSON-LD: EducationalOrganization, FAQPage

## Acceptance Criteria
- [ ] Calon siswa bisa daftar tanpa login (public form)
- [ ] Auto-hitung jarak rumah ke sekolah pakai Haversine (lat/lng)
- [ ] Bayar form via gateway dynamic (module 11b)
- [ ] Auto-rank zonasi (jarak), prestasi (skor), undian (random seed)
- [ ] Pengumuman published → notif email + WA + push
- [ ] Diterima → otomatis convert ke `students` table + buat user account

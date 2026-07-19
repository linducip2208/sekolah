# Module 24 — UKS / Klinik Sekolah

## Depends On
Module 02 (Auth), Module 04 (Academic Structure)

## What to Build
Pencatatan kunjungan klinik sekolah, riwayat medis siswa, alergi, vaksinasi, obat-obatan, rujukan eksternal.

## Database Schema

```php
Schema::create('medical_records', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->unique()->constrained();
    $t->string('blood_type', 5)->nullable();
    $t->json('allergies')->nullable();              // ['kacang', 'gluten']
    $t->json('chronic_conditions')->nullable();
    $t->json('current_medications')->nullable();
    $t->string('emergency_contact_name')->nullable();
    $t->string('emergency_contact_phone')->nullable();
    $t->string('insurance_provider')->nullable();
    $t->string('insurance_number')->nullable();
    $t->timestamps();
});

Schema::create('clinic_visits', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->constrained();
    $t->foreignId('attended_by')->constrained('users'); // perawat/dokter sekolah
    $t->timestamp('visit_at');
    $t->text('symptoms');
    $t->text('diagnosis')->nullable();
    $t->text('treatment')->nullable();
    $t->json('medications_given')->nullable();
    $t->decimal('temperature_c', 4, 1)->nullable();
    $t->string('blood_pressure', 10)->nullable();
    $t->boolean('parent_notified')->default(false);
    $t->boolean('returned_to_class')->default(true);
    $t->boolean('sent_home')->default(false);
    $t->boolean('referred_external')->default(false);
    $t->string('referred_to')->nullable();
    $t->timestamps();
    $t->index(['school_id', 'student_id', 'visit_at']);
});

Schema::create('vaccinations', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->constrained();
    $t->string('vaccine_name');                       // BCG, MR, HPV, dll.
    $t->date('vaccinated_at');
    $t->string('batch_number')->nullable();
    $t->string('administered_by')->nullable();
    $t->date('next_dose_due')->nullable();
    $t->string('certificate_path')->nullable();
    $t->timestamps();
});
```

## API Endpoints
| Method | URI | Role |
|---|---|---|
| GET/PUT | `/api/v1/medical/students/{id}/record` | nurse, admin |
| GET/POST | `/api/v1/medical/visits` | nurse |
| GET | `/api/v1/medical/visits/student/{id}` | nurse, admin, parent |
| POST | `/api/v1/medical/visits/{id}/notify-parent` | nurse |
| GET/POST | `/api/v1/medical/vaccinations/student/{id}` | nurse, parent |

## Acceptance Criteria
- [ ] Perawat input kunjungan offline-first (sync saat online)
- [ ] Auto-notify parent kalau diagnosis serius / sent_home
- [ ] Vaksinasi dengan reminder dosis berikutnya
- [ ] Riwayat lengkap viewable parent (anak sendiri)
- [ ] Export rekap kunjungan untuk Dinas Kesehatan

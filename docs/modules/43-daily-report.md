# Module 43 — Daily Report to Parent

## Depends On
Module 05 (Attendance), 09 (Marks), 19 (Notifications), 24 (UKS), 25 (BP/BK)

## What to Build
Auto-generate laporan harian per anak ke parent: kehadiran, pelajaran hari itu, mood/wellness, makan kantin, transport, tugas baru. Push tiap sore.

## Database Schema

```php
Schema::create('daily_reports', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->constrained();
    $t->date('report_date');
    $t->json('attendance');                   // {status, time_in, time_out}
    $t->json('subjects_today')->nullable();
    $t->json('homework_due')->nullable();
    $t->json('canteen_summary')->nullable();
    $t->json('clinic_visit')->nullable();
    $t->json('discipline_events')->nullable();
    $t->json('wellness_checkin')->nullable();
    $t->json('teacher_notes')->nullable();
    $t->timestamp('sent_at')->nullable();
    $t->timestamps();
    $t->unique(['student_id', 'report_date']);
});

Schema::create('daily_report_preferences', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('user_id')->unique()->constrained();
    $t->boolean('enabled')->default(true);
    $t->time('preferred_send_time')->default('17:00:00');
    $t->json('channels')->nullable();          // ['fcm','email','whatsapp']
    $t->json('sections_enabled')->nullable();  // toggle which sections to include
    $t->timestamps();
});
```

## Acceptance Criteria
- [ ] Cron tiap hari 17:00 → generate report semua siswa aktif
- [ ] Push ke parent via FCM + email + WA (jika enable)
- [ ] Parent toggle preferences
- [ ] Beautifully formatted (HTML email + push payload)

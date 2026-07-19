# Module 35 — Live Class (Video Conference Integration)

## Depends On
Module 06 (Timetable), Module 07 (Online Classroom)

## What to Build
Integrasi video conference (Zoom / Meet / Jitsi / BigBlueButton) ke timetable. Auto-generate link untuk setiap jadwal kelas online. Recording management.

**WAJIB dynamic provider** (sama pattern Module 11b & 31). Class names: `ZoomFormatAdapter`, `MeetFormatAdapter`, `JitsiFormatAdapter` adalah **format-based** (server-API style), bukan vendor lock-in. Boleh hardcode? **Tidak.**

## Database Schema

```php
Schema::create('video_providers', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->string('name');
    $t->string('slug');
    $t->enum('api_format', ['oauth_meeting_api', 'rest_room_api', 'self_hosted_jitsi', 'self_hosted_bbb', 'manual_link']);
    $t->string('base_url')->nullable();
    $t->text('client_id_encrypted')->nullable();
    $t->text('client_secret_encrypted')->nullable();
    $t->text('access_token_encrypted')->nullable();
    $t->json('extra_config')->nullable();
    $t->boolean('is_active')->default(true);
    $t->timestamps();
});

Schema::create('live_class_sessions', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('class_section_id')->constrained();
    $t->foreignId('subject_id')->constrained();
    $t->foreignId('teacher_id')->constrained('users');
    $t->foreignId('video_provider_id')->nullable()->constrained();
    $t->string('topic');
    $t->dateTime('scheduled_start');
    $t->unsignedSmallInteger('duration_minutes');
    $t->string('meeting_id')->nullable();
    $t->string('join_url', 1000)->nullable();
    $t->string('host_url', 1000)->nullable();
    $t->string('passcode')->nullable();
    $t->enum('status', ['scheduled','live','ended','cancelled'])->default('scheduled');
    $t->string('recording_url', 1000)->nullable();
    $t->timestamps();
});

Schema::create('live_class_attendances', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('live_class_session_id')->constrained();
    $t->foreignId('student_id')->constrained();
    $t->timestamp('joined_at')->nullable();
    $t->timestamp('left_at')->nullable();
    $t->unsignedInteger('total_minutes')->default(0);
    $t->timestamps();
    $t->unique(['live_class_session_id', 'student_id']);
});
```

## Acceptance Criteria
- [ ] Auto-create meeting saat schedule timetable online
- [ ] Join URL via Flutter buka webview / native SDK
- [ ] Auto-mark attendance saat student join >= 80% durasi
- [ ] Recording auto-save ke S3, tersedia 30 hari
- [ ] Self-hosted Jitsi tetap support (no vendor lock-in)

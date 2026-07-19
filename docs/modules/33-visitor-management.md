# Module 33 — Visitor Management

## Depends On
Module 02 (Auth)

## What to Build
Resepsionis log tamu masuk/keluar, scan KTP, auto-print badge, notify yang dituju, riwayat kunjungan, panic button.

## Database Schema

```php
Schema::create('visitor_logs', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->string('visitor_name');
    $t->string('id_number')->nullable();              // KTP/SIM
    $t->string('phone')->nullable();
    $t->string('photo_path')->nullable();
    $t->string('purpose');
    $t->foreignId('host_user_id')->nullable()->constrained('users');
    $t->string('badge_no', 20)->nullable();
    $t->timestamp('checked_in_at');
    $t->timestamp('checked_out_at')->nullable();
    $t->foreignId('logged_by')->constrained('users');
    $t->json('items_carried')->nullable();
    $t->boolean('is_blacklisted')->default(false);
    $t->text('note')->nullable();
    $t->timestamps();
    $t->index(['school_id', 'checked_in_at']);
});

Schema::create('visitor_blacklist', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->string('id_number')->nullable();
    $t->string('full_name');
    $t->text('reason');
    $t->foreignId('added_by')->constrained('users');
    $t->timestamps();
});
```

## Acceptance Criteria
- [ ] Resepsionis input cepat dengan foto KTP (camera)
- [ ] Auto-notify host user (FCM push)
- [ ] Print badge dengan QR
- [ ] Auto-check-out saat scan keluar
- [ ] Blacklist alert saat KTP match
- [ ] Daily report kunjungan

# Module 42 — Event Management

## Depends On
Module 02, 11b (Payment for paid events)

## What to Build
Acara sekolah: rapat ortu, kunjungan industri, festival sekolah, lomba internal. RSVP, ticket QR, kapasitas, payment.

## Database Schema

```php
Schema::create('school_events', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->string('title'); $t->string('slug');
    $t->text('description');
    $t->enum('event_type', ['parent_meeting','field_trip','festival','competition','workshop','seminar']);
    $t->dateTime('starts_at'); $t->dateTime('ends_at');
    $t->string('venue'); $t->string('city')->nullable();
    $t->decimal('venue_lat', 10, 7)->nullable();
    $t->decimal('venue_lng', 10, 7)->nullable();
    $t->unsignedInteger('capacity')->nullable();
    $t->unsignedInteger('ticket_price')->default(0);
    $t->json('target_audience')->nullable();    // ['parent','student','teacher','public']
    $t->string('cover_image_path')->nullable();
    $t->boolean('require_rsvp')->default(true);
    $t->boolean('is_published')->default(false);
    $t->timestamps(); $t->softDeletes();
    $t->unique(['school_id', 'slug']);
});

Schema::create('event_rsvps', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('school_event_id')->constrained();
    $t->foreignId('user_id')->constrained();
    $t->unsignedSmallInteger('guests_count')->default(0);
    $t->enum('status', ['going','maybe','not_going','cancelled'])->default('going');
    $t->foreignId('payment_transaction_id')->nullable()->constrained();
    $t->string('ticket_qr_token', 100)->nullable();
    $t->timestamp('checked_in_at')->nullable();
    $t->timestamps();
    $t->unique(['school_event_id', 'user_id']);
});
```

## Acceptance Criteria
- [ ] Event public page (programmatic SEO `/events/{school-slug}/{event-slug}`)
- [ ] RSVP dengan ticket QR ke email
- [ ] Paid event → bayar via gateway dynamic
- [ ] Check-in di acara: scan QR
- [ ] Capacity enforcement
- [ ] Calendar export (.ics)

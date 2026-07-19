# Module 30 — Alumni Network

## Depends On
Module 02 (Auth), Module 04 (Academic Structure)

## What to Build
Database alumni + portal alumni: profil karir, job board internal, mentoring, reuni, donasi.

## Database Schema

```php
Schema::create('alumni_profiles', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('user_id')->unique()->constrained();
    $t->unsignedSmallInteger('graduation_year');
    $t->string('class_of')->nullable();             // "XII IPA 1"
    $t->string('current_position')->nullable();
    $t->string('current_company')->nullable();
    $t->string('city')->nullable(); $t->string('country', 5)->default('ID');
    $t->string('linkedin_url')->nullable();
    $t->string('industry')->nullable();
    $t->json('skills')->nullable();
    $t->boolean('willing_to_mentor')->default(false);
    $t->boolean('willing_to_offer_internship')->default(false);
    $t->boolean('verified')->default(false);
    $t->timestamps();
});

Schema::create('alumni_job_posts', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('posted_by')->constrained('users');
    $t->string('title'); $t->string('company');
    $t->string('location'); $t->string('type', 30); // full_time|part_time|internship|contract
    $t->text('description');
    $t->string('apply_url')->nullable();
    $t->date('expires_at');
    $t->boolean('is_active')->default(true);
    $t->timestamps();
});

Schema::create('alumni_mentorships', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('mentor_id')->constrained('users');
    $t->foreignId('mentee_id')->constrained('users');
    $t->enum('status', ['requested', 'active', 'completed', 'cancelled'])->default('requested');
    $t->text('goals')->nullable();
    $t->date('start_date'); $t->date('end_date')->nullable();
    $t->timestamps();
});

Schema::create('alumni_events', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->string('title'); $t->text('description');
    $t->dateTime('starts_at'); $t->dateTime('ends_at');
    $t->string('venue'); $t->string('city');
    $t->unsignedInteger('capacity')->nullable();
    $t->unsignedInteger('ticket_price')->default(0); // cents
    $t->boolean('is_published')->default(false);
    $t->timestamps();
});
```

## Acceptance Criteria
- [ ] Alumni claim profile (verify via WA OTP atau email)
- [ ] Job board public + filter by industry/year
- [ ] Mentor matching: mentee request → mentor accept
- [ ] Event RSVP dengan ticket QR (paid event via gateway)
- [ ] Programmatic SEO: `/alumni/{school-slug}`, `/alumni/{school-slug}/{year}`, `/alumni-jobs/{industry}`

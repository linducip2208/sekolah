# Module 29 — Donations & Fundraising

## Depends On
Module 11b (Payment Gateway), Module 30 (Alumni)

## What to Build
Crowdfunding/donasi untuk sekolah — campaign per project (renovasi, beasiswa, korban bencana), donatur public + alumni + parent, payment via gateway dynamic, kuitansi pajak otomatis.

## Database Schema

```php
Schema::create('donation_campaigns', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->string('title'); $t->string('slug');
    $t->text('description');
    $t->unsignedBigInteger('target_amount');         // cents
    $t->unsignedBigInteger('raised_amount')->default(0);
    $t->date('start_date'); $t->date('end_date');
    $t->string('cover_image_path')->nullable();
    $t->json('updates')->nullable();                 // [{date, title, description}]
    $t->enum('category', ['scholarship', 'building', 'equipment', 'emergency', 'general'])->default('general');
    $t->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
    $t->boolean('is_public')->default(true);
    $t->timestamps(); $t->softDeletes();
    $t->unique(['school_id', 'slug']);
});

Schema::create('donations', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('donation_campaign_id')->nullable()->constrained();
    $t->foreignId('user_id')->nullable()->constrained();   // null = anonymous public
    $t->string('donor_name')->nullable();
    $t->string('donor_email')->nullable();
    $t->string('donor_phone')->nullable();
    $t->string('npwp')->nullable();                  // untuk kuitansi pajak
    $t->boolean('is_anonymous')->default(false);
    $t->boolean('show_amount')->default(true);
    $t->unsignedInteger('amount');
    $t->text('message')->nullable();
    $t->foreignId('payment_transaction_id')->nullable()->constrained();
    $t->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
    $t->string('receipt_no', 30)->nullable()->unique();
    $t->timestamp('donated_at')->nullable();
    $t->timestamps();
});
```

## API & SEO

- Public campaign page: `/donate/{school-slug}/{campaign-slug}` (programmatic SEO with JSON-LD `MonetaryDonation`)
- Donor wall: `/donate/{school-slug}/donors`
- Receipt PDF auto-email pas donation completed
- Share buttons (WhatsApp, FB, Twitter, Threads) — track UTM source

## Acceptance Criteria
- [ ] Public campaign page bisa diakses tanpa login
- [ ] Donasi via payment gateway sekolah (dynamic per modul 11b)
- [ ] Donor wall dengan masking nama anonim
- [ ] Kuitansi pajak otomatis (PPh 21 deduction format)
- [ ] Email thank-you + share link
- [ ] Real-time progress bar update via broadcasting

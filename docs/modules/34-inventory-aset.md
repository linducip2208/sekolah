# Module 34 — Inventory / Aset Sekolah

## Depends On
Module 02

## What to Build
Tracking aset sekolah: lab IPA, alat olahraga, lab komputer, proyektor, AC. Peminjaman, maintenance request, lifecycle tracking.

## Database Schema

```php
Schema::create('asset_categories', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->string('name');                          // "Lab IPA", "Olahraga"
    $t->string('icon')->nullable();
    $t->timestamps();
});

Schema::create('assets', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('asset_category_id')->constrained();
    $t->string('asset_code', 50)->unique();
    $t->string('name'); $t->text('description')->nullable();
    $t->string('serial_number')->nullable();
    $t->date('purchased_at')->nullable();
    $t->unsignedInteger('purchase_price')->nullable();
    $t->date('warranty_until')->nullable();
    $t->string('location')->nullable();
    $t->string('photo_path')->nullable();
    $t->enum('condition', ['excellent','good','fair','damaged','disposed'])->default('good');
    $t->enum('status', ['available','borrowed','maintenance','disposed'])->default('available');
    $t->json('specs')->nullable();
    $t->timestamps(); $t->softDeletes();
});

Schema::create('asset_loans', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('asset_id')->constrained();
    $t->foreignId('borrower_id')->constrained('users');
    $t->foreignId('approved_by')->nullable()->constrained('users');
    $t->date('borrowed_at'); $t->date('due_at'); $t->date('returned_at')->nullable();
    $t->enum('status', ['pending','active','overdue','returned','lost'])->default('pending');
    $t->text('note')->nullable();
    $t->timestamps();
});

Schema::create('maintenance_requests', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('asset_id')->nullable()->constrained();
    $t->string('location_text')->nullable();
    $t->foreignId('reported_by')->constrained('users');
    $t->text('issue_description');
    $t->json('photos')->nullable();
    $t->enum('priority', ['low','medium','high','critical'])->default('medium');
    $t->enum('status', ['reported','assigned','in_progress','resolved','rejected'])->default('reported');
    $t->foreignId('assigned_to')->nullable()->constrained('users');
    $t->text('resolution_note')->nullable();
    $t->timestamp('resolved_at')->nullable();
    $t->unsignedInteger('cost')->nullable();
    $t->timestamps();
});
```

## Acceptance Criteria
- [ ] QR scan asset → detail + history
- [ ] Loan workflow dengan approval
- [ ] Maintenance request dengan foto + auto-assign ke teknisi
- [ ] Depreciation tracking
- [ ] Audit report tahunan

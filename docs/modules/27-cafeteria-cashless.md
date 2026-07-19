# Module 27 — Cafeteria / Kantin Cashless

## Depends On
Module 02, 11b (Payment Gateway untuk top-up)

## What to Build
Top-up saldo siswa via parent → siswa pesan kantin tanpa cash → tap kartu/QR di kasir.

## Database Schema

```php
Schema::create('canteen_wallets', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->unique()->constrained();
    $t->unsignedInteger('balance')->default(0);     // cents
    $t->unsignedInteger('daily_limit')->default(0); // 0 = unlimited
    $t->json('blocked_categories')->nullable();      // parent block makanan tertentu
    $t->boolean('is_locked')->default(false);
    $t->timestamps();
});

Schema::create('canteen_topups', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('canteen_wallet_id')->constrained()->cascadeOnDelete();
    $t->foreignId('initiated_by')->constrained('users');
    $t->foreignId('payment_transaction_id')->nullable()->constrained();
    $t->unsignedInteger('amount');                  // cents
    $t->enum('status', ['pending','completed','failed','refunded'])->default('pending');
    $t->timestamps();
});

Schema::create('canteen_categories', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->string('name'); $t->string('icon')->nullable();
    $t->boolean('healthy_tag')->default(false);
    $t->timestamps();
});

Schema::create('canteen_menu_items', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('canteen_category_id')->constrained();
    $t->string('name'); $t->text('description')->nullable();
    $t->unsignedInteger('price'); // cents
    $t->string('photo_path')->nullable();
    $t->json('allergens')->nullable();
    $t->boolean('is_available')->default(true);
    $t->unsignedInteger('stock_today')->nullable();
    $t->timestamps();
});

Schema::create('canteen_orders', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->constrained();
    $t->foreignId('canteen_wallet_id')->constrained();
    $t->string('order_no', 30)->unique();
    $t->dateTime('pickup_at')->nullable();        // for pre-order
    $t->json('items');                              // [{menu_item_id, qty, price, subtotal}]
    $t->unsignedInteger('total');
    $t->enum('source', ['preorder', 'walkin']);
    $t->enum('status', ['pending','preparing','ready','picked_up','cancelled'])->default('pending');
    $t->timestamps();
});
```

## Acceptance Criteria
- [ ] Parent top-up via gateway (Module 11b) — saldo masuk otomatis setelah `paid`
- [ ] Pre-order pagi → siap saat istirahat
- [ ] Block kategori (junk food) → siswa tidak bisa pesan
- [ ] Daily limit per anak
- [ ] Parent dapat notifikasi saat anak transaksi
- [ ] Laporan harian per kantin: revenue, popular items

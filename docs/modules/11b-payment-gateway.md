# Module 11b — Payment Gateway (Dynamic, No Hardcoded Providers)

## Depends On
- Module 01 (Multi-tenant Foundation — `school_id` scope)
- Module 02 (Auth & Roles — admin, parent, student access)
- Module 11 (Fee & Invoice — `fee_invoices`, `fee_payments`)

## Why This Module Exists

Modul 11 hanya mencatat pembayaran offline (cash, transfer manual, cheque). Modul ini menambahkan **payment gateway online** sehingga orang tua / siswa bisa bayar SPP via VA, QRIS, e-wallet, kartu kredit, dll. — **tanpa hardcode vendor manapun**.

**Aturan global yang berlaku** (lihat `~/.claude/CLAUDE.md` "No Hardcoded Providers"):

- ❌ TIDAK ADA class `MidtransAdapter`, `XenditAdapter`, `DokuAdapter`, dll.
- ❌ TIDAK ADA hardcoded base URL / API key / merchant ID
- ❌ TIDAK ADA "default provider per fitur" mapping di code
- ✅ Adapter **berdasarkan format API**, bukan vendor
- ✅ Admin sekolah input kredensial sendiri via UI
- ✅ Optional preset templates JSON untuk autofill convenience saja

---

## Design Principles

### Format-Based Adapters (BUKAN Per-Vendor)

| Adapter | Format yang di-handle | Vendor yang cocok (contoh, BUKAN hardcode) |
|---|---|---|
| `RedirectCheckoutAdapter` | POST create transaction → response berisi `redirect_url` ke hosted checkout page | Midtrans Snap, Xendit Invoice, Doku Checkout, iPaymu, PayU, Stripe Checkout, Mollie, Razorpay |
| `VirtualAccountAdapter` | POST create VA → response berisi `va_number`, `bank_code`, `expired_at` | Midtrans VA, Xendit VA, Doku VA, BCA VA Direct, Permata VA |
| `EwalletDeeplinkAdapter` | POST create charge → response berisi `deeplink_url` (mobile) atau `qr_url` | Midtrans GoPay/ShopeePay, Xendit OVO/Dana/LinkAja |
| `QrisDynamicAdapter` | POST create QR → response berisi `qr_string` (EMVCo format) | QRIS lewat Midtrans/Xendit/Espay/iPaymu |
| `QrisStaticAdapter` | Sekolah upload QR statis (PNG + EMVCo string), tidak ada API call | QRIS Statis (1 QR per sekolah) |
| `BankTransferManualAdapter` | Sekolah daftarkan rekening, parent transfer manual + upload bukti | (no vendor) — admin verifikasi manual |
| `CashAdapter` | Bayar di kasir sekolah (built-in, default) | (no vendor) — admin catat di POS |

**Class naming WAJIB:** format-based names. `RedirectCheckoutAdapter`, BUKAN `MidtransAdapter`.

---

## Database Schema

### `payment_providers` — Provider yang admin sekolah daftarkan

```php
Schema::create('payment_providers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('name');                          // user input bebas: "Midtrans Production", "QRIS Sekolah"
    $table->string('slug');                          // URL-safe, unique per school
    $table->string('api_format');                    // enum: redirect_checkout|virtual_account|ewallet_deeplink|qris_dynamic|qris_static|bank_transfer_manual|cash
    $table->string('base_url')->nullable();          // user input — null untuk static/manual/cash
    $table->text('api_key_encrypted')->nullable();   // encrypted at rest
    $table->text('secret_key_encrypted')->nullable();
    $table->text('merchant_id_encrypted')->nullable();
    $table->json('extra_config')->nullable();        // field tambahan vendor-specific (snap_url, callback_url, signature_method, dll)
    $table->json('extra_headers')->nullable();       // custom HTTP headers (Authorization scheme, X-API-Key, etc.)
    $table->string('webhook_secret_encrypted')->nullable();
    $table->string('callback_url')->nullable();      // URL yang dikirim ke gateway untuk redirect
    $table->boolean('is_sandbox')->default(true);
    $table->boolean('is_active')->default(true);
    $table->unsignedSmallInteger('priority')->default(0); // sorting di UI
    $table->timestamps();
    $table->softDeletes();

    $table->unique(['school_id', 'slug']);
    $table->index(['school_id', 'is_active', 'api_format']);
});
```

### `payment_methods` — Metode bayar yang ditawarkan ke parent

```php
Schema::create('payment_methods', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('payment_provider_id')->constrained()->cascadeOnDelete();
    $table->string('code');                          // 'va_bca', 'va_mandiri', 'gopay', 'qris', 'credit_card', 'bank_manual'
    $table->string('display_name');                  // "Virtual Account BCA", "GoPay", "QRIS"
    $table->string('logo_url')->nullable();          // URL logo (admin upload sendiri)
    $table->string('instruction_template')->nullable(); // text/html — instruksi bayar untuk user
    $table->unsignedInteger('fee_flat')->default(0);     // biaya admin flat (cents)
    $table->unsignedSmallInteger('fee_percent_bp')->default(0); // basis points (100 = 1%)
    $table->unsignedInteger('fee_borne_by')->default(0); // 0=parent, 1=school
    $table->unsignedInteger('min_amount')->default(0);
    $table->unsignedInteger('max_amount')->nullable();
    $table->unsignedInteger('expiry_minutes')->default(1440); // VA/QR expire
    $table->boolean('is_active')->default(true);
    $table->unsignedSmallInteger('sort_order')->default(0);
    $table->timestamps();

    $table->unique(['school_id', 'code']);
    $table->index(['school_id', 'is_active', 'sort_order']);
});
```

### `payment_transactions` — Transaksi gateway (link ke invoice)

```php
Schema::create('payment_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('fee_invoice_id')->constrained()->cascadeOnDelete();
    $table->foreignId('payment_method_id')->constrained();
    $table->foreignId('payment_provider_id')->constrained();
    $table->foreignId('initiated_by')->constrained('users');
    $table->foreignId('fee_payment_id')->nullable()->constrained()->nullOnDelete(); // diisi saat paid

    $table->string('reference_no', 100)->unique();   // internal: PAY-{school}-{invoice}-{rand}
    $table->string('external_id', 200)->nullable();  // dari gateway (order_id / charge_id)
    $table->string('gateway_transaction_id', 200)->nullable(); // gateway-side transaction id

    $table->unsignedInteger('amount');               // gross (cents)
    $table->unsignedInteger('fee_amount')->default(0); // biaya admin
    $table->unsignedInteger('net_amount');           // diterima sekolah
    $table->string('currency', 3)->default('IDR');

    $table->enum('status', [
        'pending', 'awaiting_payment', 'paid', 'expired', 'failed', 'cancelled', 'refunded', 'disputed'
    ])->default('pending');

    // payload-specific fields
    $table->string('redirect_url', 1000)->nullable();
    $table->string('va_number', 50)->nullable();
    $table->string('va_bank_code', 20)->nullable();
    $table->text('qr_string')->nullable();
    $table->string('deeplink_url', 1000)->nullable();

    $table->json('raw_request')->nullable();
    $table->json('raw_response')->nullable();

    $table->timestamp('expired_at')->nullable();
    $table->timestamp('paid_at')->nullable();

    $table->timestamps();
    $table->index(['school_id', 'fee_invoice_id', 'status']);
    $table->index(['school_id', 'status', 'expired_at']);
    $table->index('external_id');
});
```

### `payment_webhook_logs` — Audit trail webhook

```php
Schema::create('payment_webhook_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('payment_provider_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('payment_transaction_id')->nullable()->constrained()->nullOnDelete();
    $table->string('source_ip', 45)->nullable();
    $table->json('headers')->nullable();
    $table->json('payload')->nullable();
    $table->string('signature_status', 30)->nullable(); // valid|invalid|missing
    $table->string('processing_status', 30)->default('received'); // received|processed|failed|duplicate
    $table->text('error_message')->nullable();
    $table->timestamps();
    $table->index('payment_provider_id');
    $table->index(['processing_status', 'created_at']);
});
```

---

## Architecture

```
┌──────────────────────────────────────────────────────────────────────┐
│                         Parent / Student                              │
│                              │                                        │
│                              ▼                                        │
│      POST /api/v1/payments/initiate { invoice_id, method_id }        │
│                              │                                        │
│                              ▼                                        │
│                    ┌─────────────────────┐                            │
│                    │  PaymentController  │                            │
│                    └──────────┬──────────┘                            │
│                               │                                       │
│                               ▼                                       │
│                    ┌─────────────────────┐                            │
│                    │  PaymentService     │                            │
│                    │ ::createTransaction │                            │
│                    └──────────┬──────────┘                            │
│                               │ resolve provider.api_format           │
│                               ▼                                       │
│                    ┌─────────────────────┐                            │
│                    │ PaymentAdapterFactory │                          │
│                    │  ::for($provider)   │                            │
│                    └──────────┬──────────┘                            │
│                               │                                       │
│        ┌──────────┬───────────┼────────────┬──────────────┐          │
│        ▼          ▼           ▼            ▼              ▼          │
│  Redirect     VirtualAcct   Ewallet     QrisDynamic   BankManual    │
│  Checkout      Adapter      Deeplink      Adapter      Adapter       │
│  Adapter                    Adapter                                  │
│        │          │           │            │              │          │
│        ▼          ▼           ▼            ▼              ▼          │
│  HTTP POST to provider.base_url with provider.api_key_encrypted     │
│        │                                                              │
│        ▼                                                              │
│  Response → PaymentTransaction stored (redirect_url/va/qr/deeplink)  │
│        │                                                              │
│        ▼                                                              │
│  Return to client (JSON)                                              │
└──────────────────────────────────────────────────────────────────────┘

Webhook (async, server-to-server):
  Gateway → POST /api/v1/payments/webhook/{provider_slug}
        │
        ▼
  WebhookController::receive
        │
        ▼  (1) verify signature via adapter
        ▼  (2) parse payload via adapter
        ▼  (3) idempotent update PaymentTransaction
        ▼  (4) if paid: create FeePayment + update FeeInvoice.status
        ▼  (5) NotifyPaymentReceivedJob dispatch
  Return 200 OK
```

---

## Adapter Interface

```php
namespace App\Services\Payment\Contracts;

interface PaymentAdapterInterface
{
    /**
     * Initiate a transaction with the gateway.
     * Returns array with normalized fields:
     * - external_id, redirect_url?, va_number?, va_bank_code?, qr_string?, deeplink_url?, expired_at?, raw_request, raw_response
     */
    public function createTransaction(PaymentTransactionContext $ctx): array;

    /**
     * Verify webhook authenticity. Throws InvalidWebhookSignatureException on failure.
     */
    public function verifyWebhook(array $headers, string $rawBody): void;

    /**
     * Parse webhook payload into normalized event.
     * Returns: ['external_id' => ..., 'status' => 'paid|expired|failed', 'paid_at' => ?, 'gateway_transaction_id' => ?]
     */
    public function parseWebhook(array $payload): array;

    /**
     * Optional: query latest status from gateway (for polling reconciliation).
     */
    public function fetchStatus(string $externalId): array;
}
```

`PaymentTransactionContext` is a value object containing: invoice, method, provider, amount_cents, customer (name/email/phone), callback_url, expiry_minutes, idempotency_key.

---

## API Endpoints

### Public / Authenticated (Parent, Student)

| Method | URI | Role | Description |
|---|---|---|---|
| GET | `/api/v1/payments/methods` | parent, student | List metode aktif untuk sekolah ini |
| POST | `/api/v1/payments/initiate` | parent, student, accountant | Mulai transaksi: `{ invoice_id, payment_method_id, idempotency_key? }` |
| GET | `/api/v1/payments/{reference_no}` | parent, student, accountant | Status & detail transaksi |
| POST | `/api/v1/payments/{reference_no}/cancel` | parent (own) | Batalkan transaksi pending |
| POST | `/api/v1/payments/{reference_no}/upload-proof` | parent (own) | Upload bukti TF (untuk `bank_transfer_manual`) |
| GET | `/payment/return` | public | Landing page setelah redirect dari gateway |

### Webhook (Public, Server-to-Server)

| Method | URI | Auth | Description |
|---|---|---|---|
| POST | `/api/v1/payments/webhook/{provider_slug}` | signature | Webhook dari gateway. `{slug}` resolve ke `payment_providers.slug` |

### Admin

| Method | URI | Role | Description |
|---|---|---|---|
| GET/POST | `/api/v1/admin/payment-providers` | admin | CRUD providers |
| PUT/DELETE | `/api/v1/admin/payment-providers/{id}` | admin | |
| POST | `/api/v1/admin/payment-providers/{id}/test` | admin | Test connection (call gateway sandbox) |
| GET/POST | `/api/v1/admin/payment-methods` | admin | CRUD methods |
| PUT/DELETE | `/api/v1/admin/payment-methods/{id}` | admin | |
| POST | `/api/v1/admin/payment-providers/import-preset` | admin | Autofill from preset JSON (convenience only) |
| POST | `/api/v1/admin/payments/{id}/verify-manual` | admin, accountant | Verifikasi pembayaran manual (bank_transfer_manual) |
| POST | `/api/v1/admin/payments/{id}/refund` | admin | Trigger refund via gateway |

---

## Business Flow

### A. Setup oleh Admin Sekolah

1. Admin buka **Settings → Payment Providers**
2. Klik **+ Add Provider**
3. Pilih **API Format** (dropdown: Redirect Checkout / Virtual Account / E-wallet / QRIS Dynamic / QRIS Static / Bank Transfer / Cash)
4. (Opsional) klik **Load Preset** → pilih template (Midtrans / Xendit / Doku / dll.) → field auto-fill (base URL, header convention). Admin masih bisa edit semua field.
5. Input: `name`, `base_url`, `api_key`, `secret_key`, `merchant_id`, `webhook_secret`, `is_sandbox`, `extra_config` (json)
6. Klik **Test Connection** → backend call gateway sandbox endpoint, validasi credential
7. Save

8. Buka **Payment Methods** → klik **+ Add Method**
9. Pilih provider yang barusan dibuat
10. Input: `code` (e.g., `va_bca`), `display_name`, `logo_url`, `fee_flat`, `fee_percent_bp`, `fee_borne_by` (parent/school), `expiry_minutes`
11. Save → method aktif untuk parent

### B. Pembayaran oleh Parent/Student

1. Parent buka **Tagihan** → list invoice unpaid
2. Klik **Bayar** di salah satu invoice
3. Modal/page tampilkan list `payment_methods` aktif
4. Pilih method → klik **Lanjut Bayar**
5. Backend: `POST /api/v1/payments/initiate`
   - Validate invoice belum paid, amount masih sesuai
   - Resolve adapter dari `provider.api_format`
   - Build context (customer, amount + fee, callback_url)
   - Call adapter `createTransaction()`
   - Save `PaymentTransaction` (status `awaiting_payment`)
6. Response berisi salah satu (tergantung adapter):
   - `redirect_url` → frontend redirect/WebView
   - `va_number` + `va_bank_code` → tampilkan ke user dengan instruksi transfer
   - `qr_string` → render QR code
   - `deeplink_url` → buka aplikasi e-wallet
7. User bayar di gateway/banking app
8. **Webhook** dari gateway → backend update transaction status → create `FeePayment` + update `FeeInvoice.status` → push notification ke parent
9. Frontend polling `GET /api/v1/payments/{reference_no}` setiap 5s sampai status berubah → tampilkan **Berhasil** / **Gagal** / **Expired**

### C. Bank Transfer Manual

1. Parent pilih method `bank_manual`
2. Backend buat `PaymentTransaction` (status `awaiting_payment`) dengan info rekening dari `provider.extra_config`
3. Parent transfer manual ke rekening sekolah
4. Parent klik **Upload Bukti** → upload screenshot/PDF
5. Admin/accountant buka **Pending Manual Payments** → review bukti
6. Klik **Verify** → status jadi `paid` → create `FeePayment`
7. Atau klik **Reject** → status `failed` dengan reason

### D. Cash di Kasir

1. Admin sekolah buka **Record Cash Payment** (existing flow di module 11)
2. Pilih invoice → input amount → method = `cash` → confirm
3. Backend: tetap pakai `FeeService::recordPayment()` lama, **tidak lewat PaymentTransaction**
4. (Opsional) admin bisa cetak receipt PDF

### E. Reconciliation (Daily Cron)

```
ReconcilePendingPaymentsJob (runs every 5 minutes via Scheduler):
  - Get all PaymentTransaction WHERE status='awaiting_payment' AND created_at > now()-24h
  - For each: call adapter.fetchStatus(external_id)
  - If gateway says paid but local says awaiting → trigger handlePaidEvent (idempotent)
  - If expired_at < now() AND still awaiting → mark 'expired'
```

---

## Adapter Implementations (Stub)

### `RedirectCheckoutAdapter`

```php
public function createTransaction(PaymentTransactionContext $ctx): array
{
    $payload = [
        'transaction_details' => [
            'order_id'     => $ctx->referenceNo,
            'gross_amount' => intval($ctx->amountCents / 100), // gateway pakai unit, kita pakai cents
        ],
        'customer_details' => [
            'first_name' => $ctx->customer['name'],
            'email'      => $ctx->customer['email'],
            'phone'      => $ctx->customer['phone'],
        ],
        'callbacks' => ['finish' => $ctx->callbackUrl],
        'expiry'    => ['unit' => 'minutes', 'duration' => $ctx->expiryMinutes],
    ];

    $headers = $ctx->provider->resolveExtraHeaders();
    $auth    = $ctx->provider->resolveAuth(); // returns ['type' => 'basic'|'bearer'|'header', 'value' => '...']

    $response = $this->http->post(
        $ctx->provider->base_url . '/transactions',
        $payload,
        headers: $headers,
        auth: $auth,
    );

    return [
        'external_id'  => $response['order_id'] ?? $ctx->referenceNo,
        'redirect_url' => $response['redirect_url'] ?? null,
        'expired_at'   => now()->addMinutes($ctx->expiryMinutes),
        'raw_request'  => $payload,
        'raw_response' => $response,
    ];
}

public function verifyWebhook(array $headers, string $rawBody): void
{
    $payload = json_decode($rawBody, true);
    $secret  = $this->provider->decrypted('webhook_secret_encrypted');

    // Generic SHA512 signature verification (configurable via extra_config.signature_method)
    $method     = data_get($this->provider->extra_config, 'signature_method', 'sha512');
    $fields     = data_get($this->provider->extra_config, 'signature_fields', ['order_id', 'status_code', 'gross_amount']);
    $signature  = $payload['signature_key']
        ?? $headers['x-signature'][0]
        ?? throw new InvalidWebhookSignatureException('Missing signature');

    $values = array_map(fn($f) => $payload[$f] ?? '', $fields);
    $expected = hash($method, implode('', $values) . $secret);

    if (!hash_equals($expected, $signature)) {
        throw new InvalidWebhookSignatureException('Signature mismatch');
    }
}
```

### `VirtualAccountAdapter`

Returns `va_number`, `va_bank_code`, `expired_at`. No redirect.

### `EwalletDeeplinkAdapter`

Returns `deeplink_url` (mobile) and/or `qr_url` (web fallback).

### `QrisDynamicAdapter`

Returns `qr_string` (EMVCo format) — frontend render dengan library QR.

### `BankTransferManualAdapter`

No HTTP call. Generates `va_number` from `provider.extra_config.bank_account` template. User upload bukti via separate endpoint.

### `CashAdapter`

Direct call to `FeeService::recordPayment()`, no transaction record needed.

---

## Encryption

- `api_key_encrypted`, `secret_key_encrypted`, `merchant_id_encrypted`, `webhook_secret_encrypted` use Laravel's `Crypt::encryptString()` / `decryptString()`.
- Accessor on `PaymentProvider` model returns decrypted value, never expose in API responses.
- API resource always returns `'***' . substr($key, -4)` masked.

---

## Idempotency

- Client kirim `Idempotency-Key` header (UUID) di `/payments/initiate`
- Server cache key di Redis 24h: `payment:idempotency:{school_id}:{key}` → `transaction_id`
- Hit kedua dengan key sama → return existing transaction (no double-create)

---

## Webhook Idempotency & Replay Protection

- `payment_webhook_logs.payload->{external_id}` lookup
- Jika `payment_transactions.status` already `paid` → log "duplicate", return 200 OK (gateway expects 200)
- Verify timestamp (if gateway provides) within 5min window untuk prevent replay

---

## Optional Preset Templates

Lokasi: `storage/app/payment-presets/*.json`

Contoh: `storage/app/payment-presets/midtrans-snap.json`

```json
{
  "label": "Midtrans Snap (Sandbox)",
  "api_format": "redirect_checkout",
  "base_url": "https://app.sandbox.midtrans.com/snap/v1",
  "auth_type": "basic_username_only",
  "signature_method": "sha512",
  "signature_fields": ["order_id", "status_code", "gross_amount"],
  "extra_headers": {
    "Accept": "application/json",
    "Content-Type": "application/json"
  },
  "instructions": "Server Key dari Midtrans dashboard → masuk ke field api_key. Webhook URL: {{callback_url}}"
}
```

**WAJIB:** Code di runtime TIDAK PERNAH `include` / `read` preset files. Hanya admin UI baca untuk autofill.

---

## Acceptance Criteria

- [ ] Admin bisa tambah provider format apapun tanpa code change
- [ ] Tidak ada nama vendor (Midtrans/Xendit/dll.) di class atau code
- [ ] API key/secret ter-encrypt at rest, masked di API response
- [ ] Webhook signature verified, signature mismatch → 401 + log
- [ ] Idempotency: duplicate `Idempotency-Key` returns same transaction
- [ ] Webhook duplicate → status tidak berubah lagi (idempotent)
- [ ] Pending transaction expired_at lewat → auto-mark expired via cron
- [ ] Bank transfer manual: parent upload bukti, admin verify → status paid
- [ ] Reconciliation cron: pending > X min → query gateway → sync status
- [ ] Receipt PDF auto-generated saat status paid
- [ ] Notification ke parent saat status berubah (paid/expired/failed)

---

## Tests to Write

```
tests/Feature/Payment/
  PaymentProviderCrudTest.php
  PaymentMethodCrudTest.php
  InitiateRedirectCheckoutTest.php
  InitiateVirtualAccountTest.php
  InitiateQrisDynamicTest.php
  WebhookSignatureValidTest.php
  WebhookSignatureInvalidTest.php
  WebhookIdempotencyTest.php
  IdempotencyKeyTest.php
  ManualBankTransferFlowTest.php
  ReconciliationCronTest.php
  EncryptionAtRestTest.php
  CrossSchoolIsolationTest.php

tests/Unit/Payment/
  RedirectCheckoutAdapterTest.php
  VirtualAccountAdapterTest.php
  EwalletDeeplinkAdapterTest.php
  QrisDynamicAdapterTest.php
  BankTransferManualAdapterTest.php
  PaymentAdapterFactoryTest.php
```

---

## Migration from existing `MidtransService.php`

`app/Services/Payment/MidtransService.php` **harus dihapus** karena melanggar global rule. Logikanya pindah ke `RedirectCheckoutAdapter` (signature verification, payload format) — tapi tanpa hardcode URL/key. Admin sekolah tinggal Add Provider → pilih Redirect Checkout → Load Preset "Midtrans Snap" → input Server Key sendiri.

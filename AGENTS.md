# AGENTS.md — eSchool Laravel Backend

Satu sistem dengan Flutter (`D:\project flutter\eschool`, repo `linducip2208/schoolflutter`).
Repo ini: `linducip2208/sekolah`, branch `main`. JANGAN satukan histori git.

## Stack

Laravel 13, PHP 8.3, MySQL 8 (prod) / sqlite (test), Sanctum, Spatie Permission,
Reverb (broadcast), Redis queue/cache (prod) / database (dev).

## Arsitektur

Controller (Api + Web) → Service (bisnis) → Model. Tanpa Repositories
(keputusan sadar; logika di Service). Validasi di FormRequest / inline
`validate()`. Response JSON langsung (lihat pola per controller).
Global scope `SchoolScope` via `SchoolModel` — SEMUA query tenant otomatis
ter-scope; route-model binding ikut scope.

## Multi-tenancy

Shared DB, `school_id` di semua tabel tenant. Middleware `school.access`
+ `subscription.active` pada grup `v1` auth. IDOR: cek participant/
kepemilikan di controller (contoh: Chat, ParentPortal, Fee).

## Auth

Sanctum token (device_name=mobile). Login 2FA, throttle `login/2fa/
password-reset`. Token immortal default; `SANCTUM_EXPIRATION` (menit,
env opt-in). FCM: `POST /devices/register {token, platform, device_name}`,
unregister saat logout.

## Timezone

Default `Asia/Jakarta` (`APP_TIMEZONE`). Jangan pakai asumsi UTC untuk
tanggal bisnis. Uang = integer minor unit (IDR decimals=0 → rupiah utuh).

## API contract

Prefix `/api/v1`. Flutter hanya GET+POST. Upload: `POST /uploads`
(purpose: chat|assignment|ppdb|medical|payment_proof), unduh privat via
`GET /uploads/file?path=`. Chat realtime: event `MessageSent` →
`private-conversation.{id}` event `message.new`. Idempotency: header
`Idempotency-Key` (payments, chat), upsert natural (attendance, submission).

## Perintah

```bash
php artisan route:list --path=api/v1
php artisan test tests/Feature/<Dir>
vendor/bin/pint --test   # (bila tersedia)
```

## Security rules

- Jangan commit `.env`, keystore, `*.jks`, `key.properties`.
- Upload: MIME+size, ekstensi dari MIME, nama acak, path school-scoped.
- Rate limit endpoint publik/sensitif (lihat `routes/api.php`).
- Jangan lemahkan auth agar test hijau; test regresi di
  `tests/Feature/Security/`, `tests/Feature/Files/`.

## Git

Commit pesan jelas per area (security, contract, sync, docs). Jangan
push tanpa diminta. Jangan sentuh repo Flutter dari sini.

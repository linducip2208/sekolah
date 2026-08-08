# Module 00 — License Management (whitelabel.co.id)

## Depends On
Nothing. Dieksekusi **PERTAMA** sebelum Module 01.
Proteksi lisensi — aplikasi tidak bisa berjalan tanpa lisensi valid.

> Sumber kebenaran API: lihat `LICENSE_API.md` di root project.

---

## Dua Model Bisnis — Pilih Satu

Sikad Pro mendukung dua cara monetisasi yang berbeda. Implementasi license-nya berbeda.

---

### Model 1 — SaaS Platform (Anda host, sekolah bayar subscription)

**Anda** adalah pemilik platform. Anda beli 1 license dari whitelabel.co.id.
Sekolah-sekolah mendaftar ke Anda dan bayar subscription bulanan (module 13).

```
Anda beli 1 license:
  domain: sikadpro.app
  LICENSE_KEY=XXXXX-XXXXX-XXXXX-XXXXX  ← di .env server sikadpro.app

Sekolah berjalan sebagai subdomain di server Anda:
  smkn1.sikadpro.app  ─┐
  sma2.sikadpro.app    ├── 1 server Anda = 1 license check/hari
  sd3.sikadpro.app    ─┘

Aliran uang:
  Sekolah → bayar subscription → ke Anda
  Anda    → bayar license 1x   → ke whitelabel.co.id
```

Artisan command (Anda jalankan 1x saat install platform):
```bash
php artisan license:activate XXXXX-XXXXX-XXXXX-XXXXX sikadpro.app
```

---

### Model 2 — Source Code Sale (jual source code via whitelabel.co.id)

Anda menjual **source code** Sikad Pro. Setiap customer mendapat `activation_key` sendiri
dan deploy di server mereka sendiri. whitelabel.co.id mengelola tiap license secara terpisah.

```
Customer A beli source code:              Customer B beli source code:
  LICENSE_KEY=AAAAA-AAAAA-AAAAA-AAAAA      LICENSE_KEY=BBBBB-BBBBB-BBBBB-BBBBB
  APP_URL=https://sekolaha.com              APP_URL=https://sekolahb.sch.id
  → deploy server sendiri                   → deploy server sendiri

Customer A jalankan:                      Customer B jalankan:
  php artisan license:activate              php artisan license:activate
    AAAAA-AAAAA sekolaha.com                  BBBBB-BBBBB sekolahb.sch.id

Aliran uang:
  Customer A → bayar license → ke whitelabel.co.id (bukan ke Anda)
  Customer B → bayar license → ke whitelabel.co.id (bukan ke Anda)
  Anda       → terima komisi reseller dari whitelabel.co.id
```

---

### Perbandingan

| | Model 1 (SaaS Platform) | Model 2 (Source Code Sale) |
|---|---|---|
| Yang beli license | Anda (1x saja) | Tiap customer masing-masing |
| `APP_URL` di license | `sikadpro.app` | Domain customer (mis. `sekolahku.com`) |
| Server | 1 server milik Anda | Tiap customer server sendiri |
| Modul subscription (13) | **Dipakai** | Tidak dipakai |
| Jumlah license | 1 | N (1 per customer) |

---

## SaaS Multi-Domain: License Taruh di Mana? (Model 1)

**License HANYA ada di satu tempat: server platform Sikad Pro Anda (sikadpro.app).**

Bukan di tiap sekolah/subdomain. Semua subdomain sekolah (`smkn1.sikadpro.app`, `sma2.sikadpro.app`, dll)
berjalan di **satu server yang sama** dengan **satu license**.

```
whitelabel.co.id
  └── License Key: XXXXX-XXXXX
      Domain terdaftar: sikadpro.app   ← domain platform Anda (bukan subdomain sekolah)

Server Sikad Pro (satu server, satu .env)
  ├── smkn1.sikadpro.app   ─┐
  ├── sma2.sikadpro.app     ├── semua pakai 1 server = 1 license check per hari
  ├── sd3.sikadpro.app     ─┘
  └── admin.sikadpro.app

.env di server ini:
  LICENSE_KEY=XXXXX-XXXXX-XXXXX-XXXXX
  APP_URL=https://sikadpro.app          ← domain utama platform (bukan subdomain)
```

### Kenapa begini?

- Wildcard subdomain `*.sikadpro.app` semuanya pointing ke IP server yang sama
- Laravel berjalan di satu proses — satu license check berlaku untuk semua subdomain
- Yang "beli license" adalah **Anda sebagai pemilik platform**, bukan tiap sekolah
- Tiap sekolah membayar ke **Anda** lewat subscription SaaS (module 13), bukan ke whitelabel.co.id

### Bedanya dengan Instalasi Single-School

```
Single-School Install (customer beli source code):
  Customer A: domain tokoa.com    → beli license 1 domain ke whitelabel.co.id
  Customer B: domain tokob.com    → beli license 1 domain ke whitelabel.co.id

SaaS Platform (Anda yang jual akses SaaS):
  Anda:       domain sikadpro.app  → beli 1 license ke whitelabel.co.id
  Sekolah A:  smkn1.sikadpro.app  → bayar subscription ke Anda (module 13)
  Sekolah B:  sma2.sikadpro.app   → bayar subscription ke Anda (module 13)
  ← Sekolah tidak perlu beli license ke whitelabel.co.id
```

### Jika Platform Sikad Pro Di-deploy di Multi-Server (Load Balancer)

Jika ke depan ada lebih dari 1 server (load balancing):

```
Load Balancer → Server 1 (sikadpro.app) → 1 license check
              → Server 2 (sikadpro.app) → cache Redis shared
```

Pakai **Redis shared cache** untuk cache license (`'lic_' . date('Ymd')`).
Dengan begitu, validasi API ke whitelabel.co.id tetap 1x per hari meskipun ada banyak server.

```php
// Pastikan CACHE_DRIVER=redis dan semua server pakai Redis yang sama
// LicenseChecker::validate() memakai cache() yang sudah terhubung ke Redis shared
```

Jika butuh lebih dari 3 server, pertimbangkan `extended` license (3 domain) atau
hubungi whitelabel.co.id untuk enterprise license.

---

## License API Reference

```
Base URL   : https://whitelabel.co.id/api
Auth       : Tidak perlu Bearer token — auth via activation_key + domain
Rate Limit : 60 req/menit per IP
Format     : Content-Type: application/json
```

### POST `/api/license/validate`
Dipanggil setiap boot. Verifikasi license aktif + domain terdaftar.

**Request:**
```json
{
  "activation_key": "XXXXX-XXXXX-XXXXX-XXXXX",
  "domain": "yourdomain.com"
}
```

**Response 200:**
```json
{
  "valid": true,
  "license_key": "XXXXX-XXXXX-XXXXX-XXXXX",
  "product": "Sikad Pro",
  "version": "1.0.0",
  "domain": "yourdomain.com",
  "type": "regular",
  "support": true,
  "checksum": "abc123...",
  "expires_at": "2027-04-24"
}
```

**Error Responses:**

| Status | Body                   |
|--------|------------------------|
| `404`  | License not found.     |
| `403`  | License is revoked.    |
| `403`  | Domain not authorized. |

---

### POST `/api/license/activate`
Dipanggil **sekali** saat instalasi pertama. Mendaftarkan domain ke license.
- `regular` license = max **1 domain**
- `extended` license = max **3 domain**

**Request:**
```json
{
  "activation_key": "XXXXX-XXXXX-XXXXX-XXXXX",
  "domain": "yourdomain.com"
}
```

**Response 200:**
```json
{
  "activated": true,
  "domain": "yourdomain.com",
  "checksum": "abc123...",
  "message": "Domain activated successfully."
}
```

**Error 422:** `"Maximum domain limit reached (1)."`

---

### POST `/api/license/revoke`
Dipanggil saat pindah domain/server. Membebaskan slot domain.

**Request:**
```json
{
  "activation_key": "XXXXX-XXXXX-XXXXX-XXXXX",
  "domain": "yourdomain.com"
}
```

**Response 200:**
```json
{
  "revoked": true,
  "domain": "yourdomain.com",
  "message": "Domain revoked successfully."
}
```

---

### GET `/api/version/check`
Cek apakah versi terbaru tersedia.

**Query Parameters:**

| Param     | Keterangan                        |
|-----------|-----------------------------------|
| `product` | Slug produk (misal: `sikadpro`)    |
| `current` | Versi yang terinstall saat ini    |

**Contoh:**
```
GET /api/version/check?product=sikadpro&current=1.0.0
```

**Response 200:**
```json
{
  "has_update": true,
  "latest_version": "2.1.0",
  "download_url": "https://..."
}
```

---

## Checksum — Offline Verification

Setelah aktivasi, simpan checksum untuk fallback saat API tidak terjangkau.

**Generate checksum (di sisi server whitelabel.co.id):**
```php
hash_hmac('sha256', $activation_key . '|' . $domain, APP_KEY)
```

**Verifikasi di source code Sikad Pro:**
```php
$valid = hash_equals(
    hash_hmac('sha256', $key . '|' . $domain, env('LICENSE_SECRET')),
    $storedChecksum
);
```

> `LICENSE_SECRET` = `APP_KEY` dari platform whitelabel.co.id.
> Diberikan ke customer sebagai env var.

---

## Files to Create

```
app/
  Services/
    LicenseChecker.php              ← core logic (static class)
  Http/
    Controllers/Api/LicenseController.php
    Middleware/
      EnsureValidLicense.php
  Console/
    Commands/
      LicenseActivateCommand.php    ← php artisan license:activate XXXXX domain.com
      LicenseRevokeCommand.php      ← php artisan license:revoke XXXXX domain.com
      LicenseStatusCommand.php      ← php artisan license:status

config/
  license.php

storage/
  license.sig                       ← checksum offline (gitignored!)

resources/views/errors/
  license.blade.php                 ← halaman error license (web request)
```

---

## config/license.php

```php
return [
    'key'     => env('LICENSE_KEY'),
    'secret'  => env('LICENSE_SECRET'),           // APP_KEY dari whitelabel.co.id
    'api'     => env('LICENSE_API_URL', 'https://whitelabel.co.id/api/license'),
    'enabled' => env('LICENSE_CHECK', true),      // false untuk dev/testing
    'product' => env('LICENSE_PRODUCT', 'sikadpro'),
    'version' => '1.0.0',                         // versi app ini
];
```

---

## `app/Services/LicenseChecker.php`

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LicenseChecker
{
    private const API = 'https://whitelabel.co.id/api/license';

    public static function validate(): bool
    {
        if (!config('license.enabled')) return true;

        $key    = config('license.key');
        $domain = parse_url(config('app.url'), PHP_URL_HOST);

        return cache()->remember('lic_' . date('Ymd'), now()->addHours(6), function () use ($key, $domain) {
            try {
                $res = Http::timeout(5)->post(self::API . '/validate', [
                    'activation_key' => $key,
                    'domain'         => $domain,
                ]);

                if ($res->successful() && $res->json('valid') === true) {
                    // Simpan checksum baru untuk offline fallback
                    $checksum = $res->json('checksum');
                    if ($checksum) {
                        file_put_contents(storage_path('license.sig'), $checksum);
                    }
                    return true;
                }

                return false;

            } catch (\Throwable) {
                return self::verifyChecksum($key, $domain); // offline fallback
            }
        });
    }

    public static function activate(string $key = null, string $domain = null): array
    {
        $key    = $key    ?? config('license.key');
        $domain = $domain ?? parse_url(config('app.url'), PHP_URL_HOST);

        $res = Http::timeout(10)->post(self::API . '/activate', [
            'activation_key' => $key,
            'domain'         => $domain,
        ]);

        if ($res->successful() && ($res->json('activated') === true)) {
            $checksum = $res->json('checksum');
            file_put_contents(storage_path('license.sig'), $checksum);
            cache()->forget('lic_' . date('Ymd'));
        }

        return $res->json() ?? [];
    }

    public static function revoke(string $key = null, string $domain = null): array
    {
        $key    = $key    ?? config('license.key');
        $domain = $domain ?? parse_url(config('app.url'), PHP_URL_HOST);

        $res = Http::timeout(10)->post(self::API . '/revoke', [
            'activation_key' => $key,
            'domain'         => $domain,
        ]);

        if ($res->successful()) {
            @unlink(storage_path('license.sig'));
            cache()->forget('lic_' . date('Ymd'));
        }

        return $res->json() ?? [];
    }

    public static function checkVersion(): array
    {
        $res = Http::timeout(5)->get('https://whitelabel.co.id/api/version/check', [
            'product' => config('license.product'),
            'current' => config('license.version'),
        ]);
        return $res->json() ?? [];
    }

    private static function verifyChecksum(string $key, string $domain): bool
    {
        $sigFile = storage_path('license.sig');
        if (!file_exists($sigFile)) return false;

        $stored = trim(file_get_contents($sigFile));
        return $stored && hash_equals(
            hash_hmac('sha256', $key . '|' . $domain, config('license.secret')),
            $stored
        );
    }
}
```

---

## `app/Providers/AppServiceProvider.php` — `boot()`

```php
public function boot(): void
{
    if (app()->isProduction() && !app()->runningInConsole()) {
        if (!\App\Services\LicenseChecker::validate()) {
            abort(403, 'Invalid license. Activate at https://whitelabel.co.id');
        }
    }
}
```

---

## `app/Http/Middleware/EnsureValidLicense.php`

```php
<?php

namespace App\Http\Middleware;

use App\Services\LicenseChecker;
use Illuminate\Http\Request;

class EnsureValidLicense
{
    public function handle(Request $request, \Closure $next): mixed
    {
        if (!LicenseChecker::validate()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message'      => 'License tidak valid atau tidak aktif.',
                    'activate_url' => 'https://whitelabel.co.id',
                ], 403);
            }
            return response()->view('errors.license', [], 403);
        }

        return $next($request);
    }
}
```

---

## Artisan Commands

```php
// php artisan license:activate XXXXX-XXXXX domain.com
class LicenseActivateCommand extends Command
{
    protected $signature   = 'license:activate {key} {domain}';
    protected $description = 'Activate license for this domain';

    public function handle(): int
    {
        $result = LicenseChecker::activate($this->argument('key'), $this->argument('domain'));

        if ($result['activated'] ?? false) {
            $this->info("✅ License activated for: {$this->argument('domain')}");
            $this->line("   Checksum saved → storage/license.sig");
            return self::SUCCESS;
        }

        $this->error("❌ Failed: " . ($result['message'] ?? 'Unknown error'));
        return self::FAILURE;
    }
}

// php artisan license:revoke XXXXX-XXXXX domain.com
class LicenseRevokeCommand extends Command
{
    protected $signature   = 'license:revoke {key} {domain}';
    protected $description = 'Revoke domain from license';

    public function handle(): int
    {
        $result = LicenseChecker::revoke($this->argument('key'), $this->argument('domain'));

        if ($result['revoked'] ?? false) {
            $this->info("✅ Domain revoked: {$this->argument('domain')}");
            return self::SUCCESS;
        }

        $this->error("❌ Failed: " . ($result['message'] ?? 'Unknown error'));
        return self::FAILURE;
    }
}

// php artisan license:status
class LicenseStatusCommand extends Command
{
    protected $signature   = 'license:status';
    protected $description = 'Check current license status';

    public function handle(): int
    {
        $isValid = LicenseChecker::validate();
        $sigFile = storage_path('license.sig');

        $this->table(['Key', 'Value'], [
            ['License Key',   config('license.key') ?? '(not set)'],
            ['Domain',        parse_url(config('app.url'), PHP_URL_HOST)],
            ['Status',        $isValid ? '✅ Valid' : '❌ Invalid/Expired'],
            ['Checksum File', file_exists($sigFile) ? '✅ Present' : '❌ Missing'],
            ['Cache (daily)', cache()->has('lic_' . date('Ymd')) ? '✅ Cached' : '⚡ Will re-validate'],
        ]);

        return $isValid ? self::SUCCESS : self::FAILURE;
    }
}
```

---

## Route Registration (API endpoint opsional untuk UI aktivasi)

```php
// routes/api.php — TANPA middleware license (agar bisa aktivasi)
Route::prefix('v1')->group(function () {
    Route::post('/license/status',   [LicenseController::class, 'status']);
    Route::post('/license/activate', [LicenseController::class, 'activate']);
});

// Semua route lain DENGAN middleware license:
Route::middleware(['api', 'license'])->prefix('v1')->group(function () {
    // ... semua endpoint aplikasi
});
```

---

## Customer `.env` Setup

```env
# Wajib diisi customer setelah beli lisensi
LICENSE_KEY=XXXXX-XXXXX-XXXXX-XXXXX
LICENSE_SECRET=secret-dari-whitelabel
APP_URL=https://yourdomain.com

# Opsional
LICENSE_CHECK=true           # set false untuk dev/staging lokal
LICENSE_PRODUCT=sikadpro
LICENSE_API_URL=https://whitelabel.co.id/api/license
```

---

## .gitignore

```
# Tambahkan — jangan commit checksum ke repo
storage/license.sig
```

---

## Production URL Notes

| Environment                          | Validate URL                                     |
|--------------------------------------|--------------------------------------------------|
| Production (Nginx, doc root `/public`) | `https://yourdomain.com/api/license/validate`  |
| Local (`php artisan serve`)            | `http://127.0.0.1:8000/api/license/validate`   |

---

## Acceptance Criteria

- [ ] `php artisan license:activate KEY domain.com` berhasil dan menyimpan `storage/license.sig`
- [ ] `php artisan license:status` menampilkan info akurat
- [ ] License valid di-cache harian (`'lic_' . date('Ymd')`) — tidak hit API tiap request
- [ ] Jika API timeout, fallback ke checksum offline tetap valid
- [ ] Jika checksum tidak ada DAN API gagal → `abort(403)`
- [ ] Middleware tidak berlaku saat `LICENSE_CHECK=false`
- [ ] Web request dengan license invalid → tampil `errors.license` Blade view
- [ ] API request dengan license invalid → JSON `403`

## Tests to Write

```
tests/Feature/License/
  ValidLicenseTest.php          ← Http::fake() → valid response
  InvalidLicenseTest.php        ← Http::fake() → 403 response
  OfflineChecksumTest.php       ← simulate timeout, checksum ada → valid
  OfflineNoChecksumTest.php     ← simulate timeout, no checksum → 403
  ActivateCommandTest.php       ← artisan license:activate
  RevokeDomainTest.php          ← artisan license:revoke
  DailyCacheTest.php            ← cache hit, API hanya dipanggil sekali per hari
```

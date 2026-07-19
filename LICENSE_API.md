# License API — whitelabel.co.id

> **Base URL:** `https://whitelabel.co.id/api`  
> **Auth:** No Bearer token — authenticated via `activation_key` + `domain`  
> **Rate Limit:** 60 req/min per IP  
> **Content-Type:** `application/json`

---

## Dua Model Bisnis eSchool

eSchool mendukung **dua model bisnis** yang berbeda. Pilih satu sesuai kebutuhan:

---

### Model 1 — SaaS Platform (Anda jual akses, bukan source code)

Anda adalah **pemilik platform**. Anda membeli 1 license dari whitelabel.co.id dan meng-host
aplikasi di server Anda sendiri. Sekolah-sekolah membayar subscription bulanan ke **Anda**,
bukan ke whitelabel.co.id.

```
whitelabel.co.id  →  Anda beli 1 license untuk domain: eschool.app

Server Anda (1 server, 1 .env):
  LICENSE_KEY=XXXXX-XXXXX-XXXXX-XXXXX
  APP_URL=https://eschool.app            ← domain platform Anda

Semua sekolah jalan di server yang sama:
  smkn1.eschool.app  ─┐
  sma2.eschool.app    ├── wildcard subdomain → 1 server → 1 license check/hari
  sd3.eschool.app    ─┘

Sekolah bayar subscription → ke Anda (via module 13)
Anda bayar license          → ke whitelabel.co.id (1x saja)
```

**Artisan command (Anda jalankan 1x saat install):**
```bash
php artisan license:activate XXXXX-XXXXX-XXXXX-XXXXX eschool.app
```

---

### Model 2 — Source Code Sale (Anda jual source code via whitelabel.co.id)

Anda menjual **source code** eSchool melalui marketplace whitelabel.co.id.
Setiap customer yang membeli mendapatkan `activation_key` mereka sendiri
dan men-deploy di server mereka sendiri.

```
whitelabel.co.id  →  Customer A beli source code → dapat activation_key A
                  →  Customer B beli source code → dapat activation_key B

Customer A (server sendiri):            Customer B (server sendiri):
  LICENSE_KEY=AAAAA-AAAAA-AAAAA-AAAAA    LICENSE_KEY=BBBBB-BBBBB-BBBBB-BBBBB
  APP_URL=https://sekolaha.com            APP_URL=https://sekolahb.sch.id
  → 1 license = 1 domain (regular)        → 1 license = 1 domain (regular)

Customer bayar license → ke whitelabel.co.id
Customer jalankan sendiri:
  php artisan license:activate AAAAA-AAAAA sekolaha.com
```

**Artisan command (customer jalankan saat install):**
```bash
php artisan license:activate AAAXX-XXXXX-XXXXX-XXXXX sekolaha.com
```

---

### Perbandingan Singkat

| Aspek | Model 1 (SaaS Platform) | Model 2 (Source Code Sale) |
|---|---|---|
| Yang beli license | Anda (1x) | Setiap customer |
| Domain di license | `eschool.app` (platform Anda) | Domain customer masing-masing |
| Server | 1 server milik Anda | Tiap customer server sendiri |
| Sekolah bayar ke | Anda (subscription) | whitelabel.co.id (license) |
| Jumlah license di whitelabel | 1 | N (1 per customer) |
| Modul subscription (13) | Dipakai | Tidak dipakai |

---

## Endpoints

### POST `/api/license/validate`

Validates that a license is active and the domain is registered. Call on every app boot.

**Request**
```json
{
  "activation_key": "XXXXX-XXXXX-XXXXX-XXXXX",
  "domain": "yourdomain.com"
}
```

**Response 200**
```json
{
  "valid": true,
  "license_key": "XXXXX-XXXXX-XXXXX-XXXXX",
  "product": "Nama Produk",
  "version": "1.0.0",
  "domain": "yourdomain.com",
  "type": "regular",
  "support": true,
  "checksum": "abc123...",
  "expires_at": "2027-04-24"
}
```

**Error Responses**

| Status | Body |
|--------|------|
| `404` | `License not found.` |
| `403` | `License is revoked.` |
| `403` | `Domain not authorized.` |

---

### POST `/api/license/activate`

Binds a domain to a license. Call once on first install.  
- Regular license → max **1 domain**  
- Extended license → max **3 domains**

**Request**
```json
{
  "activation_key": "XXXXX-XXXXX-XXXXX-XXXXX",
  "domain": "yourdomain.com"
}
```

**Response 200**
```json
{
  "activated": true,
  "domain": "yourdomain.com",
  "checksum": "abc123...",
  "message": "Domain activated successfully."
}
```

**Error Responses**

| Status | Body |
|--------|------|
| `422` | `Maximum domain limit reached (1).` |

---

### POST `/api/license/revoke`

Removes a domain from a license, freeing the slot for another domain. Use when customer migrates servers.

**Request**
```json
{
  "activation_key": "XXXXX-XXXXX-XXXXX-XXXXX",
  "domain": "yourdomain.com"
}
```

**Response 200**
```json
{
  "revoked": true,
  "domain": "yourdomain.com",
  "message": "Domain revoked successfully."
}
```

---

### GET `/api/version/check`

Checks whether a newer version is available for a given product.

**Query Parameters**

| Param | Description |
|-------|-------------|
| `product` | Product slug |
| `current` | Currently installed version (semver) |

**Example**
```
GET /api/version/check?product=eschool&current=1.0.0
```

**Response 200**
```json
{
  "has_update": true,
  "latest_version": "2.1.0",
  "download_url": "https://..."
}
```

---

## Checksum (Offline Verification)

After activation, persist the checksum for offline fallback when the API is unreachable.

**Server-side (whitelabel platform) — generate checksum:**
```php
hash_hmac('sha256', $activation_key . '|' . $domain, APP_KEY)
```

**Client-side (in sold source code) — verify checksum:**
```php
$valid = hash_equals(
    hash_hmac('sha256', $key . '|' . $domain, env('LICENSE_SECRET')),
    $storedChecksum
);
```

> `LICENSE_SECRET` = the platform's `APP_KEY` from `.env`.  
> Embed in sold source code as a hardcoded constant or env var.

---

## Laravel Integration

### `config/license.php`

```php
return [
    'key'     => env('LICENSE_KEY'),
    'secret'  => env('LICENSE_SECRET'),           // APP_KEY dari whitelabel.co.id
    'api'     => env('LICENSE_API_URL', 'https://whitelabel.co.id/api/license'),
    'enabled' => env('LICENSE_CHECK', true),      // false untuk dev/testing
    'product' => env('LICENSE_PRODUCT', 'eschool'),
    'version' => '1.0.0',
];
```

### `app/Services/LicenseChecker.php`

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
            file_put_contents(storage_path('license.sig'), $res->json('checksum'));
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
        $stored = @file_get_contents(storage_path('license.sig'));

        return $stored && hash_equals(
            hash_hmac('sha256', $key . '|' . $domain, config('license.secret')),
            trim($stored)
        );
    }
}
```

### `app/Providers/AppServiceProvider.php` — `boot()` method

```php
if (app()->isProduction() && !app()->runningInConsole()) {
    if (!\App\Services\LicenseChecker::validate()) {
        abort(403, 'Invalid license. Activate at https://whitelabel.co.id');
    }
}
```

### Artisan Commands

```bash
# Aktivasi domain saat install pertama
php artisan license:activate XXXXX-XXXXX-XXXXX-XXXXX yourdomain.com

# Cek status license
php artisan license:status

# Cabut domain (pindah server)
php artisan license:revoke XXXXX-XXXXX-XXXXX-XXXXX yourdomain.com
```

### `.env` — Model 1: SaaS Platform

```env
# .env di server platform Anda (eschool.app)
LICENSE_KEY=XXXXX-XXXXX-XXXXX-XXXXX
LICENSE_SECRET=secret-dari-whitelabel
APP_URL=https://eschool.app            # domain platform, bukan subdomain sekolah

LICENSE_CHECK=true
LICENSE_PRODUCT=eschool
```

### `.env` — Model 2: Source Code Sale (di server customer)

```env
# .env di server customer (sekolah mereka sendiri)
LICENSE_KEY=CCCCC-CCCCC-CCCCC-CCCCC   # activation_key milik customer
LICENSE_SECRET=secret-dari-whitelabel
APP_URL=https://sekolahku.sch.id       # domain sekolah customer

LICENSE_CHECK=true
LICENSE_PRODUCT=eschool
```

### `.gitignore`

```
# Jangan commit checksum ke repo
storage/license.sig
```

---

## Production URL Notes

| Environment | Validate URL |
|-------------|-------------|
| Production (Nginx/Apache, doc root → `/public`) | `https://yourdomain.com/api/license/validate` |
| Local (`php artisan serve`) | `http://127.0.0.1:8000/api/license/validate` |

---

## Quick Reference

| Action | Method | Endpoint |
|--------|--------|----------|
| Validate license | `POST` | `/api/license/validate` |
| Activate domain | `POST` | `/api/license/activate` |
| Revoke domain | `POST` | `/api/license/revoke` |
| Check for update | `GET` | `/api/version/check` |

# Module 03b — School Branding & Whitelabel Settings

## Depends On
- Module 01 (Multi-tenant Foundation)
- Module 03 (School Setup — extends `schools.settings`)

## Why This Module Exists

Setiap sekolah harus bisa **whitelabel** UI mereka — logo, warna, nama tampilan, favicon, splash screen mobile, header email — **tanpa code change**. Admin sekolah ubah dari panel sendiri, langsung berlaku ke web panel + mobile app + email + receipt PDF.

---

## What Admin Can Customize

### 1. Identitas Sekolah (Web + Mobile + PDF)
- **Display name** (nama yang tampil di header, beda dari `schools.name` legal)
- **Tagline** (1-line, tampil di login page)
- **Logo primary** (PNG/SVG transparent, tampil di header — light bg)
- **Logo secondary** (untuk dark bg / footer)
- **Logo monochrome** (untuk PDF receipt, watermark)
- **Favicon** (32x32 ICO/PNG)

### 2. Color Palette
- **Primary color** (CTA, header bar) — hex
- **Secondary color** (accents, links)
- **Success / warning / danger** (overrides default Tailwind palette)
- **Background mode** (light / dark / auto)

### 3. Login Page
- **Login background image** (full-bleed cover image)
- **Login welcome text** (multi-language: id, en)
- **Show school motto** (boolean)

### 4. Mobile App
- **Splash screen logo** (1024x1024 transparent PNG)
- **Splash background color**
- **App display name** (yang tampil di OS launcher — fetched on app boot)

### 5. Email & Receipt PDF
- **Email header logo**
- **Email footer text** (kontak, alamat, copyright)
- **Receipt header layout** (preset: simple / formal / modern)
- **Watermark on PDF** (boolean — pakai `logo_monochrome`)

### 6. Domain & Identity
- **Custom domain** (read-only display — manage by super admin)
- **Subdomain** (read-only)
- **School type label** (e.g., "SMK", "Pesantren", "Universitas") — tampil di breadcrumb
- **Academic year display format** (e.g., "2024/2025" vs "TA 2024-2025")

### 7. Notification Branding
- **FCM notification icon** (small icon, white silhouette)
- **FCM notification color** (LED color hint Android)
- **WhatsApp message header** (kalau pakai WA blast)

---

## Database Schema

Karena branding adalah **per-sekolah extension** dari `schools`, dan sebagian besar field optional + sering di-update bersamaan, kita pakai **dedicated table** `school_branding` dengan eager-loaded relationship.

```php
Schema::create('school_branding', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->unique()->constrained()->cascadeOnDelete();

    // Identity
    $table->string('display_name')->nullable();
    $table->string('tagline')->nullable();
    $table->string('school_type_label')->nullable();
    $table->string('academic_year_format')->default('Y/Y+1');

    // Logos (S3 paths)
    $table->string('logo_primary_path')->nullable();
    $table->string('logo_secondary_path')->nullable();
    $table->string('logo_monochrome_path')->nullable();
    $table->string('favicon_path')->nullable();

    // Colors (hex strings, e.g. "#1E40AF")
    $table->string('color_primary', 9)->default('#2563EB');
    $table->string('color_secondary', 9)->default('#64748B');
    $table->string('color_success', 9)->default('#16A34A');
    $table->string('color_warning', 9)->default('#EAB308');
    $table->string('color_danger', 9)->default('#DC2626');
    $table->enum('background_mode', ['light', 'dark', 'auto'])->default('light');

    // Login page
    $table->string('login_background_path')->nullable();
    $table->json('login_welcome_text')->nullable(); // {id: "...", en: "..."}
    $table->boolean('login_show_motto')->default(true);

    // Mobile
    $table->string('mobile_splash_logo_path')->nullable();
    $table->string('mobile_splash_bg_color', 9)->default('#FFFFFF');
    $table->string('mobile_app_display_name')->nullable();

    // Email & PDF
    $table->string('email_header_logo_path')->nullable();
    $table->text('email_footer_text')->nullable();
    $table->enum('receipt_layout', ['simple', 'formal', 'modern'])->default('formal');
    $table->boolean('pdf_watermark_enabled')->default(false);

    // Notification
    $table->string('fcm_notification_icon_path')->nullable();
    $table->string('fcm_notification_color', 9)->nullable();

    // Cache versioning — increment on update for cache busting
    $table->unsignedInteger('cache_version')->default(1);

    $table->timestamps();
});
```

**Catatan:** semua path file disimpan **relative path** (`branding/{school_id}/logo-primary.png`), URL di-resolve oleh `Storage::disk('s3')->url($path)`.

---

## API Endpoints

### Public (no auth — for login page rendering & mobile app boot)

| Method | URI | Description |
|---|---|---|
| GET | `/api/v1/branding/{subdomain}` | Get branding by subdomain (untuk Flutter app boot, login page) |

### Authenticated

| Method | URI | Role | Description |
|---|---|---|---|
| GET | `/api/v1/branding` | any auth | Branding sekolah aktif user |

### Admin

| Method | URI | Role | Description |
|---|---|---|---|
| GET | `/api/v1/admin/branding` | admin | Get full branding config |
| PUT | `/api/v1/admin/branding` | admin | Update text/color fields (bulk) |
| POST | `/api/v1/admin/branding/upload-logo` | admin | Upload logo (multipart, type: primary/secondary/monochrome/favicon/login_bg/splash_logo/email_header/fcm_icon) |
| DELETE | `/api/v1/admin/branding/logo/{type}` | admin | Remove specific logo |
| POST | `/api/v1/admin/branding/reset` | admin | Reset to defaults |

---

## Service: `BrandingService`

```php
namespace App\Services;

use App\Models\School;
use App\Models\SchoolBranding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class BrandingService
{
    public function getForSchool(int $schoolId): array
    {
        return Cache::remember("branding:school:{$schoolId}", 3600, function () use ($schoolId) {
            $branding = SchoolBranding::where('school_id', $schoolId)->first()
                ?? $this->createDefaults($schoolId);
            return $this->toArray($branding);
        });
    }

    public function getBySubdomain(string $subdomain): array
    {
        $school = School::where('subdomain', $subdomain)->firstOrFail();
        return $this->getForSchool($school->id);
    }

    public function update(int $schoolId, array $data): SchoolBranding
    {
        $branding = SchoolBranding::firstOrCreate(['school_id' => $schoolId]);
        $branding->update($data);
        $branding->increment('cache_version');
        $this->forgetCache($schoolId);
        return $branding->fresh();
    }

    public function uploadLogo(int $schoolId, string $type, UploadedFile $file): string
    {
        $allowed = ['primary', 'secondary', 'monochrome', 'favicon', 'login_bg', 'splash_logo', 'email_header', 'fcm_icon'];
        if (!in_array($type, $allowed)) {
            throw new \InvalidArgumentException("Unknown logo type: {$type}");
        }

        $path = $file->storeAs(
            "branding/{$schoolId}",
            $type . '-' . time() . '.' . $file->getClientOriginalExtension(),
            ['disk' => config('filesystems.default')]
        );

        $field = match ($type) {
            'primary'      => 'logo_primary_path',
            'secondary'    => 'logo_secondary_path',
            'monochrome'   => 'logo_monochrome_path',
            'favicon'      => 'favicon_path',
            'login_bg'     => 'login_background_path',
            'splash_logo'  => 'mobile_splash_logo_path',
            'email_header' => 'email_header_logo_path',
            'fcm_icon'     => 'fcm_notification_icon_path',
        };

        $this->update($schoolId, [$field => $path]);
        return $path;
    }

    public function resolveUrl(?string $path): ?string
    {
        return $path ? Storage::disk(config('filesystems.default'))->url($path) : null;
    }

    public function toArray(SchoolBranding $b): array
    {
        return [
            'display_name'  => $b->display_name,
            'tagline'       => $b->tagline,
            'colors' => [
                'primary'   => $b->color_primary,
                'secondary' => $b->color_secondary,
                'success'   => $b->color_success,
                'warning'   => $b->color_warning,
                'danger'    => $b->color_danger,
            ],
            'logos' => [
                'primary'      => $this->resolveUrl($b->logo_primary_path),
                'secondary'    => $this->resolveUrl($b->logo_secondary_path),
                'monochrome'   => $this->resolveUrl($b->logo_monochrome_path),
                'favicon'      => $this->resolveUrl($b->favicon_path),
                'splash'       => $this->resolveUrl($b->mobile_splash_logo_path),
                'email_header' => $this->resolveUrl($b->email_header_logo_path),
                'fcm_icon'     => $this->resolveUrl($b->fcm_notification_icon_path),
                'login_bg'     => $this->resolveUrl($b->login_background_path),
            ],
            'login' => [
                'welcome_text' => $b->login_welcome_text,
                'show_motto'   => $b->login_show_motto,
            ],
            'mobile' => [
                'splash_bg_color' => $b->mobile_splash_bg_color,
                'app_name'        => $b->mobile_app_display_name,
            ],
            'pdf' => [
                'receipt_layout'    => $b->receipt_layout,
                'watermark_enabled' => $b->pdf_watermark_enabled,
            ],
            'cache_version' => $b->cache_version,
        ];
    }

    protected function createDefaults(int $schoolId): SchoolBranding
    {
        return SchoolBranding::create(['school_id' => $schoolId]);
    }

    protected function forgetCache(int $schoolId): void
    {
        Cache::forget("branding:school:{$schoolId}");
    }
}
```

---

## Frontend Application

### Blade Layout — Inject Branding via CSS Variables

```blade
{{-- resources/views/layouts/app.blade.php --}}
@php
    $branding = app(\App\Services\BrandingService::class)->getForSchool(auth()->user()->school_id);
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $branding['display_name'] ?? config('app.name') }}</title>
    @if($branding['logos']['favicon'])
        <link rel="icon" href="{{ $branding['logos']['favicon'] }}?v={{ $branding['cache_version'] }}">
    @endif
    <style>
        :root {
            --color-primary:   {{ $branding['colors']['primary'] }};
            --color-secondary: {{ $branding['colors']['secondary'] }};
            --color-success:   {{ $branding['colors']['success'] }};
            --color-warning:   {{ $branding['colors']['warning'] }};
            --color-danger:    {{ $branding['colors']['danger'] }};
        }
    </style>
</head>
<body>
    <header>
        @if($branding['logos']['primary'])
            <img src="{{ $branding['logos']['primary'] }}" alt="{{ $branding['display_name'] }}" class="h-10">
        @else
            <span class="font-bold text-xl">{{ $branding['display_name'] ?? config('app.name') }}</span>
        @endif
    </header>
    @yield('content')
</body>
</html>
```

### Tailwind Config

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        primary:   'var(--color-primary)',
        secondary: 'var(--color-secondary)',
        success:   'var(--color-success)',
        warning:   'var(--color-warning)',
        danger:    'var(--color-danger)',
      },
    },
  },
};
```

### Flutter — Boot Branding

```dart
// lib/core/branding/branding_service.dart
class BrandingService {
  final ApiClient api;
  Branding? _cached;

  Future<Branding> load(String subdomain) async {
    if (_cached != null) return _cached!;
    final res = await api.get('/api/v1/branding/$subdomain');
    _cached = Branding.fromJson(res.data);
    return _cached!;
  }
}

// lib/app/theme/app_theme.dart
ThemeData buildTheme(Branding branding) => ThemeData(
  colorScheme: ColorScheme.fromSeed(
    seedColor: hexToColor(branding.colors.primary),
  ),
  useMaterial3: true,
);
```

App boot flow:
1. User input `subdomain` di first launch (atau dari deep link)
2. Call `/api/v1/branding/{subdomain}` → cache hasil di `flutter_secure_storage`
3. Apply theme + splash + app name dari result
4. Lanjut ke login page

---

## Cache Strategy

- Branding di-cache di Redis 1 jam per school
- `cache_version` field auto-increment saat update → invalidate Cache + bust browser cache via `?v=N` di asset URL
- Flutter app: cache di local storage 7 hari, refresh on app boot kalau `cache_version` berubah

---

## Acceptance Criteria

- [ ] Admin upload logo via UI → langsung berubah di header (refresh browser)
- [ ] Color picker → CSS vars update → semua tombol primary ganti warna
- [ ] Reset to defaults → field kosong → fallback ke nilai default Tailwind
- [ ] Cross-school isolation: school A logo tidak bocor ke school B
- [ ] Mobile app: ganti logo splash → next launch tampil logo baru
- [ ] Email & PDF receipt: pakai logo + warna sekolah
- [ ] Browser favicon: berubah per subdomain
- [ ] File upload: max 2MB, validate MIME (PNG/SVG/ICO), no SVG with `<script>`

---

## Tests to Write

```
tests/Feature/Branding/
  GetBrandingPublicTest.php
  GetBrandingAuthenticatedTest.php
  UpdateBrandingAdminTest.php
  UpdateBrandingUnauthorizedTest.php
  UploadLogoTest.php
  UploadInvalidFileTest.php
  CacheInvalidationTest.php
  CrossSchoolBrandingIsolationTest.php
  ResetBrandingTest.php

tests/Unit/Branding/
  BrandingServiceTest.php
  ResolveUrlTest.php
```

---

## Future Enhancements (Out of Scope for v1)

- Per-role color override (e.g., admin panel pakai warna beda dari student panel)
- Multi-theme switcher (light + dark mode aware branding)
- Custom font upload (TTF/WOFF2)
- Letterhead template editor (drag-drop)
- A/B test login page variants

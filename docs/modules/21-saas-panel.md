# Module 21 — Super Admin SaaS Panel

## Depends On
Semua modul sebelumnya (01–20). Ini adalah panel monitoring dan manajemen platform.

## What to Build
Panel super admin di `admin.eschool.app`. Kelola semua sekolah, lihat analytics platform,
manage plans, subscription billing, system config, dan activity monitoring.

---

## API Endpoints (Prefix: /api/v1/super/)

| Method | URI                                        | Deskripsi                             |
|--------|--------------------------------------------|---------------------------------------|
| GET    | `/super/dashboard`                         | Statistik platform (semua sekolah)    |
| GET    | `/super/schools`                           | List semua sekolah                    |
| POST   | `/super/schools`                           | Daftarkan sekolah baru                |
| GET    | `/super/schools/{id}`                      | Detail sekolah                        |
| PUT    | `/super/schools/{id}`                      | Update info sekolah                   |
| POST   | `/super/schools/{id}/suspend`              | Suspend sekolah                       |
| POST   | `/super/schools/{id}/activate`             | Aktifkan sekolah                      |
| DELETE | `/super/schools/{id}`                      | Soft delete sekolah                   |
| GET    | `/super/schools/{id}/stats`                | Statistik satu sekolah                |
| GET    | `/super/schools/{id}/activity-log`         | Log aktivitas sekolah                 |
| GET    | `/super/plans`                             | List semua plan                       |
| POST   | `/super/plans`                             | Buat plan baru                        |
| PUT    | `/super/plans/{id}`                        | Update plan                           |
| GET    | `/super/subscriptions`                     | List semua transaksi langganan        |
| POST   | `/super/subscriptions`                     | Catat pembayaran langganan            |
| POST   | `/super/schools/{id}/subscription/extend`  | Perpanjang langganan sekolah          |
| POST   | `/super/schools/{id}/subscription/upgrade` | Upgrade plan sekolah                  |
| GET    | `/super/analytics/revenue`                 | Laporan revenue per bulan/plan        |
| GET    | `/super/analytics/growth`                  | Pertumbuhan jumlah sekolah & siswa    |
| GET    | `/super/system/config`                     | Konfigurasi global platform           |
| PUT    | `/super/system/config`                     | Update konfigurasi global             |

---

## Super Admin Dashboard Data

```json
GET /api/v1/super/dashboard
{
  "overview": {
    "total_schools": 142,
    "active_schools": 138,
    "suspended_schools": 4,
    "total_students": 48320,
    "total_teachers": 3842,
    "total_revenue_this_month_cents": 25841800
  },
  "plan_distribution": [
    { "plan": "Free",  "count": 45, "percentage": 31.7 },
    { "plan": "Basic", "count": 67, "percentage": 47.2 },
    { "plan": "Pro",   "count": 30, "percentage": 21.1 }
  ],
  "subscriptions_expiring_soon": [
    { "school": "SMKN 1 Jakarta", "expires_at": "2025-08-01", "plan": "Pro" },
    { "school": "SMA 2 Bandung",  "expires_at": "2025-08-05", "plan": "Basic" }
  ],
  "monthly_revenue": [
    { "month": "2025-02", "amount_cents": 21500000 },
    { "month": "2025-03", "amount_cents": 23100000 },
    { "month": "2025-04", "amount_cents": 24800000 },
    { "month": "2025-05", "amount_cents": 25841800 }
  ],
  "new_schools_this_month": 12
}
```

---

## Files to Create

```
app/Http/Controllers/Api/SuperAdmin/
  SuperDashboardController.php
  SuperSchoolController.php
  SuperPlanController.php
  SuperSubscriptionController.php
  SuperAnalyticsController.php
  SuperSystemConfigController.php

app/Services/
  SuperAdminService.php
  PlatformAnalyticsService.php

resources/views/super-admin/
  dashboard.blade.php
  schools/
    index.blade.php
    show.blade.php
    create.blade.php
  plans/
    index.blade.php
  subscriptions/
    index.blade.php
  analytics/
    revenue.blade.php
    growth.blade.php
  config/
    index.blade.php
```

---

## SuperAdmin Middleware (Bypass SchoolScope)

```php
// app/Http/Middleware/SuperAdminOnly.php
class SuperAdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->hasRole('super_admin')) {
            abort(403, 'Super admin access only.');
        }
        return $next($request);
    }
}

// routes/api.php
Route::prefix('v1/super')
    ->middleware(['auth:sanctum', 'super_admin'])
    ->group(function () {
        // Semua route super admin di sini
        // CATATAN: SchoolScope TIDAK berlaku di sini
        // Query harus withoutGlobalScope(SchoolScope::class) jika perlu
    });
```

---

## Web Panel Layout (Blade + Alpine + Tailwind)

```
┌─────────────────────────────────────────────────────────┐
│  eSchool SaaS — Super Admin                 [Logout]    │
├──────────────┬──────────────────────────────────────────┤
│  SIDEBAR     │  MAIN CONTENT                           │
│              │                                          │
│  Dashboard   │  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐  │
│  Schools     │  │  142 │ │  138 │ │48.3K │ │ 25.8M│  │
│  Plans       │  │Seklh │ │Aktif │ │Siswa │ │ Rev. │  │
│  Subscript.  │  └──────┘ └──────┘ └──────┘ └──────┘  │
│  Analytics   │                                          │
│  Config      │  [Revenue Chart — Line — 12 months]      │
│              │                                          │
│              │  [Schools Table — sortable + filter]     │
│              │  Nama | Plan | Siswa | Expired | Status  │
└──────────────┴──────────────────────────────────────────┘
```

---

## System Config Options

```php
// Global config yang dikelola super admin:
[
  'app_name'              => 'eSchool SaaS',
  'app_logo'              => 'S3 path',
  'default_plan'          => 'free',
  'trial_days'            => 14,
  'grace_period_days'     => 7,
  'support_email'         => 'support@eschool.app',
  'smtp_host'             => '...',
  'smtp_port'             => 587,
  'smtp_username'         => '...',
  'smtp_password'         => '...',
  'firebase_server_key'   => '...',
  'sms_gateway_key'       => '...',
  'storage_driver'        => 's3',
  's3_bucket'             => '...',
  'pusher_app_id'         => '...',
  'pusher_key'            => '...',
  'pusher_secret'         => '...',
  'maintenance_mode'      => false,
  'allow_school_register' => true,    // self-service registration
]
```

---

## Acceptance Criteria

- [ ] Hanya `super_admin` yang bisa akses endpoint `/super/*`
- [ ] Suspend sekolah membuat semua user sekolah itu tidak bisa login
- [ ] Dashboard menampilkan statistik real-time semua sekolah
- [ ] Revenue chart akurat dari subscription_transactions
- [ ] Extend/upgrade langganan langsung update `plan_expires_at` sekolah
- [ ] System config tersimpan di cache dan bisa di-flush

## Tests to Write

```
tests/Feature/SuperAdmin/
  DashboardStatsTest.php
  SchoolManagementTest.php
  SuspendSchoolTest.php
  PlanManagementTest.php
  SubscriptionExtendTest.php
  NonSuperAdminBlockedTest.php
  RevenueAnalyticsTest.php
```

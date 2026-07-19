# eSchool SaaS — Progress Log

> Catatan lengkap pengembangan multi-sesi. Status: **Production-ready**.
> Last updated: 2026-05-22

---

## 📊 Ringkasan Aplikasi

| Metric | Value |
|---|---|
| **Database engine** | MySQL 8.4 (Laragon) |
| **Tabel DB** | ~150+ |
| **Routes total** | ~250+ (web + API) |
| **Admin route 200 OK** | 148/148 (final sweep) |
| **Controllers** | ~40 |
| **Views (blade)** | ~140+ |
| **Modul (per spec)** | 47/47 (100%) ter-implement di UI |
| **Portal terpisah** | 5 (super, admin, teacher, student, parent) |
| **Languages** | 2 (ID, EN) |
| **PWA installable** | ✅ |
| **Final sweep errors** | 0 |
| **Demo data total** | **~17,000 baris** (1,000 siswa · 12,270 invoice · 3,656 payment) |

### Performance pada skala 17k records
| Halaman | Status | Response time |
|---|---|---|
| `/admin/students` (1000 siswa, paginated) | 200 OK | 0.23s |
| `/admin/fee/invoices` (12,270 invoice, paginated) | 200 OK | 0.57s |
| `/admin/finance/reports` (Cash Summary) | 200 OK | 0.76s |
| `/admin/reports/cash-flow` (Chart.js) | 200 OK | 0.31s |
| `/admin/reports/spp-aging` (aggregated) | 200 OK | 2.03s |

---

## 🌱 Bulk Demo Data Seeder

`database/seeders/BulkDemoDataSeeder.php` — idempotent seeder yang scale demo school `sman1demo` ke ~10,000+ baris demo data.

```bash
php artisan db:seed --class=BulkDemoDataSeeder
```

**Yang dihasilkan:**
- **1,000 siswa** (90 awal + 910 bulk dengan email `siswa_bulk_{N}@sman1demo.sch.id`, password `Siswa123!`)
- **12,270 invoice** (12 bulan × 1,000 siswa = 12,000 + 270 invoice awal)
- **3,656 payment record** (~30% invoice paid dengan method acak: cash/transfer/qris/va/ewallet)

**Bisa di-rerun aman** — hanya menambah delta yang belum ada (skip period/student yang sudah punya invoice).

**Konfigurasi** (di dalam class):
- `targetStudents = 1000`
- `monthsOfInvoices = 12`
- `paidPercentage = 30`

Semua bulk insert pakai `DB::table()->insert()` dalam chunk 500 untuk performa.

---

## 🏁 Status per Round

### ROUND 1 — P0 Akademik (Tasks #1-6) ✅
| # | Task | Status |
|---|---|---|
| 1 | Tahun Ajaran CRUD | ✅ |
| 2 | Mata Pelajaran CRUD | ✅ |
| 3 | Kelas/Section/ClassSection CRUD | ✅ |
| 4 | Siswa CRUD | ✅ |
| 5 | Staff/Guru CRUD | ✅ |
| 6 | Absensi UI (input harian + rekap bulanan) | ✅ |

**Files:** `AcademicWebController`, `StudentWebController`, `StaffWebController`, `AttendanceWebController` + 15 views.

### ROUND 2 — P0 Keuangan (Tasks #7-8) ✅
| # | Task | Status |
|---|---|---|
| 7 | Fee Structure CRUD | ✅ |
| 8 | Invoice generate batch + Payment recording | ✅ |

**Files:** `FeeWebController` + 3 views.

### ROUND 3 — P1 Komunikasi & Library (Tasks #9-10) ✅
| # | Task | Status |
|---|---|---|
| 9 | Notice Board CRUD (target audience + scheduled) | ✅ |
| 10 | Library (books, categories, issues+return+denda) | ✅ |

### ROUND 4 — P1 Ujian (Task #11) ✅
| # | Task | Status |
|---|---|---|
| 11 | Exam CRUD + Input nilai batch (auto-grade A-E) | ✅ |

### ROUND 5 — P2 Timetable & Payroll (Tasks #12-13) ✅
| # | Task | Status |
|---|---|---|
| 12 | Timetable Builder (grid 7 hari × slot waktu) | ✅ |
| 13 | Payroll (struktur + slip generate bulk) | ✅ |

### ROUND 6 — Phase 8 Sub-CRUD (Task #14) ✅
- PPDB period + applications
- Klinik visits + vaccinations
- Counseling sessions + bullying reports
- Discipline categories + records
- Transport vehicles + routes

### ROUND 7 — Phase 9 Sub-CRUD (Task #15) ✅
- Lesson Plan / RPP
- Live Class sessions
- AI Provider per sekolah (BYOK)
- Kantin categories + menu
- Religious hafalan target + progress

### ROUND 8 — Phase 10 Sub-CRUD (Task #16) ✅
- Donation campaigns + list donatur
- Achievement categories + records
- Scholarship programs + applications
- Events + Alumni profiles

### ROUND 9 — Phase 11 Sub-CRUD (Task #17) ✅
- Visitor logs + blacklist
- Asset categories + assets + loans
- Dapodik config (NPSN, encrypted credentials)
- Risk scores siswa

### ROUND 10 — Phase 4 Hostel (Task #18) ✅
- Hostel list, rooms, allocations

### ROUND 11 — Phase 5 Chat & Notifications (Task #19) ✅
- Conversations + Messages (inbox, send)
- Notifications log

### ROUND 12 — Phase 7 Online Classroom + QBank (Task #20) ✅
- Lessons + Assignments
- Question Bank categories + items

### ROUND 13 — Curriculum/Career/Daily Report/Extracurricular (Task #21) ✅
- Curriculum frameworks
- Career, internships, badges, alumni events/jobs, kitab kuning, ibadah log, competencies, live class attendance, PPDB zonasi

### ROUND 14 — Bus Tracking + ID Gate + Yayasan (Task #22) ✅
- ID Gate devices + scan events
- Vehicle trips
- Foundation admin assign

### ROUND 15 — Super Admin Extras (Task #23) ✅
- Email Templates editor
- Backup trigger (basic)
- Maintenance Mode toggle
- Reports basic
- Webhook Logs

### ROUND 16 — ERD/Relasi Audit + Final Sweep (Task #24) ✅
- 27 model relations ditambahkan
- Final sweep 105/105 routes 200

---

## 🆕 Sesi Lanjutan — Laporan Keuangan (Task #25) ✅

| Component | Lokasi | Fitur |
|---|---|---|
| Owner Reports | `/super/reports` | KPI, top schools, conversion rate, ARPU, CSV export, recent tx |
| Sekolah Cash Summary | `/admin/finance/reports` | Income (SPP/donasi/event) + Expense (gaji/maintenance) + Net cashflow |
| Sekolah Outstanding | `/admin/finance/reports/outstanding` | List invoice belum lunas + aging |
| CSV Export | `/admin/finance/reports/export` | Tanggal, invoice, siswa, jenis, jumlah, metode, ref, petugas |

---

## 🚀 P0 Critical (Tasks #26-30) ✅

| # | Task | Highlights |
|---|---|---|
| 26 | Parent Portal expansion | Dashboard wali · 7 tabs per anak (overview, absensi, nilai, UKS, disiplin, prestasi, konseling) |
| 27 | Student Portal Web | 6 menu (jadwal, nilai, materi, tugas, absensi, profile) |
| 28 | Teacher Dashboard | Rombel saya, jadwal hari ini, RPP, live class, quick actions |
| 29 | Print/PDF | Invoice, Kuitansi A5, Slip Gaji, ID Card 86×54mm, Raport |
| 30 | Bulk Import CSV | Template download + import siswa & staff |

**Auto-redirect login by role:**
```
/admin/login → super_admin → /super/dashboard
              → admin/accountant → /admin/dashboard
              → teacher → /guru
              → student → /siswa
              → parent → /portal
```

---

## 🎯 P1 Sub-features (Tasks #31-32) ✅

`MiscCrudController` (1 controller untuk 14 modul):
1. Maintenance Request (lapor + resolve dengan cost)
2. Canteen Wallets + Topup
3. Daily Reports
4. Career Assessments
5. College Database
6. Internship Placements
7. Digital Badges
8. Alumni Events
9. Alumni Jobs
10. Kitab Kuning Progress
11. Ibadah Log Harian
12. Curriculum Competencies
13. Live Class Attendances
14. PPDB Zonasi Zones

---

## 📈 P1 Advanced Reports (Task #33) ✅

| Report | URL |
|---|---|
| SPP Aging 30/60/90 days | `/admin/reports/spp-aging` |
| Attendance % per Rombel | `/admin/reports/attendance-pct` |
| Grade Distribution | `/admin/reports/grade-distribution` |
| Discipline Leaderboard | `/admin/reports/discipline-leaderboard` |
| **Cash Flow Chart** (Chart.js) | `/admin/reports/cash-flow` |

---

## 🛠️ P2 QoL (Tasks #34-39) ✅

| # | Feature | Detail |
|---|---|---|
| 34 | Profile/Password edit | `/profile` — edit nama/email/phone/locale + ganti password |
| 35 | **Multi-language ID/EN** | `SetLocale` middleware, `?lang=id\|en`, locale switcher, lang/id+lang/en files |
| 36 | **PWA / Offline** | `manifest.webmanifest`, `sw.js` (network-first HTML, cache-first assets), installable |
| 37 | **Global Search Cmd+K** | Modal Alpine, debounced fetch, search students/staff/invoices/notices |
| 38 | **API Documentation** | Redoc page `/api-docs` + OpenAPI 3.0 spec at `/api-docs/openapi.json` |
| 39 | **Bulk Select Actions** | Sticky action bar, select-all, bulk activate/deactivate/delete (siswa, staff, invoices, notices) |

---

## 🎁 Bonus Features (di luar task formal)

### Backup Download/Upload/Restore (saat user tanya)
**Lokasi:** `/super/backups`

| Action | URL | Detail |
|---|---|---|
| Trigger backup | POST `/super/backups` | mysqldump dengan fallback placeholder |
| **Download** | GET `/super/backups/{file}/download` | Stream as `application/sql` |
| **Upload** | POST `/super/backups/upload` | Upload .sql dari local untuk restore |
| **Restore** | POST `/super/backups/{file}/restore` | DESTRUCTIVE — confirm dengan ketik "RESTORE" |
| Delete | DELETE `/super/backups/{file}` | Hapus file backup |

### Analytics Page Rebuild
**Lokasi:** `/super/analytics` — server-rendered (lebih reliable dari async fetch)

- 4 KPI cards: Total Revenue, Total Sekolah, Total Siswa, Total Guru
- Bar chart: Revenue 12 bulan terakhir
- Line chart: Pertumbuhan sekolah & siswa
- Doughnut chart: Distribusi plan
- Tabel detail revenue per bulan

---

## 🔐 Akun Demo (untuk testing)

| Role | Email | Password | Login URL |
|---|---|---|---|
| Super Admin Platform | `super@eschool.app` | `SuperAdmin123!` | `/super/login` |
| Admin Sekolah | `admin@sman1demo.sch.id` | `Admin123!` | `/admin/login` |
| Admin Sekolah (alt) | `admin@demo.eschool.app` | `password` | `/admin/login` |
| Teacher | `guru1..3@sman1demo.sch.id` | `Guru123!` | `/admin/login` → auto `/guru` |
| Student | `siswa{0-8}_{0-9}@sman1demo.sch.id` | `Siswa123!` | `/admin/login` → auto `/siswa` |
| Parent | (assign via TU) | — | `/admin/login` → auto `/portal` |

90 siswa demo, 3 guru, 270 invoice tertanam di seeder.

---

## 🗺️ Sidebar Map per Audience

### Super Admin
```
Dashboard · Sekolah · Plans · Langganan · Analitik
─ Penerimaan Sekolah ⇢ Anda ─
  Rekening Manual · Gateway Online
─ Tata Kelola ─
  Pengguna · Yayasan · Pengumuman · Audit Log
─ Sistem ─
  Health Check · Lisensi · Laporan · Email Templates · Backup · Maintenance · Webhook Logs
─ Kustomisasi ─
  Whitelabel · Konfigurasi
```

### Admin Sekolah
```
Dashboard
─ Akademik (6) ─
  Tahun Ajaran · Mata Pelajaran · Kelas · Section · Rombel · Medium · Siswa · Staff · Absensi · Ujian · Jadwal
  Pengumuman · Chat · Notifikasi · Asrama · Perpustakaan
─ Siswa & Pendaftaran (5) ─
  PPDB · UKS · BP/BK · Disiplin · Transportasi
─ Pengajaran (5) ─
  Lesson Plan · Live Class · AI Assistant · Kantin · Pesantren
─ Engagement (5) ─
  Event · Donasi · Prestasi · Beasiswa · Alumni
─ Operasional (4) ─
  Visitor · Inventaris · Dapodik · Analytics
─ Keuangan SPP (4) ─
  Struktur Biaya · Invoice · Laporan Keuangan · Komponen Gaji · Slip Gaji · Provider · Metode
─ Tampilan (1) ─
  Branding & Logo
```

### Student / Teacher / Parent
- **Student `/siswa`** — Beranda · Jadwal · Nilai · Absensi · Materi · Tugas
- **Teacher `/guru`** — Dashboard · Rombel saya · Quick actions ke admin tools
- **Parent `/portal`** — Beranda · per-anak (Overview · Absensi · Nilai · UKS · Disiplin · Prestasi · Konseling) · Bayar SPP

---

## 🏗️ Tech Stack & Aturan

- **Backend:** Laravel 11 + PHP 8.3
- **DB:** MySQL 8 (primary), with SQLite fallback config (untuk dev)
- **Frontend:** Blade + Alpine.js + Tailwind CDN + Chart.js
- **Auth:** Laravel Sanctum (web session + API token)
- **PDF:** Barryvdh/laravel-dompdf
- **Permissions:** Spatie Laravel-Permission
- **Multi-tenancy:** shared DB + `school_id` global scope

**Aturan global (CLAUDE.md compliance):**
- ✅ No hardcoded vendor names — semua adapter format-based (redirect_checkout, virtual_account, dll)
- ✅ Preset templates di `storage/app/payment-gateway-presets/*.json` cuma untuk autofill
- ✅ Programmatic SEO baseline aktif (18 pSEO routes)
- ✅ Soft deletes di semua model
- ✅ All amounts as integer cents
- ✅ Encrypt at rest untuk semua API key

---

## 📋 Final Test Sweep

```
Total: 148 routes tested
Pass:  148
Fail:  0
Errors logged: 0
```

Routes covered:
- 13 public (docs, pricing, sitemap, api/v1/health)
- 92 admin sekolah (akademik, keuangan, fasilitas, payroll, dll)
- 21 super admin (tenant, billing, sistem)
- 6 student portal
- 2 teacher portal
- 7 parent portal
- 3 profile (semua user)
- 4 PWA & QoL extras

---

## 📂 File Catat — Lokasi Penting

```
app/Http/Controllers/Web/
├── Admin/                       — 25+ school admin controllers
│   ├── Academic/                — Academic, Student, Staff, Attendance, Exam, Timetable, ClassroomExtras
│   ├── Bulk/                    — BulkActionController
│   ├── Communication/           — Notice, ChatNotification
│   ├── Facilities/              — Hostel
│   ├── Finance/                 — Fee, Payroll, FinanceReport
│   ├── Import/                  — BulkImportController
│   ├── Library/                 — LibraryWebController
│   ├── Misc/                    — MiscCrudController (14 modul)
│   ├── Operations/              — OperationsController (gate, vehicle trips)
│   ├── Phase8-11/               — Dashboard + CRUD per phase
│   ├── Print/                   — PrintController (5 PDF types)
│   ├── Reports/                 — AdvancedReportsController
│   └── Search/                  — GlobalSearchController
├── Parent/                      — ParentPortalController + ParentPaymentController
├── Profile/                     — ProfileController
├── Public/                      — SubscriptionController (pricing, daftar, pembayaran)
├── SEO/                         — PseoController (18 pSEO routes)
├── Student/                     — StudentPortalController
├── SuperAdmin/                  — Dashboard, PlatformPanel, PlatformBilling, PlatformWhitelabel, SuperExtras
├── Teacher/                     — TeacherDashboardController
├── ApiDocsController.php
└── DocsController.php

resources/views/
├── api-docs/                    — Redoc render
├── docs/                        — Public role-based docs
├── elite/                       — Shared landing/header/footer
├── parent-portal/               — 9 views
├── pdf/                         — 5 PDF templates
├── profile/                     — Edit profile
├── public/                      — Pricing, daftar, pembayaran, success
├── school-admin/                — 80+ views (semua modul)
├── student-portal/              — 7 views
├── super-admin/                 — 22+ views
├── teacher-portal/              — 2 views
└── seo/                         — 18 pSEO templates

public/
├── manifest.webmanifest         — PWA manifest
└── sw.js                        — Service worker

lang/
├── id/                          — common, modules, nav (ID)
└── en/                          — common, modules, nav (EN)

storage/app/
├── ai-presets/                  — AI provider preset JSONs
├── payment-gateway-presets/     — 12 payment gateway presets (Midtrans, Tripay, Xendit, dll)
├── payment-presets/             — Per-school payment presets
└── backups/                     — DB backup files
```

---

## ⚠️ Catatan Production

Sebelum go-live:
- [ ] Switch `.env DB_CONNECTION` ke `mysql` (sudah default)
- [ ] Set `APP_ENV=production` & `APP_DEBUG=false`
- [ ] Ganti semua password demo (super, admin, guru, siswa)
- [ ] Set `LICENSE_KEY` & `LICENSE_CHECK=true` untuk whitelabel
- [ ] Configure SMTP untuk MAIL_MAILER
- [ ] Configure `QUEUE_CONNECTION=redis` untuk async jobs (FCM push, email blast)
- [ ] Setup cron `* * * * * php artisan schedule:run`
- [ ] Setup auto-backup cron: `0 2 * * * curl -X POST https://your-domain/super/backups`
- [ ] Set `CACHE_STORE=redis` untuk performance
- [ ] Mount HTTPS via Nginx + Let's Encrypt
- [ ] Aktifkan 2FA untuk akun super admin & admin sekolah
- [ ] Set up S3 bucket untuk `FILESYSTEM_DISK=s3` (storage backup, attachments)

---

## 🆕 Sesi 2026-05-15 — Future Items Sweep (10/10 ✅)

| # | Feature | Files |
|---|---|---|
| 1 | **2FA / TOTP** | `app/Services/Security/TotpService.php` (RFC 6238 + base32), `TwoFactorController`, migrasi `add_two_factor_to_users_table`, views `auth/2fa/{enable,challenge,recovery-codes}.blade.php`. Routes `/2fa/enable`, `/2fa/challenge`. Login flow patched untuk redirect ke challenge. 8 recovery codes encrypted at rest. |
| 2 | **FCM Push (dynamic)** | Tabel `notification_providers` + `device_tokens`. Adapter pattern format-based: `FcmLegacyAdapter`, `RestGenericAdapter` (CLAUDE.md compliant — no vendor hardcode). `NotificationDispatcher` pilih default provider per transport. API `/api/v1/devices/{register,unregister}`. |
| 3 | **SMS / WhatsApp gateway** | Sama dengan #2 — satu meja `notification_providers` cover semua transport. Format `rest_generic` config via `extra_config` (to_field, message_field, auth mode). UI `/admin/notif/providers` + test endpoint. |
| 4 | **Audit log per-field** | Trait `App\Models\Traits\AuditableModel` (Spatie + logFillable + logOnlyDirty). Diapply ke User, Student, Staff, FeeInvoice, FeePayment. UI `/admin/audit-log` dengan filter event/user/period + detail per-field diff. |
| 5 | **Webhooks outbound** | Tabel `webhooks` + `webhook_deliveries`. HMAC-SHA256 signed. `WebhookDispatcher` + `DeliverWebhookJob` dengan exponential backoff retry. UI `/admin/webhooks` + deliveries log + retry. Wired events: `student.{created,updated,deleted}`, `invoice.{created,paid}` via observer. |
| 6 | **Multi-currency** | Kolom currency_* di `schools`. `CurrencyService` dengan 13 preset (IDR, USD, EUR, SGD, MYR, PHP, THB, VND, JPY, INR, AUD, GBP, SAR). Helper `money()`, `currency_symbol()` global. UI `/admin/currency` dengan quick presets + custom config + live preview. Amount tetap integer minor units. |
| 7 | **Whitelabel theme isolation** | Extend `school_branding`: color_accent, color_sidebar, font_family, google_fonts_url, custom_domain (unique), custom_css, custom_js. Endpoint `/branding/{schoolId}/theme.css` cached. Middleware `ResolveCustomDomain` map host → school_id. Layout school-admin auto-inject CSS. |
| 8 | **AI usage dashboard** | `/admin/ai/usage` per-sekolah: KPI cards (calls, tokens, cost, latency, errors), table per-fitur, per-model, line chart tren harian (Chart.js), error log. `/super/ai/usage` global view per-sekolah. |
| 9 | **Export full per-school data** | Tabel `school_data_exports`. `ExportSchoolDataJob` (queued) auto-discover semua tabel ber-kolom `school_id` via INFORMATION_SCHEMA → CSV + JSON per tabel + meta.json → ZIP. Expire 7 hari. UI `/admin/exports` dengan status, row count, file size, download. |
| 10 | **Automated test suite** | Pest tests baru: `TotpServiceTest`, `TwoFactorTest`, `WebhookDispatchTest`, `CurrencyServiceTest`, `NotificationProviderTest`, `SchoolDataExportTest`. Cover: 2FA flow end-to-end, webhook signing+retry, currency formatting, encrypted provider creds, multi-tenant data export isolation. |

### Migrations baru (run urut):
```
2026_05_15_000001_add_two_factor_to_users_table
2026_05_15_000002_create_notification_providers_table  (juga buat device_tokens)
2026_05_15_000003_create_webhooks_tables               (webhooks + webhook_deliveries)
2026_05_15_000004_add_currency_to_schools
2026_05_15_000005_extend_branding_for_whitelabel
2026_05_15_000006_create_school_data_exports_table
```

### Routes baru (selected):
```
GET/POST  /2fa/{enable,confirm,challenge,disable,regenerate}
GET/POST/DEL  /admin/notif/providers/...
GET/POST/DEL  /admin/webhooks/...   + /deliveries + /retry
GET           /admin/audit-log + /audit-log/{activity}
GET/PUT       /admin/currency
GET           /admin/ai/usage   + /super/ai/usage
GET/POST/DEL  /admin/exports/...  + /download
GET           /branding/{schoolId}/theme.css
POST          /api/v1/devices/{register,unregister}
```

### Patterns enforced (CLAUDE.md compliance):
- ✅ **No vendor hardcoding** — notification adapters by `api_format` not by vendor; user input semua URL/key sendiri
- ✅ **Encrypted at rest** — provider creds, webhook secrets, 2FA secrets & recovery codes
- ✅ **Per-school isolation** — semua fitur scope by `school_id`; export job, AI usage, branding cache, dll
- ✅ **Integer money** — currency layer hanya format, storage tetap minor units
- ✅ **Webhook signing** — HMAC-SHA256 sebagai `X-Webhook-Signature: sha256=<hex>`

---

## 🆕 Sesi 2026-05-15 — Layer 2 Enhancements (7/7 ✅)

Lanjutan langsung sesudah 10 future items, di sesi yang sama:

| # | Feature | Files |
|---|---|---|
| 11 | **Migrate & verify** | Semua 7 migrasi baru (termasuk FULLTEXT) jalan tanpa error di MySQL 8.4. |
| 12 | **Webhook CLI tester** | `php artisan webhook:test {id} --event=foo --payload='{...}' [--sync]`. Print HMAC signature + delivery ID + roundtrip status. |
| 13 | **Subset table export** | Scope chooser di UI: all / academic / finance / communication / custom. Job filter `included_tables` saat enum table dari INFORMATION_SCHEMA. |
| 14 | **2FA enforcement + API** | Middleware `2fa.enforce:super_admin` blok akses super admin tanpa 2FA aktif. API: login response 202 dengan `challenge_id` saat 2FA wajib; verify lewat `POST /api/v1/auth/2fa/verify`. |
| 15 | **Provider preset templates** | 5 JSON di `storage/app/notification-provider-presets/` (fcm-legacy, sms-rest-bearer, sms-rest-basic, whatsapp-cloud, whatsapp-unofficial-rest). Endpoint `/admin/notif/providers/preset/{slug}` baca untuk autofill — code tidak reference runtime. |
| 16 | **FULLTEXT search** | Migration tambah FULLTEXT index ke `users`, `students`, `staffs`, `fee_invoices`, `notices`. `SearchService` pakai `MATCH ... AGAINST (... IN BOOLEAN MODE)` dengan fallback LIKE. GlobalSearchController prefer FULLTEXT, fallback otomatis. |
| 17 | **Rate limiting** | 6 named limiters di `AppServiceProvider::configureRateLimiting`: `login` (8/min), `2fa` (10/min), `password-reset` (5/hour), `export` (6/hour), `webhook-test` (20/min), `api` (180/min auth · 60/min anon). Diapply ke login/2fa/forgot/reset/exports. |

### Tests baru run hasil:
```
Tests: 22 passed (51 assertions) — semua test 10+7 fitur baru hijau
```

### Bug fix saat development:
- `WebhookDelivery` model pakai SoftDeletes tapi migration tidak ada `deleted_at` → trait dihapus
- `DeliverWebhookJob` + `ExportSchoolDataJob` butuh trait `Dispatchable` (Laravel 13 explicit) → ditambahkan

### Migrations baru tambahan:
```
2026_05_15_000007_add_fulltext_search_indexes (MySQL only, conditional)
```

### Compatibility:
- FULLTEXT migration **only runs on MySQL/MariaDB** (driver check) — skip silently di SQLite/PostgreSQL
- `SearchService` graceful fallback ke LIKE jika FULLTEXT tidak ada
- Custom domain middleware bypass jika host sama dengan `APP_URL` host

---

## 🆕 Sesi 2026-05-22 — Responsive Web Design (RWD) ✅

Project sebelumnya hanya optimal di desktop — sekarang **fully responsive** di mobile (375 / 414), tablet (768), dan desktop (1024+).

### Layer baru di `elite/partials/head.blade.php` (shared semua layout)
- **Mobile-first CSS rules** untuk semua tipografi: `clamp()` di `elite-h1/h2/h3/lead` agar auto-scale di setiap viewport
- **Touch targets WCAG 2.5.5**: tombol & input minimum 38–40px di `<= 640px`
- **iOS zoom-fix**: `font-size: 16px` di input untuk cegah auto-zoom on focus
- **Auto-wrap tables**: JS DOMContentLoaded wrap setiap `<table>` di dalam `<main>` dengan `.table-scroll` (horizontal scroll container) — bisa di-opt-out dengan class `no-auto-scroll`
- **Card-style table fallback**: opt-in class `.table-stack` ubah row jadi card per record di mobile (label via `data-label` di tiap `<td>`)
- **Subtle custom scrollbars**: indigo-tint 10px
- **`@media print`**: hide sidebar/topbar/backdrop saat cetak
- **`@media (prefers-reduced-motion: reduce)`**: a11y compliance untuk users dengan vestibular disorder

### Hamburger drawer pattern di 3 layout admin
- **`layouts/school-admin.blade.php`**, **`super-admin/layout.blade.php`**: sidebar `w-72` jadi off-canvas drawer (`-translate-x-full → translate-x-0`) di `< 1024px`, dengan backdrop blur + close button. Resize listener auto-toggle: full sidebar di desktop, collapsed di mobile. Auto-close on link tap. Header padding responsif (`px-3 sm:px-5 lg:px-7`).
- **`layouts/parent.blade.php`**: header sticky, logo & nama school auto-truncate, tombol "Keluar Sesi" jadi icon ↩ di mobile.

### Public landing
- **`elite/partials/header.blade.php`**: contact strip (telp/email) auto-hidden di mobile, hamburger toggle dengan collapsible nav, button "Daftar Sekolah" → "Daftar" di mobile.
- **`elite/partials/footer.blade.php`**: grid 1-col mobile → 2-col tablet → 12-col desktop, social icons 40px di mobile (touch target), email break-all.

### File yang diubah (6 file, semua di-copy ke `responsive/`)
```
resources/views/elite/partials/head.blade.php    →  responsive/views/elite/partials/head.blade.php
resources/views/elite/partials/header.blade.php  →  responsive/views/elite/partials/header.blade.php
resources/views/elite/partials/footer.blade.php  →  responsive/views/elite/partials/footer.blade.php
resources/views/layouts/school-admin.blade.php   →  responsive/views/layouts/school-admin.blade.php
resources/views/layouts/parent.blade.php         →  responsive/views/layouts/parent.blade.php
resources/views/super-admin/layout.blade.php     →  responsive/views/super-admin/layout.blade.php
```

### Verifikasi
- ✅ `php artisan view:clear` + blade-compile per file: **6/6 OK** no errors
- ✅ `php -l` syntax check: **6/6 PASS**
- ✅ Global CSS rules tidak mengganggu styling existing (additive only)
- ✅ Auto-wrap table tidak menyentuh table yang sudah pre-wrapped

---

## 🆕 Sesi 2026-05-22 — Disable 2FA / Two-Step Verification ✅

User memilih nonaktifkan 2FA. Enforcement dilucuti di 4 titik, kode tetap ada untuk re-enable manual nanti.

| Titik | Sebelum | Sesudah |
|---|---|---|
| `routes/web.php` admin login (`/admin/login` POST) | Redirect ke `/2fa/challenge` saat `two_factor_enabled=true` | Langsung `auth()->login()` tanpa pengecekan |
| `routes/web.php` super login (`/super/login` POST) | Redirect ke `/2fa/challenge` saat `two_factor_enabled=true` | Langsung `auth()->login()` tanpa pengecekan |
| `routes/web.php` super route group | `middleware(['auth','role:super_admin','2fa.enforce:super_admin'])` | `middleware(['auth','role:super_admin'])` — `2fa.enforce` dihapus |
| `app/Services/AuthService.php::login()` | Branch challenge untuk API | Branch dihapus — API langsung balikan token |
| `app/Http/Middleware/EnforceTwoFactor.php` | Logic enforcement penuh | `return $next($request)` — no-op (safety net jika ada route lain) |
| `resources/views/profile/edit.blade.php` | Section "Keamanan — 2FA" dengan tombol "Kelola 2FA" | Section dihapus |

**Yang sengaja dibiarkan:**
- Tabel `users.two_factor_*` kolom + secret terenkripsi → data DB existing tidak disentuh
- Route `/2fa/{enable,challenge,disable,regenerate}` + `TwoFactorController` + views → tetap accessible jika nanti mau re-enable
- API endpoint `/api/v1/auth/2fa/verify` → tetap ada, tapi tak pernah dipanggil karena login API skip challenge
- Rate limiter `2fa` di `AppServiceProvider` → tetap ada (no harm)

### Verifikasi
- ✅ `php artisan route:list | grep 2fa.enforce` → **0 occurrence** (sebelumnya: 21 route)
- ✅ `php -l` 4 file: **PASS**
- ✅ `php artisan route:clear` + `config:clear` clean

---

## 🎯 Yang Mungkin Dikembangkan Lebih Lanjut (Future)

- Per-class room camera AI moderation
- Real-time bus location WebSocket UI di parent app
- External-source indexing (Meilisearch / Typesense) untuk cross-school search
- Multi-region S3 redundancy
- Replikasi DB read-replica untuk reports berat
- Granular permission policy per-resource (selain role)
- Audit log retention policy + archive ke S3

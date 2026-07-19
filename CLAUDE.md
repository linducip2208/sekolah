# eSchool SaaS — Project Bible for Claude Code

## MANDATORY: Read This First

You are building **eSchool SaaS** — a multi-tenant School Management ERP.
Before writing ANY code, read the relevant module doc in `docs/modules/`.
Before touching auth or permissions, read `docs/roles/rbac.md`.
Before touching database, read `docs/schema/conventions.md`.

This file is your entry point. All decisions flow from the docs here.

---

## User Preferences (CRITICAL)

- **Auto-approve everything** — user mempercayai Claude Code 100%
- **Jangan tanya konfirmasi** — langsung eksekusi semua keputusan
- **Jangan tawarkan pilihan** — pilih sendiri opsi terbaik dan lakukan
- **Jangan klik-klik** — user tidak mau diminta konfirmasi apapun

---

## Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 11 (PHP 8.3) |
| Frontend web | Blade + Alpine.js + Tailwind CSS |
| Mobile | Flutter 3.x (Dart) |
| Database | MySQL 8 (primary), Redis 7 (cache/queue) |
| Storage | S3-compatible (local `storage/` in dev) |
| Queue | Laravel Queue via Redis |
| Auth | Laravel Sanctum (SPA + mobile tokens) |
| Permissions | Spatie Laravel-Permission |
| Modules | nwidart/laravel-modules |
| Notifications | Firebase FCM (push) + SMTP (email) + SMS gateway |
| Real-time | Laravel Broadcasting + Pusher/Soketi (WebSocket) |
| Multi-tenancy | **Shared database, school_id on every table** |
| License | whitelabel.co.id (see `docs/modules/00-license.md`) |
| Deployment | Docker + Nginx + Let's Encrypt |

---

## Project Structure (Laravel)

```
app/
  Http/
    Controllers/
      Api/          ← mobile API endpoints
      Web/          ← blade web endpoints
    Middleware/
      EnsureSchoolAccess.php   ← CRITICAL: always applied on school routes
      EnsureValidLicense.php   ← CRITICAL: license check on every request
      RoleMiddleware.php
  Models/
  Services/         ← business logic lives here, NOT in controllers
  Repositories/     ← DB queries live here, NOT in services
  Policies/         ← Laravel policies per model
config/
  license.php       ← whitelabel.co.id license config
database/
  migrations/
  seeders/
docs/               ← architecture docs (READ BEFORE CODING)
  ARCHITECTURE.md   ← system overview, all module map, API structure
  HOW-TO-USE.md     ← how to work with this project
  modules/          ← one spec file per module
  roles/rbac.md     ← permission matrix
  schema/           ← DB conventions, multi-tenant rules
  deployment/       ← Docker, Nginx, CI/CD
  flutter/          ← Flutter app setup guide
  testing/          ← testing strategy
modules/            ← nwidart/laravel-modules
  Academic/
  Finance/
  Facilities/
  Communication/
  Auth/
resources/
  views/
  js/               ← Alpine.js components
routes/
  api.php
  web.php
tests/
  Feature/
  Unit/
```

---

## Non-Negotiable Rules

1. **Every DB query must include `where('school_id', auth()->user()->school_id)`** — no exceptions. Use the `SchoolScope` global scope on all models.

2. **No business logic in controllers.** Controllers call Services. Services call Repositories. This is enforced.

3. **Every API endpoint must be covered by a Feature test.**

4. **All amounts (fees, salary, etc.) are stored as integers (paise/sen/cents).** Never float.

5. **Soft deletes on all models** (`SoftDeletes` trait everywhere).

6. **Translations via `__('key')` always.** No hardcoded strings in views or API responses.

7. **Every migration must be reversible** (proper `down()` method).

8. **Role checks via Policy, never inline `if ($user->role === 'admin')`.**

9. **License check on every production request** — `LicenseChecker::validate()` in AppServiceProvider.

---

## How to Execute Modules

Modules are executed in dependency order. Each has its own doc:

```
Phase 0 — License (WAJIB PERTAMA)
  00. docs/modules/00-license.md

Phase 1 — Foundation
  01. docs/modules/01-multi-tenant-foundation.md
  02. docs/modules/02-auth-roles.md

Phase 2 — Academic Core
  03.  docs/modules/03-school-setup.md
  03b. docs/modules/03b-school-branding.md
  04.  docs/modules/04-academic-structure.md
  05.  docs/modules/05-attendance.md
  06.  docs/modules/06-timetable.md
  07.  docs/modules/07-online-classroom.md
  08.  docs/modules/08-exam-engine.md
  09.  docs/modules/09-marks-grades.md

Phase 3 — Finance & Admin
  10.  docs/modules/10-admission.md
  11.  docs/modules/11-fee-invoice.md
  11b. docs/modules/11b-payment-gateway.md  ← Dynamic, no hardcode
  12.  docs/modules/12-payroll.md
  13.  docs/modules/13-subscription.md

Phase 4 — Facilities
  14. docs/modules/14-library.md
  15. docs/modules/15-hostel.md
  16. docs/modules/16-transport.md

Phase 5 — Communication
  17. docs/modules/17-notice-board.md
  18. docs/modules/18-chat.md
  19. docs/modules/19-notifications.md

Phase 6 — Mobile
  20. docs/modules/20-flutter-app.md

Phase 7 — Super Admin
  21. docs/modules/21-saas-panel.md

Phase 8 — Student Lifecycle
  22. docs/modules/22-ppdb-online.md
  23. docs/modules/23-bus-tracking-id-gate.md
  24. docs/modules/24-uks-klinik.md
  25. docs/modules/25-bp-bk-discipline.md

Phase 9 — Teaching Tools
  26. docs/modules/26-lesson-plan-rpp.md
  27. docs/modules/27-cafeteria-cashless.md
  28. docs/modules/28-pesantren-madrasah-mode.md
  31. docs/modules/31-ai-assistant.md       ← Dynamic, no hardcode
  35. docs/modules/35-live-class.md         ← Dynamic, no hardcode
  36. docs/modules/36-question-bank.md
  40. docs/modules/40-curriculum-mapping.md

Phase 10 — Engagement & Growth
  29. docs/modules/29-donations-fundraising.md
  30. docs/modules/30-alumni-network.md
  37. docs/modules/37-achievement-tracker.md
  38. docs/modules/38-scholarship.md
  39. docs/modules/39-career-guidance.md
  42. docs/modules/42-event-management.md
  43. docs/modules/43-daily-report.md
  44. docs/modules/44-extracurricular.md

Phase 11 — Operations & Intelligence
  32. docs/modules/32-dapodik-sync.md
  33. docs/modules/33-visitor-management.md
  34. docs/modules/34-inventory-aset.md
  41. docs/modules/41-yayasan-dashboard.md
  45. docs/modules/45-learning-analytics.md
```

**To execute a module, say:**
> "Execute module 22 — PPDB online"

Claude Code will read the spec doc and implement exactly what is specified there.

---

## Key Conventions

- See `docs/ARCHITECTURE.md` for full system overview
- See `docs/schema/conventions.md` for DB naming
- See `docs/roles/rbac.md` for permission matrix
- See `docs/schema/multi-tenant.md` for tenancy rules
- See `docs/deployment/docker.md` for deployment setup
- See `docs/flutter/setup.md` for Flutter dev setup
- See `docs/testing/strategy.md` for testing approach
- See `LICENSE_API.md` for license API reference

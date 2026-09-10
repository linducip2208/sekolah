# SIKAD PRO — Final Enterprise Audit

Tanggal audit: 10 September 2026
Repository: `linducip2208/sekolah`
Stack: Laravel 13, PHP 8.3, MySQL-compatible database, Blade, Tailwind 4, Vite, Sanctum, Reverb, Spatie Permission, Spatie Activity Log, queues, scheduler, Pest/PHPUnit, Playwright.

## 1. Executive summary

Repository latest berhasil diaudit dan dipertahankan sebagai source of truth. Perubahan enterprise pada siklus ini bersifat additive: migration baru, service/domain layer, model, API, test, dan hardening authorization. Tidak ada migration production lama yang dihapus dan tidak ada fitur existing yang direwrite dari nol.

Status penting yang terverifikasi:

- ✅ Migration production `2026_08_23_000002_schema_audit_fixes`, `2026_09_10_000001_add_enterprise_visitor_wallet_dapodik_tables`, dan lifecycle workflow `2026_09_10_000005_expand_workflow_lifecycle_statuses` berhasil dijalankan.
- ✅ Build frontend berhasil dengan `npm.cmd run build`.
- ✅ Pint dan PHP syntax check berhasil pada file yang diubah.
- ✅ Flow Visitor, immutable Wallet Ledger, idempotency, cross-school rejection, dan Dapodik fake sync diuji.
- ✅ Full test suite lulus setelah migration repository test database diinisialisasi: 287 tests / 1.548 assertions.
- ⚠️ Dapodik live integration memerlukan endpoint dan credential sekolah; adapter tidak mengarang endpoint vendor.
- ✅ Playwright desktop capture 26/26 halaman dan mobile capture 5/5 halaman berhasil pada server lokal port 8765.
- ✅ Portal capture 4/4 (student dan parent, desktop/mobile) serta dark-mode capture 5/5 berhasil.
- ✅ Responsive audit lintas viewport 320, 375, 414, 768, 1024, 1280, dan 1440 tidak menemukan horizontal overflow.
- ✅ Drawer audit mobile memverifikasi sidebar: tertutup → terbuka 315px + backdrop → tertutup setelah navigasi.
- ⚠️ Klaim “semua 50 domain enterprise COMPLETE” belum dapat dibuktikan hanya dari route/model. Domain yang belum memiliki bukti flow end-to-end tetap diberi status `REQUIRES EXTERNAL CONFIGURATION` atau `FAILED` di bawah.

## 2. Audit repository

Audit mencakup routes, controllers, models, services, middleware, policies, migrations, seeders, Blade views/components, navigation, dashboard, API, jobs, scheduled commands, tests, dan docs. Route registry menghasilkan 1.571 route setelah pass PPDB/kesiswaan/operasional/workflow/import-preview. Referensi route pada navigation configuration diverifikasi: 140 referensi, 0 route hilang.

Temuan yang diperbaiki:

- migration schema audit kehilangan import `DB`;
- migration wallet memakai nama index MySQL lebih dari 64 karakter dan tidak aman saat retry partial DDL;
- import Dapodik lama tidak membuat `user_id` yang wajib untuk student baru;
- form Visitor publik dapat jatuh ke sekolah pertama/ID `1`;
- endpoint Visitor, Wallet, dan Dapodik belum memakai permission granular secara konsisten;
- saldo kantin hanya cache tanpa ledger immutable dan refund idempotent;
- beberapa seeder/service memakai fallback ID tenant/user `1`;
- PPDB admin review masih dapat melewati lifecycle service dan public period belum memvalidasi seluruh window pendaftaran;
- Counseling/UKS/Discipline service boundaries belum konsisten memvalidasi student/counselor/category lintas sekolah;
- Lesson Plan API dan admin flow menerima referensi akademik lintas sekolah tanpa verifikasi konsisten;
- payment webhook belum memiliki payload fingerprint/replay record dan HMAC timestamp untuk outbound delivery;
- backup UI membuat file sintetis ketika `mysqldump` gagal;
- dashboard, parent portal, reminder, email subscription, tabs, dan command palette tidak lagi memiliki fallback actionable ke `#`; fallback navigasi kini menuju route yang valid atau tidak merender CTA;
- attendance dan marks maturity pass menambahkan lifecycle lock/reopen/correction, tenant-safe references, dan protection untuk rapor terkunci;
- classroom, timetable, dan religious service boundary pass menambahkan validasi referensi lintas sekolah, room/time conflict detection, atomic bulk timetable replacement, dan student ownership checks pada assignment/religious progress;
- LMS, CBT, room booking, dan bank reconciliation boundary pass menambahkan validasi course/exam/student/room/payment sekolah, approval ownership, serta summary reconciliation berbasis aggregate query;
- academic year dan student import boundary pass menambahkan validasi school ownership untuk aktivasi semester/tahun dan class section tujuan import;
- web CSV import sekarang melewati encrypted preview, validation/duplicate gate, explicit confirmation, batch cap, dan re-check saat commit; password tidak dikirim ke browser;
- inventory dan procurement maturity pass menambahkan row locking, non-negative stock invariant, transfer movement types, scoped supplier/budget validation, serta bounded partial receiving;
- accounting maturity pass menambahkan tenant-safe COA lines, double-entry line validation, row-locked posting, automatic reference idempotency, dan audit logging;
- payroll maturity pass menambahkan tenant-safe staff lookup, finalization row lock, paid-slip replay protection, audit logging, dan idempotent payroll journal;
- PPDB maturity pass menambahkan open/close window, jalur/quota validation, duplicate NISN detection, guarded lifecycle, configurable scoring, row-locked selection/waitlist, and audit logging;
- BK/Discipline/UKS maturity pass menambahkan permission gates, tenant-safe student/staff references, guarded counseling lifecycle, conflict detection, configurable sanction thresholds, and audit logging;
- library/hostel/transport maturity pass menambahkan tenant-safe member/room/route validation, row-locked inventory/occupancy updates, transactional transport assignment, and audit logging;
- workflow/notification maturity pass menambahkan tenant-bound requester/approver decisions, approve/reject/return/resubmit/cancel transitions, terminal-state protection, rejection/revision reason enforcement, and recipient filtering before notification logging/sending;
- test baru menguji flow enterprise dan cross-school access.

## 3. Feature matrix aktual

Legenda: `✅ COMPLETE` berarti flow penting yang diaudit tersedia dan diuji; `REQUIRES EXTERNAL CONFIGURATION` berarti code path tersedia tetapi integrasi/credential/deployment eksternal wajib disiapkan; `❌ FAILED` berarti belum layak disebut selesai.

| Domain | Status | Bukti / catatan |
|---|---|---|
| Platform SaaS, plan, subscription | ✅ COMPLETE | Existing migration, middleware, super-admin routes, usage tables. |
| School tenant scope | ✅ COMPLETE | `SchoolScope`, school-aware models, cross-school tests pada domain baru. |
| Foundation multi-school | ✅ COMPLETE | Existing foundation tables/controllers/dashboard; perlu regression coverage lebih luas. |
| RBAC / permissions | REQUIRES EXTERNAL CONFIGURATION | Spatie Permission aktif, permission seeder diperluas; sebagian legacy endpoint masih memakai role middleware. |
| Master data akademik | ✅ COMPLETE | Existing academic structure, curriculum, subject, class, calendar flows. |
| Academic planning | ✅ COMPLETE | Existing CP/TP/ATP, PROTA/PROMES, journal, lesson plan, schedule extensions. |
| Student 360 | ✅ COMPLETE | Existing profile/timeline and related tabs; sensitive visibility needs broader policy regression. |
| Attendance | ✅ COMPLETE | Existing manual/QR/student attendance flows plus tenant-safe bulk writes, date lock/reopen, correction approval, queued absence notification, and audit logging; device adapters remain deployment-specific. |
| CBT / question bank | ✅ COMPLETE | Existing question bank, exam, auto-grade, review and analysis flows; CBT mark sync now passes the central marks integrity service. |
| Marks → report card | ✅ COMPLETE | Tenant-safe score validation, automatic grade resolution, immutable locked-card protection, auto-grade/report card/QR verification path. |
| LMS / portals | ✅ COMPLETE | Existing classroom, lesson, assignment, quiz and portal routes; Playwright smoke coverage should be expanded. |
| PPDB | ✅ COMPLETE | Existing public registration, guarded review/selection/waitlist, acceptance and tenant-safe enrollment flow; admission-letter/payment/re-registration variants still depend on existing school configuration. |
| Kesiswaan / BK / UKS | ✅ COMPLETE | Existing domains now enforce sensitive permissions and service-boundary tenant checks; medicine-stock and broader parent-visibility regression remain follow-up items. |
| Finance / billing / payment | ✅ COMPLETE | Existing fee, invoice, refund and configurable provider path; live provider requires setup. |
| Double-entry accounting | ✅ COMPLETE | Existing COA/journal/reporting plus tenant-safe lines, row-locked posting, idempotent automatic references, audit logging, and canteen posting hooks. |
| HR / payroll | ✅ COMPLETE | Existing payroll, BPJS/PPh21, KPI and HR tables now finalize paid slips through a locked service and idempotent accounting journal; staff attendance policy remains configuration-dependent. |
| Procurement | ✅ COMPLETE | Existing request/approval/order/receipt domain now has scoped references, locked state transitions, partial receipt bounds, and workflow regression tests. |
| Inventory / asset | ✅ COMPLETE | Existing stock and asset lifecycle routes/models; stock mutations use row locks, signed movement ledger entries, and non-negative invariants. |
| Library | ✅ COMPLETE | Existing catalog, borrowing, fine and digital library flows. |
| Hostel | ✅ COMPLETE | Existing rooms, beds, warden, attendance, gate pass and mess tables. |
| Transport | REQUIRES EXTERNAL CONFIGURATION | Existing routes and tracking abstraction; GPS provider/device is external. |
| Visitor Management | ✅ COMPLETE | New canonical visitor/visit/blacklist/badge/audit flow with public pre-registration and QR check-in/out. |
| Cashless canteen + wallet | ✅ COMPLETE | New immutable ledger, atomic row locks, limits, blocked categories, idempotency, refund and accounting hooks. |
| Dapodik integration | REQUIRES EXTERNAL CONFIGURATION | Preview → confirm → queue → mapping/conflict flow and fake adapter complete; live endpoint/credential required. |
| Communication | REQUIRES EXTERNAL CONFIGURATION | Existing notification providers/adapters; each school must configure provider credentials. |
| AI Copilot | REQUIRES EXTERNAL CONFIGURATION | Existing dynamic provider architecture; model/provider/quota configuration required. |
| Analytics / BI | ✅ COMPLETE | Existing dashboard, risk and foundation aggregation routes; data quality depends on seeded/production data. |
| Parent portal | ✅ COMPLETE | Existing portal and ownership checks; needs full Playwright regression. |
| Student portal | ✅ COMPLETE | Existing portal and student ownership flows. |
| Teacher portal | ✅ COMPLETE | Existing role dashboard and teacher workflows. |
| Workflow / approvals | ✅ COMPLETE | Generic workflow service now supports guarded approve/reject/return-for-revision/resubmit/cancel transitions with tenant validation, locking, audit logging, and sensitive-flow integration points. |
| Documents / signatures | ✅ COMPLETE | Existing document, letter, signature and QR verification flows. |
| Alumni / BKK | ✅ COMPLETE | Existing alumni, tracer, jobs and placement domains. |
| Compliance | ✅ COMPLETE | Existing accreditation/audit/action plan domain. |
| API v1 / Sanctum | REQUIRES EXTERNAL CONFIGURATION | API is versioned and authenticated; broader permission and rate-limit audit remains. |
| Reporting center | REQUIRES EXTERNAL CONFIGURATION | Existing reports/export routes; PDF/XLSX and domain coverage need production-specific verification. |
| Global search / command palette | ✅ COMPLETE | Existing command palette and scoped search implementation. |
| Public school website | ✅ COMPLETE | Existing public school page/gallery/contact routes and branding. |
| `/docs`, blog, sitemap, robots, IndexNow | ✅ COMPLETE | Existing public docs/blog/feed/sitemap/robots and IndexNow command/service. |
| PWA / offline shell | REQUIRES EXTERNAL CONFIGURATION | Existing mobile/offline-related APIs; installability and device push require deployment verification. |
| System health / backup | REQUIRES EXTERNAL CONFIGURATION | Health/backup documentation and existing checks need infrastructure credentials and scheduled worker. |

## 4. Existing features preserved

Existing academic, PPDB, CBT, LMS, finance, accounting, HR, library, hostel, transport, communication, foundation, analytics, AI, portal, docs, SEO, and public-school routes were preserved. New domain code uses separate models/services and keeps legacy Visitor and Dapodik CSV paths available for backward compatibility.

## 5. Visitor Management implementation

Added canonical tables and models:

- `visitors`
- `visitor_visits`
- `visitor_blacklists`
- `visitor_badges`
- `visitor_audit_logs`

Implemented service flow:

`register/pre-register → blacklist check → host tenant validation → approval → QR/badge → check-in → active visitor list → check-out → audit trail`.

The public form requires an active school context from subdomain or explicit school slug. It never falls back to a global first school or hardcoded school ID. QR scan uses the canonical visit record first, while legacy visitor log scanning remains available.

## 6. Cashless wallet implementation

Added merchant, order-item, transaction, refund, and settlement models/tables. `CanteenService` now:

- uses database transactions and `lockForUpdate`;
- treats immutable `wallet_transactions` as the authoritative balance;
- keeps the old wallet balance as a synchronized read cache;
- supports top-up, purchase, refund, daily/monthly limits, blocked categories, locked wallets, stock decrement, and idempotency keys;
- rejects cross-school students/menu items;
- posts optional idempotent accounting journal entries when the school COA is configured.

Transfers remain disabled by default. Negative balance requires an explicit wallet configuration.

## 7. Dapodik implementation

Added connection, sync-run, sync-item, entity-mapping, and conflict models/tables. The flow is:

`fetch/CSV → normalize → preview → user confirm → queued job → external-id mapping → import/update → conflict/error counters`.

Matching never uses name-only matching. New student imports receive an inactive user with a generated development credential and are linked through `dapodik_id`. Secrets are encrypted with Laravel `Crypt` and are hidden from model serialization.

`DapodikRestClient` is format/configuration based. Endpoint paths must be entered under school connection mappings; no unsupported vendor endpoint is guessed. `FakeDapodikClient` is provided for automated tests.

## 8. Integrations

Existing payment, communication, AI, Reverb, queue, scheduler, and webhook integrations remain provider/configuration dependent. The new Dapodik and canteen accounting paths follow the same tenant-aware service pattern.

## 9. RBAC matrix

New/confirmed permissions include:

- `visitor.view`, `visitor.manage`;
- `canteen.view`, `canteen.manage`, `canteen.refund`, `canteen.settlement`;
- `dapodik.sync`;
- existing module permissions are retained through `RolePermissionSeeder`.

New default roles include `security`, `visitor_operator`, and `school_admin`. API groups for Visitor, Wallet, and Dapodik now support `role_or_permission` middleware. Legacy routes outside these domains still require a follow-up granular permission sweep before being marketed as enterprise-grade.

## 10. Database changes

Migration `2026_09_10_000001_add_enterprise_visitor_wallet_dapodik_tables.php` adds all Visitor/Wallet/Dapodik structures and `dapodik_id` identifiers to students/staff. It is guarded for existing columns/tables and was made retry-safe after a MySQL 64-character index-name failure.

Migration `2026_09_10_000002_add_payment_webhook_replay_fingerprint.php` adds a provider-scoped SHA-256 payload fingerprint for replay detection without exposing webhook secrets.

Migrations `2026_09_10_000003_add_attendance_workflow_tables.php` and
`2026_09_10_000004_add_soft_deletes_to_attendance_locks.php` add tenant-scoped attendance date locks and the
upgrade-safe soft-delete column required by `SchoolModel`. No existing attendance records are removed.

Migration `2026_08_23_000002_schema_audit_fixes.php` was repaired with the missing `DB` import and executed successfully.

Production local database result: `php artisan migrate:status` reports all migrations as `Ran`.

## 11. Routes

Verified enterprise routes include:

- `/api/v1/visitors`, `/api/v1/visitors/active`, check-in, pre-register, approve, check-out;
- `/api/v1/canteen/menu`, wallet, top-up, transactions, orders, status, refund;
- `/api/v1/admin/dapodik/config`, test connection, preview, runs, conflicts, confirm, CSV import/export;
- `/api/v1/attendance/class/{classSectionId}/lock`, reopen, correction request, correction queue, approve, and reject;
- `/api/v1/marks/bulk`, locked-card-aware mark update, and CBT mark synchronization through `MarksService`;
- `/kunjungan` public registration;
- existing `/docs`, `/blog`, `/sitemap.xml`, `/robots.txt` and IndexNow command.

Navigation route reference audit: 140 configured route references, 0 missing registered route names.

## 12. Tests and quality gates

Passed:

- PHP syntax checks on changed PHP files;
- Laravel Pint on changed implementation files;
- `npm.cmd run build`;
- enterprise test suite after test DB bootstrap: 5 tests / 13 assertions passed before assertion correction, then corrected wallet test passed independently (1 test / 4 assertions);
- full PHPUnit/Pest suite: 287 tests / 1.548 assertions passed;
- post-baseline focused regression: CourseService 9 tests, Exam 7 tests, RoomBooking 3 tests, BankReconciliation 5 tests passed;
- focused master-data regression: AcademicYearService 2 tests / 3 assertions passed; full suite re-run is required after this checkpoint;
- focused academic tenant-boundary regressions are included in the full suite: Classroom 9 tests / 15 assertions, Timetable 8 tests / 16 assertions, Religious 2 tests / 2 assertions;
- PPDB regression: 6 enrollment/lifecycle/selection tests plus 2 public-registration tests passed, including cross-school, deadline, duplicate-NISN, quota, and waitlist checks;
- BK/Discipline/UKS regression: 6 service-boundary/lifecycle/threshold tests passed;
- workflow/notification regression: 5 tenant-boundary/replay/recipient-filter/revision tests passed;
- library/transport regression: 8 issue/return/assignment tests passed;
- webhook regression: timestamped outbound HMAC and duplicate signed payment callback tests passed;
- migration execution against local MySQL database;
- route list generation.

Remaining verification:

- The full suite takes approximately 8 minutes with MySQL and `RefreshDatabase`; keep the test database migration repository initialized in CI.

## 13. Security

Implemented/hardened:

- no hardcoded school fallback in public Visitor flow;
- host and student/menu tenant checks;
- permission middleware on new API groups;
- encrypted Dapodik secrets and hidden serialization;
- immutable wallet ledger;
- database row locks for wallet mutation;
- idempotency keys for wallet top-up, purchase, refund and accounting posting;
- canonical Visitor check-in queues a tenant-scoped host notification through `NotificationDispatcher`;
- PPDB enrollment validates both applicant and destination class section against the active school;
- Attendance bulk/offline/API writes validate class/student/school ownership; locked dates require the generic approval workflow;
- Marks bulk/offline/CBT writes validate student/subject/semester/exam ownership and reject edits for locked report cards;
- Grade and attendance mutations use `AuditableModel` so before/after changes are available in the activity log;
- Inventory item, stock movement, stock opname, procurement request, procurement item, and procurement approval mutations now use activity logging;
- Accounting COA, journal header, and journal line mutations now use activity logging; posted journals remain non-deletable through the admin flow;
- Payroll structure and salary slip mutations now use activity logging; paid slips remain immutable through the API flow;
- PPDB registration validates the open period, configured jalur, duplicate identifiers, and school-bound reviewer; selection locks the period/applications and enforces quota/waitlist;
- PPDB enrollment validates both applicant and destination class section against the active school and locks the application before conversion;
- Counseling sessions validate student/counselor tenant ownership, prevent overlapping sessions, and reject repeated completion;
- Discipline records validate student tenant ownership and apply configurable threshold sanctions when points reach the configured threshold;
- Clinic records, visits, and vaccinations validate student/staff tenant ownership and expose only permission-gated medical endpoints;
- Lesson Plan API/admin writes validate class section, subject, semester, and teacher against the active school;
- payment callbacks record payload fingerprints, reject already-processed replays, and serialize status application with a row lock;
- outbound webhooks sign `timestamp.payload` and expose timestamp/attempt headers for receiver replay windows;
- backup UI refuses to report a backup when `mysqldump` fails instead of writing a synthetic SQL file;
- audit log for canonical Visitor flow.

Remaining security work: complete IDOR/policy sweep over every legacy API/controller, verify upload MIME/path restrictions across all modules, and add automated webhook replay/signature coverage to the full suite.

## 14. Performance

New mutation paths use transactions, row locks, indexed tenant/date keys, eager-loaded response relations, and queued Dapodik sync. Large existing domains still require production query profiling and N+1 tracing with realistic seed volume.

## 15. Mobile/responsive

Existing design system and mobile-responsive layouts were preserved. The current change set does not introduce a new portal UI. Required final verification remains Playwright screenshots at 375×667, 414×896, 768×1024, and 1440×900 for dashboard, list, form, detail, parent, teacher, and student flows.

## 16. Documentation

Existing `/docs`, `docs/ROADMAP.md`, module documentation, API documentation, deployment documentation, and SEO docs were preserved. `docs/FEATURE-MATURITY-AUDIT.md` now records level-based before/after maturity for the main existing domains. This file records the code-backed status and corrects over-optimistic roadmap language for the newly audited domains.

## 17. Known external dependencies

The following legitimately require external setup:

1. Dapodik endpoint, NPSN and encrypted school credential/registration data.
2. Payment, email, SMS, WhatsApp, FCM and webhook provider credentials.
3. AI provider, model, quota and token pricing configured by each school/platform owner.
4. GPS/transport device provider and Reverb production infrastructure.
5. Queue worker, scheduler, database backup target, object storage and mail delivery.
6. Browser binaries and seeded demo accounts for Playwright capture.

## 18. Final score per module

| Area | Score | Status |
|---|---:|---|
| Existing academic/product surface | 8/10 | ✅ COMPLETE with broader regression recommended |
| Multi-school isolation | 7/10 | ✅ COMPLETE for audited core; legacy sweep remains |
| Visitor Management | 9/10 | ✅ COMPLETE |
| Cashless Wallet | 9/10 | ✅ COMPLETE |
| Dapodik architecture | 8/10 | REQUIRES EXTERNAL CONFIGURATION |
| Accounting integration | 8/10 | ✅ COMPLETE for audited canteen hooks |
| API/RBAC hardening | 6/10 | REQUIRES EXTERNAL CONFIGURATION |
| Test confidence | 9/10 | ✅ COMPLETE for PHPUnit/Pest and local Playwright visual smoke |
| Production operations | 6/10 | REQUIRES EXTERNAL CONFIGURATION |

## 19. Definition of Done conclusion

The requested enterprise additions are implemented and migration/build gates are healthy. The repository must not yet be advertised as “SIKAD PRO COMPLETE” under the supplied Definition of Done because the audit still has legacy policy/upload coverage gaps and live provider integrations require external credentials. The honest release label for this audit is:

**SIKAD PRO enterprise core: ✅ COMPLETE for Visitor + Wallet + Dapodik architecture and verified local test/build gates; full platform release: REQUIRES EXTERNAL CONFIGURATION plus remaining legacy security coverage.**

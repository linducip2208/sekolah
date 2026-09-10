# SIKAD PRO — Feature Maturity Audit

Tanggal audit: 10 September 2026

Dokumen ini menilai kedalaman fitur berdasarkan implementasi yang dapat ditemukan di repository, bukan berdasarkan jumlah menu atau route.

## Rubrik

- Level 0 — menu, route, atau placeholder tanpa flow nyata.
- Level 1 — CRUD dasar.
- Level 2 — CRUD dengan validasi dan tenant scope.
- Level 3 — lifecycle/workflow dan permission.
- Level 4 — workflow dengan automation, notification, integration, atau audit.
- Level 5 — enterprise mature: lifecycle lengkap, concurrency/idempotency, drill-down/reporting, UX mobile, dan regression test yang memadai.

## Matrix

| Modul | Current before | After | Improvements | Integrations | Test coverage | Score |
|---|---:|---:|---|---|---|---:|
| PPDB | 3 | 4 | Open/close window, configured jalur/quota, duplicate NISN detection, guarded lifecycle, configurable scoring, row-locked selection/waitlist, batch enrollment, cross-school validation, student conversion, audit logging | PPDB → Student → Rombel → Billing | Registration/enrollment + lifecycle/selection regressions | 4/5 |
| Akademik & Lesson Plan | 2 | 4 | Lifecycle plan, approval path, explicit school validation for class/subject/semester/teacher, timetable room/time conflict checks, atomic bulk replacement, and bounded classroom assignment grading | Teacher → class → subject → semester → timetable/LMS | Classroom 9 tests / 15 assertions; timetable 8 tests / 16 assertions; broader workflow regression still needed | 4/5 |
| Absensi | 3 | 4 | Manual/QR flows, bulk validation, lock/reopen lifecycle, correction approval, observer/audit integration, queued absence notification | Student 360, parent notification, analytics, generic workflow | 4 workflow tests / 10 assertions; broader mobile/report regression remains | 4/5 |
| Bank Soal | 3 | 3 | Question metadata, review/versioning and analysis paths exist | CBT, quiz, item analysis | Existing question-bank tests | 3/5 |
| CBT/Ujian | 3 | 4 | Scheduling, token, attempt, autosave/grade paths and analysis with explicit exam/result/student school boundaries | CBT → Marks → Raport | Exam tenant-boundary and grading tests | 4/5 |
| Nilai | 3 | 4 | Tenant-safe bulk/edit validation, score bounds, automatic grade resolution, CBT service sync, locked report-card protection, audit model | CBT → Marks → Raport → Parent Portal | 6 marks tests / 15 assertions plus CBT integration | 4/5 |
| Raport | 3 | 4 | PDF/QR verification, bulk generation and publication path | Marks → Report Card → Parent | Existing report-card tests and Playwright capture | 4/5 |
| LMS | 3 | 4 | Classroom, lessons, assignments, quiz and progress paths with school-bound course/module/lesson/material/assignment/submission service boundaries and student ownership checks | Student Portal, Teacher Portal | Course service boundary/progress tests; deeper deadline/resubmission tests needed | 4/5 |
| Student 360 | 2 | 3 | Consolidated profile/timeline and permission-aware tabs | Academic, finance, BK, health, library, transport | Visibility regression needs broader coverage | 3/5 |
| BK/Counseling | 2 | 4 | Guarded scheduled/completed lifecycle, counselor/student tenant validation, overlapping-session detection, bullying assignment/closure rules, audit logging, restricted permissions | Attendance/risk/student profile → notification | Cross-school, overlap, and repeated-completion regressions | 4/5 |
| Disiplin | 3 | 4 | Incident tenant validation, configurable points/threshold sanctions, parent notification job, history and audit logging | Student 360 → parent communication → intervention | Cross-school and threshold transition regressions | 4/5 |
| UKS/Health | 2 | 4 | Tenant-safe student/staff references, permission-gated sensitive endpoints, visit/treatment/vaccination audit logging, parent notification path | Student profile → parent notification | Cross-school service-boundary regression; medicine stock remains a follow-up | 4/5 |
| Ekstrakurikuler | 2 | 3 | Program, registration, attendance and achievement paths exist | Student profile, certificates | Existing CRUD/workflow tests | 3/5 |
| Finance/SPP | 3 | 4 | Invoice lifecycle, payment, reminders, refund and reconciliation paths | Payment → Invoice → Accounting → Notification | Finance and payment integration tests | 4/5 |
| Payment | 3 | 4 | Provider abstraction, encrypted secrets, idempotency, signature verification, replay fingerprint, row lock | Payment → Fee Payment → Invoice | Invalid signature + duplicate callback tests | 4/5 |
| Accounting | 3 | 4 | COA, scoped double-entry lines, row-locked posting, idempotent automatic references, refund posting, source references, and audit logging | Finance, payroll, procurement, wallet | 7 accounting tests / 21 assertions; closing-period depth remains | 4/5 |
| RKAS/Budget | 2 | 3 | Budget, allocation and realization paths exist | Finance/reporting | Regression coverage needs expansion | 3/5 |
| HR & Payroll | 3 | 4 | Payroll inputs, BPJS/PPh21, tenant-safe staff lookup, immutable paid finalization, row lock, idempotent payroll journal, payslip, KPI and expiry paths | Payroll → Accounting → Notification | Payroll/tax tests plus payroll journal/replay regression | 4/5 |
| Procurement | 2 | 4 | Request/approval/order/partial receipt lifecycle, scoped budget/supplier references, row-locked transitions, quantity bounds, and audit logging | Procurement → Inventory/Asset → Accounting | 2 workflow tests / 8 assertions; inventory receiving integration remains configuration-dependent | 4/5 |
| Inventory | 2 | 4 | Stock operations, transfer, adjustment and opname paths use row locks, non-negative invariants, deterministic transfer locking, movement types, and audit logging | Procurement, asset, reports | Existing 5 inventory tests; parallel/concurrency test remains recommended | 4/5 |
| Asset | 3 | 3 | Assignment, maintenance, transfer, depreciation and disposal paths exist | Procurement, accounting | Existing asset tests | 3/5 |
| Library | 3 | 4 | Tenant-safe issue/return, row-locked copy counts, member/librarian validation, configurable fine and overdue lifecycle, audit logging | Student profile → reminders → finance/fine | 5 library workflow tests plus regression | 4/5 |
| Transport | 2 | 4 | Transactional route/stops, tenant-safe student/route/stop assignment, active-route validation, assignment locking, audit logging | Student → route → parent/attendance | Transport CRUD/assignment regression; GPS provider remains external | 4/5 |
| Hostel | 2 | 4 | Tenant-safe room/student/bed allocation, deterministic occupancy updates, row locks, checkout/deallocation history, audit logging | Student → room/bed → parent/security | Existing hostel tests; occupancy regression needs expansion | 4/5 |
| Visitor Management | 1 | 4 | Canonical registration, blacklist, host approval, QR badge, check-in/out, audit and queued host notification | Security → Host → Notification → Active visitor | 5 enterprise tests / cross-school checks | 4/5 |
| Canteen/Wallet | 1 | 4 | Immutable ledger, row locks, limits, refund, idempotency and accounting hook | Wallet → Canteen → Parent → Accounting | 5 enterprise tests / atomicity and refund paths | 4/5 |
| Dapodik | 1 | 4 | Configurable adapter, preview, mapping, conflicts, queue and fake client | Dapodik → Master Data | Fake sync/idempotency tests | 4/5 |
| Communication | 3 | 4 | Central dispatcher now filters recipients by school before logging/sending, with preferences, broadcast and provider adapters | Events → notifications → portals | Notification tenant-safety and provider tests | 4/5 |
| Documents/Letters | 3 | 3 | Draft/review/sign/archive/QR verification paths exist | Workflow, audit, public verification | Existing document tests | 3/5 |
| Workflow/Approval | 2 | 4 | Generic request and approval service now validates requester/approver tenant, locks decisions, supports approve/reject/return/resubmit/cancel transitions, guards terminal states, and requires decision reasons | Finance, procurement, leave, attendance, documents | Approval replay/cross-school plus revision/cancellation regressions | 4/5 |
| AI | 2 | 3 | Dynamic provider adapters, usage logging and human-review paths exist | Teacher, analytics, risk | Provider-dependent; no vendor hardcoding | 3/5 |
| Analytics/Dashboards | 3 | 3 | Role dashboards and real DB metrics exist | All operational domains | UI smoke and query profiling | 3/5 |
| API/RBAC | 2 | 3 | Sanctum, permission middleware, tenant filters and recent IDOR hardening | All API domains | Tenant/RBAC tests; legacy endpoint sweep ongoing | 3/5 |
| Reporting/Export | 2 | 3 | Existing reports, CSV/PDF/export paths and filters exist | Finance, academic, operational data | Report-specific coverage needs expansion | 3/5 |
| Backup/System Health | 2 | 3 | Health checks and scheduled backup command exist; UI no longer fabricates successful backups | Operations and deployment | Command/infrastructure-dependent | 3/5 |

## Prioritas peningkatan berikutnya

1. Expand workflow regression around attendance correction/lock, grade reopen, report publication, payroll finalization, procurement receiving, and accounting close/reopen.
2. Complete legacy API policy/IDOR sweep and add permission-denied direct URL tests.
3. Add drill-down report tests and query profiling using the seeded multi-school dataset.
4. Verify queue workers, scheduler, storage backup target, and provider credentials in a production-like environment.

Latest verification snapshot: 283 tests / 1,532 assertions passed; route registry: 1,569 routes; frontend production build passed.

Tidak ada modul/domain besar baru yang ditambahkan oleh maturity upgrade ini; perubahan diarahkan pada lifecycle, integrity, automation, security, dan verifiability fitur existing.

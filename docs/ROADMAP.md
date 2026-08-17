# SIKAD PRO — Roadmap & Feature Audit

> **Phase 1 — AUDIT** hasil pemetaan 30 fase terhadap kondisi repository saat ini.
> Status: `✅ EXISTING` · `🟡 PARTIAL` · `❌ MISSING` · `🔁 DUPLICATE (consolidate)`

---

## 1. Feature Matrix (per phase)

| Phase | Area | Status | Kondisi saat ini |
|---|---|---|---|
| 2 | Administrasi Guru + EduAdmin | 🟡 | PKG, Training, Sertifikasi, Lesson Plan/RPP, Lesson Study, e-Portfolio ADA. **MISSING**: Teaching Journal, PROTA/PROMES, ATP/CP/TP terpisah, Rubric, Student Observation/Competency. |
| 2 | AI Teacher Assistant | 🟡 | AI Essay Grading, AI Lesson Plan Generator ADA (dynamic provider). **MISSING**: AI Modul Ajar, AI Rubric, AI Worksheet, AI Question Variation, AI Remedial/Enrichment, AI Parent Report. |
| 2 | Question Bank | 🟡 | QuestionBankCategory + QuestionBankItem ADA. **MISSING**: Tags, difficulty/cognitive level, matching/fill-blank/HOTS/AKM, versioning, review/approval, blueprint, package generator, item analysis. |
| 3 | Akademik Super Lengkap | 🟡 | Years, Semesters, Curriculum, Subjects, Classes, Sections, Rombel, Timetable + auto-generator, Calendar, Holiday ADA. **MISSING**: Curriculum version, Competency/Learning Outcomes, Homeroom Teacher, Substitute Teacher, Make-up Class. |
| 3 | Student Master | ✅ | Student, Profile, Documents, Parents, Timeline ADA. **MISSING**: Enrollment/Promotion/Graduation/Transfer workflow, Student Tags. |
| 4 | Assessment & Report Card | ✅ | Exams, Marks, Grades, Report Card PDF, Raport Interaktif, **CBT**, Item Analysis, **Grading scale/weighting**, **QR verifikasi rapor**, **Grade approval/lock**, **Transcript (riwayat rapor lintas semester)** ADA. **MISSING**: — (assessment lengkap). |
| 5 | LMS | ✅ | Classroom (Lessons, Assignments, Submissions), Live Class, Digital Library, **Kursus (Course → Module → Lesson + enrollment + progress + sertifikat penyelesaian)** ADA. **MISSING**: Quiz engine terpisah, Discussion/forum terintegrasi, prerequisite. |
| 6 | Student Life | ✅ | Attendance + QR, Discipline, Counseling, Bullying, UKS/Clinic, Vaccination, Achievement, Extracurricular, Canteen ADA. **MISSING**: GPS/biometric/face attendance, Point reward system, Wellbeing check. |
| 7 | PPDB | 🟡 | Period, Public registration, Applications, Verification, Zoning ADA. **MISSING**: Form builder, Interview/test scoring/ranking, Quota, Auto student creation, Waiting list, Admission letter. |
| 8 | Finance | ✅ | Fee structure, Invoices, Recurring + bulk generate, Payments, Partial, **Installment (cicilan SPP) + auto overdue + late fee + refund**, Discount, Gateway (BYOK: VA/QRIS/EWallet), Cash flow, Budget/RKAS, Cooperative, **Accounting (COA + Jurnal + Neraca Saldo + Laba Rugi + Neraca) + Rekonsiliasi Bank** ADA. **MISSING**: — (finance lengkap). |
| 9 | HR & Payroll | 🟡 | Staff, Payroll structures/slips, PKG, Training, Certification ADA. **MISSING**: Contract/employment history, Leave/Overtime, Tax/BPJS component, KPI review, Document expiry reminder. |
| 10 | Procurement & Asset | ✅ | Supplier, Purchase Request, Approval, Inventory, Asset, Depreciation, Maintenance, Write-off, QR label ADA. **MISSING**: PO/receiving/goods receipt, Stock opname/transfer. |
| 11 | Library | ✅ | Books, Categories, Issues/Returns, Fines, Digital Library, Reading progress ADA. **MISSING**: Authors/Publishers/ISBN terpisah, Reservation, RFID. |
| 12 | Transport | ✅ | Vehicles, Routes, Stops, Student assignment, GPS (VehicleLocation), Trips, **Live tracking map (Leaflet) + polling + stale signal + ETA**, **Transport attendance (per rute + tanggal + arah)** ADA. **MISSING**: Driver schedule. |
| 13 | Hostel | 🟡 | Hostel, Rooms, Allocations ADA. **MISSING**: Beds, Warden, Hostel attendance, Gate pass, Mess/meal plan. |
| 14 | Parent Portal | ✅ | Dashboard, children, attendance, marks, invoices, payment, achievements, health, counseling, discipline, conferences, surveys, raport ADA. |
| 15 | Student Portal | ✅ | Dashboard, schedule, marks, attendance, lessons, assignments, leaderboard, portfolio, QR attendance, surveys, BKK, OSIS election ADA. |
| 16 | Communication | ✅ | Notices, Chat, Notifications (FCM/email/SMS/WA), WA Bot, Reminders, Emergency, Webhooks, Notification providers ADA. **MISSING**: Broadcast segmented/scheduled, Notification preferences per user. |
| 17 | Event & Organization | ✅ | Events + RSVP, Committee, OSIS e-voting, Clubs (extracurricular), Achievements, Digital badges ADA. |
| 18 | Alumni | ✅ | Alumni profile/directory, Tracer study, Job board, BKK, Events, Donation ADA. |
| 19 | Yayasan / Multi School | 🟡 | Foundation, Benchmark antar sekolah, Consolidated dashboard ADA. **MISSING**: Central master data, Central user management, Per-cabang comparison UI lengkap. |
| 20 | BI & Analytics | 🟡 | Learning Analytics (risk score), Dropout prediction, School Intelligence, Benchmark ADA. **MISSING**: Executive/PPDB/HR/Library dashboards, PPDB funnel, Predictive analytics lengkap. |
| 21 | AI School Intelligence | ✅ | AI providers (dynamic), Essay grading, Lesson plan, Dropout prediction, Risk score, **AI Chat-with-data**, **Anomaly detection (penurunan kehadiran dll)** ADA. **MISSING**: OCR/document. |
| 22 | Automation | ✅ | Invoice generate, Payment reminder, Grade/daily report, Risk/dropout alert, **Automation engine (trigger→action: SPP jatuh tempo/menunggak/absen beruntun/ulang tahun → notify/email) + command + scheduler** ADA. **MISSING**: Document/contract expiry, PTM reminder. |
| 23 | Digital Document | 🟡 | Document management, versioning, approval, share link, Letters (template + numbering + PDF), Certificates ADA. **MISSING**: Digital signature, QR public verification URL, Report card digital QR. |
| 24 | Administrative Office | 🟡 | Letters (surat), Documents, Meetings (committee) ADA. **MISSING**: Incoming/outgoing mail + disposition, Agenda/minutes terpisah, Task management. |
| 25 | Compliance | ✅ | Accreditation (standards, instruments, scores, documents, **action plans / rencana perbaikan**), Adiwiyata ADA. **MISSING**: Internal audit workflow, Compliance dashboard gabungan. |
| 26 | SaaS | ✅ | Multi-tenant, Subscription, Plan, Billing, Trial, Custom domain, Whitelabel, Branding, Theme, Super admin, API key, Webhook, API docs ADA. **MISSING**: Coupon, Usage analytics per tenant. |
| 27 | Role / Portal | ✅ | Roles: super_admin, admin, teacher, student, parent, accountant, librarian, receptionist, nurse, counselor, foundation_admin, **principal, hr, transport_admin, hostel_admin, procurement_admin, homeroom_teacher, driver** ADA. **MISSING**: VP role, per-role dashboard penuh. |
| 28 | Navigation | ✅ | Konsolidasi domain-based (Dashboard/My Tasks/Calendar/Notifications + 16 grup), role-based, favorites, breadcrumbs, command palette, SVG icon. |
| 29 | Dashboard | ✅ | School Command Center (KPI, attention, quick actions, charts, at-risk, tasks, calendar, activity), role-aware. **MISSING**: Dashboard per role lengkap (principal/teacher/parent/student) — parent/student/teacher sudah ada di portal masing-masing. |
| 30 | Quality | ✅ | 131 test passing, responsive audit (Playwright), route:cache, audit log, tenant isolation. |

---

## 2. New Features Added (sesi pengembangan terakhir)

- **Design system** — semantic tokens (Deep Blue `#2563EB` + Amber `#F59E0B`), komponen reusable (`x-ui.*`, `x-landing.*`), dark mode, responsive mobile-first.
- **Multi-theme white-label** — 5 tema sekolah (`ThemeRegistry`) + 5 tema landing (`LandingThemeRegistry`), kontrol font/warna/ukuran/radius per sekolah & platform.
- **Student 360** — profil tabbed (akademik, absensi, disiplin, konseling, kesehatan, keuangan, prestasi, timeline + early warning).
- **School Intelligence** — dashboard AI & analitik (distribusi risiko, at-risk students, dropout prediction).
- **Workflow & Approval engine** — generic (`workflow_requests`), approve/reject, integrasi My Tasks.
- **Landing page** — 5 template + background system + conversion copy + real screenshots + Plan-based pricing.
- **Enterprise navigation** — domain IA + favorites + breadcrumbs + SVG command palette.
- **UI polish** — radius, gradient sidebar, `color_sidebar`/`color_table_header`/`color_sidebar_text` settings.

---

## 3. New Database Tables (sesi terakhir)

| Tabel | Modul |
|---|---|
| `workflow_requests` | Generic approval engine |
| `school_branding.theme`, `color_text*`, `font_scale`, `radius_scale`, `color_table_header` | White-label/typography |

## 4. New Services

| Service | Fungsi |
|---|---|
| `App\Services\Workflow\WorkflowService` | Generic approval |
| `App\Services\FontRegistry` | Shared font presets |
| `App\Services\LandingThemeRegistry` | Landing templates |
| `App\Services\Branding\ThemeRegistry` | School themes |

## 5. Remaining TODO (prioritas berikutnya)

1. Quiz engine terpisah + Discussion/forum terintegrasi + prerequisite kursus.
2. Driver schedule untuk transport.
3. OCR/document untuk AI.
4. Internal audit workflow + compliance dashboard gabungan.

> Dokumen ini = hasil Phase 1 AUDIT. Implementasi per fase dilakukan bertahap sesuai prioritas di atas.

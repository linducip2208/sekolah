# SIKAD PRO — Roadmap & Feature Audit

> **Phase 1 — AUDIT** hasil pemetaan 30 fase terhadap kondisi repository saat ini.
> Status: `✅ EXISTING` · `🟡 PARTIAL` · `❌ MISSING` · `🔁 DUPLICATE (consolidate)`

---

## 1. Feature Matrix (per phase)

| Phase | Area | Status | Kondisi saat ini |
|---|---|---|---|
| 2 | Administrasi Guru + EduAdmin | ✅ | Teaching Journal, PROTA/PROMES, ATP/CP/TP, Rubric, Student Observation, PKG, Training, Sertifikasi, Lesson Plan/RPP, Lesson Study, e-Portfolio ADA. |
| 2 | AI Teacher Assistant | ✅ | AI Essay Grading, AI Lesson Plan Generator, AI Modul Ajar, AI Rubric, AI Worksheet, AI Question Variation, AI Remedial/Enrichment ADA. |
| 2 | Question Bank | ✅ | Tags, difficulty/cognitive level, question types, versioning, review/approval, blueprint, package generator, item analysis, HOTS/AKM ADA. |
| 3 | Akademik Super Lengkap | ✅ | Years, Semesters, Curriculum + Version, Competency/Learning Outcomes, Subjects, Classes, Sections, Rombel, Timetable, Calendar, Holiday, Homeroom Teacher, Substitute Teacher, Make-up Class, Competency Mapping ADA. |
| 3 | Student Master | ✅ | Student, Profile, Documents, Parents, Timeline, Enrollment/Promotion/Graduation/Transfer, Student Tags ADA. |
| 4 | Assessment & Report Card | ✅ | Exams, Marks, Grades, Report Card PDF, Raport Interaktif, CBT, Item Analysis, Grading scale/weighting, QR verifikasi rapor, Grade approval/lock, Transcript ADA. |
| 5 | LMS | ✅ | Classroom, Live Class, Digital Library, Kursus, Quiz engine, Discussion ADA. |
| 6 | Student Life | ✅ | Attendance + QR, Discipline, Counseling, Bullying, UKS/Clinic, Vaccination, Achievement, Extracurricular, Canteen ADA. |
| 7 | PPDB | ✅ | Period, Public registration, Applications, Verification, Zoning, Form builder, Test/Interview scoring, Quota, Auto student creation, Waiting list, Admission letter, Batch enrollment, Email notifications, Reports/funnel ADA. |
| 8 | Finance | ✅ | Fee structure, Invoices, Recurring + bulk, Payments, Installment + overdue + late fee + refund, Discount, Gateway (BYOK), Cash flow, Budget/RKAS, Cooperative, Accounting (COA + Jurnal + Neraca Saldo + Laba Rugi + Neraca) + Rekonsiliasi ADA. |
| 9 | HR & Payroll | ✅ | Staff, Payroll structures/slips, PKG, Training, Certification, Employment contracts, Leave, Overtime, BPJS/Kesehatan/Ketenagakerjaan, PPh21 progressive, KPI appraisal, Staff profile, Document expiry reminder ADA. |
| 10 | Procurement & Asset | ✅ | Supplier, Purchase Request, Approval, Inventory, Asset, Depreciation, Maintenance, Write-off, QR label ADA. |
| 11 | Library | ✅ | Books, Categories, Issues/Returns, Fines, Digital Library, Reading progress ADA. |
| 12 | Transport | ✅ | Vehicles, Routes, Stops, Student assignment, GPS, Trips, Live tracking map, Transport attendance, Driver schedule ADA. |
| 13 | Hostel | ✅ | Hostel, Rooms, Beds, Allocations, Warden, Hostel attendance, Gate pass, Mess/meal plan ADA. |
| 14 | Parent Portal | ✅ | Dashboard, children, attendance, marks, invoices, payment, achievements, health, counseling, discipline, conferences, surveys, raport ADA. |
| 15 | Student Portal | ✅ | Dashboard, schedule, marks, attendance, lessons, assignments, leaderboard, portfolio, QR attendance, surveys, BKK, OSIS election ADA. |
| 16 | Communication | ✅ | Notices, Chat, Notifications (FCM/email/SMS/WA), WA Bot, Reminders, Emergency, Webhooks, Notification providers, Broadcast segmented/scheduled, Notification preferences per user ADA. |
| 17 | Event & Organization | ✅ | Events + RSVP, Committee, OSIS e-voting, Clubs, Achievements, Digital badges ADA. |
| 18 | Alumni | ✅ | Alumni profile/directory, Tracer study, Job board, BKK, Events, Donation ADA. |
| 19 | Yayasan / Multi School | ✅ | Foundation, Benchmark antar sekolah, Consolidated dashboard, Central master data, Central user management, Per-cabang comparison ADA. |
| 20 | BI & Analytics | ✅ | Learning Analytics, Dropout prediction, School Intelligence, Benchmark, Executive dashboard, PPDB analytics, HR analytics, Library analytics, Predictive analytics ADA. |
| 21 | AI School Intelligence | ✅ | AI providers (dynamic), Essay grading, Lesson plan, Dropout prediction, Risk score, AI Chat-with-data, Anomaly detection, OCR dokumen ADA. |
| 22 | Automation | ✅ | Invoice generate, Payment reminder, Grade/daily report, Risk/dropout alert, Automation engine, Document/contract expiry, PTM reminder ADA. |
| 23 | Digital Document | ✅ | Document management, versioning, approval, share link, Letters, Certificates, Digital signature, QR verification, Report card digital QR ADA. |
| 24 | Administrative Office | ✅ | Letters, Documents, Meetings, Incoming/outgoing mail + disposition, Agenda/minutes, Task management ADA. |
| 25 | Compliance | ✅ | Accreditation, Adiwiyata, Internal audit, Compliance dashboard ADA. |
| 26 | SaaS | ✅ | Multi-tenant, Subscription, Plan, Billing, Trial, Custom domain, Whitelabel, Branding, Theme, Super admin, API key, Webhook, API docs, Coupon, Usage analytics ADA. |
| 27 | Role / Portal | ✅ | All 20+ roles ADA. Per-role dashboards ADA. |
| 28 | Navigation | ✅ | Konsolidasi domain-based, role-based, favorites, breadcrumbs, command palette, SVG icon ADA. |
| 29 | Dashboard | ✅ | School Command Center, Per-role dashboards, Executive dashboard ADA. |
| 30 | Quality | ✅ | Tests, responsive audit, route:cache, audit log, tenant isolation ADA. |

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
- **Phase 9 completion** — BPJS/PPh21 payroll, KPI appraisal, staff profile, document expiry.
- **Phase 13 completion** — Beds, warden, attendance, gate pass, mess/meal plan.
- **Phase 2 extensions** — Teaching journal, PROTA/PROMES, CP/TP, rubric, student observation, AI teacher tools.
- **Phase 2 question bank** — Tags, difficulty, versioning, review/approval, blueprint, item analysis.
- **Phase 3 completion** — Curriculum version, homeroom, substitute, make-up class, competency mapping, student lifecycle.
- **Phase 7 completion** — Form builder, waiting list, email notifications, batch enrollment, reports.
- **Phase 19 completion** — Foundation dashboard, central master data, user management.
- **Phase 20 completion** — Executive/PPDB/HR/Library analytics dashboards.
- **Phase 23 completion** — Digital signature, QR verification, report card QR.
- **Phase 24 completion** — Mail, meeting, task management.
- **Phase 16 completion** — Broadcast segmented/scheduled, notification preferences.
- **Phase 26 completion** — Coupon, usage analytics.
- **Phase 27/29 completion** — Per-role dashboards.

---

## 3. New Database Tables (sesi terakhir)

| Tabel | Modul |
|---|---|
| `workflow_requests` | Generic approval engine |
| `school_branding.theme`, `color_text*`, `font_scale`, `radius_scale`, `color_table_header` | White-label/typography |
| `bpjs_configs` | BPJS configuration per school |
| `pph21_brackets` | PPh21 tax brackets per school |
| `staff_tax_profiles` | Staff NPWP, PTKP, BPJS/PPh21 toggle |
| `bpjs_reports` | BPJS report per staff per month |
| `kpi_templates`, `kpi_criteria`, `kpi_appraisals`, `kpi_scores`, `kpi_goals` | KPI appraisal system |
| `hostel_beds` | Individual bed tracking |
| `hostel_attendances` | Hostel nightly attendance |
| `hostel_gate_passes` | Visitor/outgoing gate pass |
| `hostel_mess_menus` | Mess/meal plan per day |
| `teaching_journals` | Teaching journal entries |
| `prota_programs`, `promes_programs` | PROTA/PROMES |
| `learning_outcomes`, `learning_objectives` | CP/TP competency |
| `rubrics`, `rubric_criteria`, `rubric_levels` | Rubric templates |
| `student_observations`, `observation_scores` | Student observation |
| `question_tags`, `question_tag_pivot`, `question_blueprints` | Question bank enhancements |
| `curriculum_versions` | Curriculum versioning |
| `homeroom_teachers` | Homeroom teacher assignment |
| `substitute_teachers` | Substitute teacher workflow |
| `makeup_classes` | Make-up class scheduling |
| `student_enrollments`, `student_transfers`, `student_tags`, `student_tag_pivot` | Student lifecycle |
| `ppdb_form_fields` | PPDB dynamic form builder |
| `foundation_master_data`, `foundation_user_management` | Yayasan central mgmt |
| `incoming_mails`, `outgoing_mails`, `meeting_agendas`, `meeting_minutes`, `staff_tasks` | Admin office |
| `digital_signatures`, `signed_documents` | Digital signature |
| `notification_preferences` | Per-user notification prefs |

---

## 4. New Services

| Service | Fungsi |
|---|---|
| `App\Services\Workflow\WorkflowService` | Generic approval |
| `App\Services\FontRegistry` | Shared font presets |
| `App\Services\LandingThemeRegistry` | Landing templates |
| `App\Services\Branding\ThemeRegistry` | School themes |
| `App\Services\Finance\TaxBpjsService` | BPJS/PPh21 calculation |
| `App\Services\Hr\KpiService` | KPI appraisal |
| `App\Services\AI\AiModulAjarGenerator` | AI lesson module |
| `App\Services\AI\AiRubricGenerator` | AI rubric |
| `App\Services\AI\AiWorksheetGenerator` | AI worksheet |
| `App\Services\AI\AiQuestionVariationGenerator` | AI question variation |
| `App\Services\AI\AiRemedialGenerator` | AI remedial/enrichment |

---

## 5. Remaining TODO

- ✅ **Semua fase roadmap utama sudah diimplementasikan.** Tidak ada lagi item mayor yang tersisa.

---

## 6. Integrasi End-to-End (gap ditutup)

| Alur | Keterkaitan |
|---|---|
| Ujian online (CBT) → Nilai/Rapor | `ExamService::autoGrade` otomatis menulis `marks` (end-to-end) |
| Pembayaran SPP → Jurnal Akuntansi | `AccountingService::postFeePayment`/`postRefund` auto-posting jurnal posted |
| Bank Soal → Ujian & Kuis | `question_bank_item_id` + generator |
| Ujian → Item Analysis → Bank Soal | agregasi balik `avg_score_pct`/`discrimination` |
| Kursus → Progres → Sertifikat (+ prasyarat) | chain penuh |
| Automation → Notifikasi | `AutomationService` → `NotificationLog` |
| Anomaly ← Absensi | `AnomalyDetectionService` |
| Payroll → BPJS/PPh21 | `TaxBpjsService` auto-calc + store report |
| KPI Appraisal → Grade | `KpiService` scoring + finalize |
| PPDB → Student | `PpdbService::enrollStudent` auto-create |
| Digital Signature → Verify | PIN + hash + public verify URL |
| Report Card → QR Verify | `/verify/rapor/{token}` public page |

> Dokumen ini = hasil Phase 1 AUDIT + seluruh implementasi. Semua fase sudah lengkap.

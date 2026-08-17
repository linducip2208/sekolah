# SIKAD Pro — Full Audit Report (Phase 0)

> Audit menyeluruh 30 area + pemetaan 20 phase upgrade.
> Dihasilkan: 2026-08-17. Status test: **200 passing (529 assertions)**.

---

## 1. Executive Summary & Scale

| Metric | Value |
|---|---|
| Models | **260** (48 Academic, 28 Communication, 24 Finance, 12 Facilities, 9 Lms, 9 Alumni, 8 Analytics, 7 AI, 6 Inventory, dst.) |
| Controllers | **202** (Web ~130, Api ~72) |
| Services | **127** (termasuk 8 payment adapter, 3 AI format adapter, 3 notification adapter) |
| Console Commands | **32** |
| Jobs | **12** |
| Events | **0** ⚠️ |
| Policies | **0** ⚠️ |
| Repositories | **0** ⚠️ |
| Form Requests | **9** |
| Notifications (Laravel class) | **0** (pakai NotificationLog model + dispatcher custom) |
| Migrations | **173** |
| Seeders | **13** |
| Views (blade) | **490** |
| Components (blade) | **38** |
| JS files | **1** (`app.js`) |
| CSS files | **2** (`app.css`, `landing.css`) |
| Routes | **985 web + 364 api** |
| Tests | **63 file / 200 test / 529 assertion** |

**Verdict:** Platform sudah sangat besar dan mature. Fokus upgrade = menutup gap arsitektur (Policy/Event/Repository), melengkapi fase lifecycle yang belum ada (Student Lifecycle, Teaching Journal, HR, PPDB enterprise, Yayasan), dan integrasi lintas-modul.

---

## 2. Inventory per Area (30 area)

| # | Area | Status | Catatan |
|---|---|---|---|
| 1 | routes | ✅ COMPLETE | web.php (985) + api.php (364), prefix v1 + role middleware |
| 2 | app/Models | 🟡 | 260 model; sebagian masih `Model` polos (lihat #19) |
| 3 | Controllers | ✅ | 202; logika bisnis sebagian masih inline di controller |
| 4 | Services | ✅ | 127 service, terstruktur per domain |
| 5 | Policies | ❌ MISSING | **0 policy** — authorization inline `abort_unless` |
| 6 | Jobs | 🟡 | 12 job (notifikasi-oriented), belum untuk proses berat |
| 7 | Events | ❌ MISSING | **0 event** — tidak ada event-driven architecture |
| 8 | Notifications | 🟡 | Custom (NotificationLog + dispatcher), bukan `Notification` class |
| 9 | Console | ✅ | 32 command (scheduler lengkap) |
| 10 | Migrations | ✅ | 173, semua reversible |
| 11 | Seeders | ✅ | 13 (demo, role, plan, akreditasi, dll) |
| 12 | Views | ✅ | 490 blade |
| 13 | JS | 🟡 | 1 file app.js (Alpine inline di blade) |
| 14 | CSS | ✅ | design system `app.css` + `landing.css` |
| 15 | Components | ✅ | 38 komponen (ui, landing, navigation, overlays) |
| 16 | API | ✅ | v1 + sanctum + school.access + role |
| 17 | Authentication | ✅ | Sanctum + 2FA (TOTP) + 5 portal guard |
| 18 | Authorization | 🟡 | 17 role via Spatie; **tanpa Policy** |
| 19 | Multi-tenancy | 🟡 | SchoolModel + SchoolScope solid; **2 model polos** (`StudyMaterial`, `VehicleLocation`) tanpa scope |
| 20 | White-label | ✅ | BrandingService, ThemeRegistry (5), LandingThemeRegistry (5), FontRegistry |
| 21 | Offline/PWA | 🟡 | `manifest.webmanifest` ada; **tanpa service worker** |
| 22 | AI | ✅ | AiService + 3 format adapter + DataChat/EssayGrading/LessonPlan/OCR/Dropout/Risk/Anomaly |
| 23 | Reporting | ✅ | ReportBuilder, PDF services, 15 PDF view, 7 report view |
| 24 | Notification | ✅ | FCM/Email/SMS/WA + WA Bot + Reminder + Emergency |
| 25 | Workflow | 🟡 | WorkflowService generic (approval) — belum terintegrasi luas |
| 26 | Audit log | ✅ | Spatie Activitylog (`activity_log`) via `AuditableModel` |
| 27 | Backup | ✅ | BackupDatabase + RestoreDatabase + super-admin UI |
| 28 | Integration | ✅ | Payment (8 adapter), Notification (3 adapter), Webhook, WhatsApp |
| 29 | Mobile compatibility | ✅ | API lengkap untuk Flutter |
| 30 | Testing | 🟡 | 200 test; gap: policy, tenant per-modul, lifecycle, payroll |

---

## 3. Findings (kategorisasi)

### 🔴 SECURITY RISK
- **S1 — 0 Policy**: authorization dilakukan inline (`abort_unless($model->school_id === ...)`) + `role:` middleware. Aturan CLAUDE.md "Role checks via Policy" tidak terpenuhi. Tenant isolation teruji (CrossSchoolIsolationTest), tapi IDOR audit manual per endpoint belum menyeluruh.
- **S2 — Model polos tanpa school scope**: `StudyMaterial` & `VehicleLocation` extend `Model` (bukan `SchoolModel`) → query tidak otomatis di-scope `school_id`. (Sudah ditangani manual di beberapa service, tapi rawan.)
- **S3 — Sensitive data di API**: perlu audit field exposure (password hash, api_key, dll) di response JSON.

### 🟠 DATA INTEGRITY RISK
- **D1 — Student Lifecycle tidak ada**: tidak ada state machine Applicant→Enrolled→Active→Promotion→Transfer→Graduation→Alumni. Promosi/kelulusan tidak tercatat sebagai transisi.
- **D2 — CP/TP/ATP/Learning Outcomes** belum entity terpisah (hanya `CurriculumCompetency` generik).
- **D3 — Payroll belum terhubung otomatis ke Accounting** (slip gaji belum auto-posting jurnal).

### 🟡 NEED REFACTOR
- **R1 — Repositories = 0**: rule "Services call Repositories" tidak terpenuhi (query Eloquent di service/controller langsung).
- **R2 — Form Requests = 9**: validasi masih inline `$request->validate()`.
- **R3 — Events = 0**: trigger (payment/attendance) dipanggil langsung, tidak bisa di-extend via listener.
- **R4 — N+1 risk**: beberapa dashboard/table belum eager-load optimal (perlu audit).

### ❌ MISSING (fitur belum ada)
- **M1 — Teaching Journal** (guru: tanggal, materi, CP/TP/ATP, aktivitas, partisipasi, PR, refleksi + AI).
- **M2 — HR lengkap**: Employment Contract, Employment History, Leave, Overtime, BPJS, PPh21, KPI, Performance Review, Document Expiry.
- **M3 — PPDB enterprise**: Dynamic Form Builder, Entrance Test, Interview, Scoring, Ranking, Quota, Waiting List, Admission Letter, Convert Applicant→Student (otomatis).
- **M4 — Procurement full**: Purchase Order, Goods Receipt, Vendor Performance (baru sampai Purchase Request + Approval + Supplier).
- **M5 — Inventory lanjutan**: Stock Opname, Stock Transfer (baru stock basic + asset + depreciation + maintenance + QR).
- **M6 — Yayasan full**: Central Master Data, Central Users, School Comparison, Consolidated (Finance/Academic/HR) — baru benchmark dasar.
- **M7 — AI Recommendation Engine** (rekomendasi tindakan dari risk, dengan approval manusia).
- **M8 — Dashboard per-role spesifik** (principal/teacher/finance/BK/HR).
- **M9 — Global Search** sudah ada `SearchService::searchSchool` — perlu diverifikasi coverage entity (students/parents/teachers/employees/invoices/documents/books/applicants/assets/transactions).
- **M10 — Library lanjutan**: Authors, Publishers, ISBN, Reservations, Reading Analytics (baru books/loans/fines/digital).

### 🔁 DUPLICATE (perlu konsolidasi)
- Beberapa resource "misc" (`MiscCrudController`, 14 view) = placeholder berkelompok; sebagian besar sudah punya modul proper → dead menu candidate.

### 🎨 UX PROBLEM
- Sidebar sangat besar (120+ link). Perlu IA konsolidasi domain (Phase 1) + grouping collapse + "Command Palette" (sudah ada) sebagai jalur cepat.
- Beberapa list table belum konsisten (badge, empty state, pagination).

---

## 4. Implementation Plan (20 Phase → prioritas)

| Phase | Area | Status saat ini | Prioritas |
|---|---|---|---|
| 0 | Audit | ✅ SELESAI (dokumen ini) | — |
| 1 | IA / Navigation | 🟡 perlu konsolidasi domain (bukan ratusan menu) | HIGH |
| 2 | Student Lifecycle | ❌ MISSING | HIGH |
| 3 | Student 360 | 🟡 PARTIAL (show + timeline ada) | HIGH |
| 4 | Curriculum (CP/TP/ATP/LO) | 🟡 PARTIAL | HIGH |
| 5 | Teaching Journal | ❌ MISSING | HIGH |
| 6 | PPDB Enterprise | 🟡 PARTIAL | HIGH |
| 7 | Finance + Procurement + Inventory integration | 🟡 PARTIAL | HIGH |
| 8 | HR | 🟡 PARTIAL | MED |
| 9 | Inventory & Asset | 🟡 PARTIAL | MED |
| 10 | Dashboard per-role | 🟡 PARTIAL | MED |
| 11 | AI School OS + Recommendation | 🟡 PARTIAL | MED |
| 12 | School Command Center | ✅ EXISTS (upgrade visual) | LOW |
| 13 | Yayasan | 🟡 PARTIAL | MED |
| 14 | Workflow Engine (integrasi) | 🟡 PARTIAL | MED |
| 15 | UX/UI | ✅ EXISTS (polish) | MED |
| 16 | Global Search | 🟡 PARTIAL | MED |
| 17 | Security | 🟡 (0 Policy!) | HIGH |
| 18 | Performance | 🟡 (N+1 audit) | MED |
| 19 | Testing | 🟡 (200 test, gap) | HIGH |
| 20 | QC | ongoing | — |

**Urutan eksekusi yang disarankan** (dependency-aware, tidak merusak existing):
1. **Phase 17 (Security)** — tambah Policy layer (tanpa ubah behavior), perbaiki 2 model polos → fondasi aman.
2. **Phase 2 (Student Lifecycle)** — state machine + migration + audit log + notifikasi.
3. **Phase 5 (Teaching Journal)** + **Phase 4 (CP/TP/ATP)** — melengkapi akademik.
4. **Phase 6 (PPDB enterprise)** — form builder + test/interview/scoring/ranking + convert.
5. **Phase 7 (Finance/Procurement/Inventory integration)** — PO + goods receipt + stock opname + payroll→jurnal.
6. **Phase 8 (HR)** — contract/leave/overtime/BPJS/PPh21/KPI.
7. **Phase 10 + 12 (Dashboard)** — role-specific widgets.
8. **Phase 11 (AI Recommendation)**.
9. **Phase 13 (Yayasan)** + **Phase 14 (Workflow)**.
10. **Phase 16 (Search)** + **Phase 18 (Performance)** + **Phase 19 (Testing)**.
11. **Phase 1 (IA)** + **Phase 15 (UX)** — restrukturisasi nav + polish di akhir (setelah semua modul ada).

---

## 5. Next Action

Setelah audit ini, langkah berikutnya = **Phase 17 (Security hardening)** karena:
1. 0 Policy → fondasi authorization harus dibenahi sebelum menambah modul baru.
2. 2 model polos → perbaiki tenant isolation.

Bersamaan, jalankan **Phase 2 (Student Lifecycle)** sebagai fitur bisnis bernilai tertinggi.

# Sikad Pro — Complete System Architecture

## Overview

Sikad Pro adalah platform **multi-tenant School Management ERP** yang dirancang untuk mengelola
ratusan sekolah dari satu dashboard terpusat. Setiap sekolah mendapatkan subdomain sendiri, data
terisolasi, dan konfigurasi mandiri.

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        Sikad Pro Platform                            │
│                                                                         │
│  ┌──────────────────┐   ┌──────────────────┐   ┌──────────────────┐   │
│  │  Super Admin     │   │  School Admin     │   │  Flutter App      │   │
│  │  admin.sikadpro   │   │  smkn1.sikadpro    │   │  iOS / Android    │   │
│  │  .app            │   │  .app             │   │                   │   │
│  └────────┬─────────┘   └────────┬──────────┘   └────────┬──────────┘   │
│           │                      │                        │              │
│           └──────────────────────┴────────────────────────┘              │
│                                  │                                        │
│                    ┌─────────────▼──────────────┐                        │
│                    │      Laravel 11 API          │                        │
│                    │   (REST + Sanctum Auth)      │                        │
│                    └─────────────┬──────────────┘                        │
│                                  │                                        │
│          ┌───────────────────────┼───────────────────────┐               │
│          │                       │                       │               │
│  ┌───────▼──────┐    ┌───────────▼──────┐    ┌──────────▼─────┐        │
│  │  MySQL 8     │    │    Redis 7        │    │   S3 Storage   │        │
│  │  (primary)   │    │  (cache/queue)    │    │  (files/media) │        │
│  └──────────────┘    └──────────────────┘    └────────────────┘        │
│                                                                          │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │  Firebase FCM  │  SMTP Email  │  SMS Gateway  │  Payment Gateway │   │
│  └──────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Platform Components

### 1. Laravel Web Panel (Blade + Alpine.js + Tailwind CSS)

```
┌────────────────────────────────────────────────────────┐
│               Laravel Web Panel                        │
│                                                        │
│  ┌─────────────────┐    ┌─────────────────────────┐   │
│  │  Super Admin    │    │   School Panel           │   │
│  │  Panel          │    │                          │   │
│  │                 │    │  ┌──────┐ ┌───────────┐  │   │
│  │  - Manage       │    │  │Admin │ │ Teacher   │  │   │
│  │    Schools      │    │  └──────┘ └───────────┘  │   │
│  │  - Billing      │    │  ┌──────┐ ┌───────────┐  │   │
│  │  - Plans        │    │  │Staff │ │ Librarian │  │   │
│  │  - Analytics    │    │  └──────┘ └───────────┘  │   │
│  │  - System       │    │                          │   │
│  │    Config       │    │  Student Web Portal      │   │
│  └─────────────────┘    └─────────────────────────┘   │
└────────────────────────────────────────────────────────┘
```

### 2. Flutter Mobile App (iOS & Android)

```
┌────────────────────────────────────────────────────────┐
│               Flutter Mobile App                       │
│                                                        │
│  ┌─────────────┐  ┌─────────────┐  ┌──────────────┐  │
│  │  Student    │  │   Parent    │  │   Teacher    │  │
│  │  Shell      │  │   Shell     │  │   Shell      │  │
│  │             │  │             │  │              │  │
│  │ - Dashboard │  │ - Child     │  │ - Attendance │  │
│  │ - Timetable │  │   Progress  │  │ - Classes    │  │
│  │ - Homework  │  │ - Fees      │  │ - Marks      │  │
│  │ - Exams     │  │ - Chat      │  │ - Chat       │  │
│  │ - Marks     │  │ - Notices   │  │ - Notices    │  │
│  │ - Chat      │  │ - Attendance│  │              │  │
│  │ - Fees      │  │ - Timetable │  │              │  │
│  │ - Library   │  │             │  │              │  │
│  └─────────────┘  └─────────────┘  └──────────────┘  │
│                                                        │
│  ┌─────────────┐  ┌─────────────┐  ┌──────────────┐  │
│  │  Admin      │  │ Accountant  │  │  Librarian   │  │
│  │  Shell      │  │   Shell     │  │   Shell      │  │
│  └─────────────┘  └─────────────┘  └──────────────┘  │
└────────────────────────────────────────────────────────┘
```

### 3. Student Web Portal

```
┌────────────────────────────────────────────────────────┐
│             Student Web Portal                         │
│          (Blade + Alpine.js + Tailwind)                │
│                                                        │
│  - Dashboard dengan progress belajar                   │
│  - Timetable & jadwal pelajaran                        │
│  - Online classroom (materi, tugas, ujian)             │
│  - Hasil ujian & nilai                                 │
│  - Riwayat kehadiran                                   │
│  - Tagihan & pembayaran SPP                            │
│  - Notice board & pengumuman                           │
│  - One-to-one chat                                     │
│  - Perpustakaan digital                                │
└────────────────────────────────────────────────────────┘
```

---

## Multi-Tenancy Architecture

```
Internet
   │
   ├── admin.sikadpro.app        → Super Admin Panel
   ├── smkn1.sikadpro.app        → School A (Blade Web Panel)
   ├── smkn1.sikadpro.app/api    → School A (REST API)
   ├── sma2.sikadpro.app         → School B (Blade Web Panel)
   └── sma2.sikadpro.app/api     → School B (REST API)

Database: SHARED (satu database MySQL)
   ├── schools          (no school_id — is the school)
   ├── plans            (no school_id — global)
   ├── roles            (no school_id — global)
   └── [semua tabel lain] WHERE school_id = X
```

---

## The 7 Roles

| # | Role          | Scope    | Akses Utama                                          |
|---|---------------|----------|------------------------------------------------------|
| 1 | `super_admin` | Platform | Semua sekolah, billing SaaS, konfigurasi global      |
| 2 | `admin`       | School   | Full akses dalam satu sekolah                        |
| 3 | `teacher`     | School   | Kelas sendiri: absensi, nilai, tugas, ujian          |
| 4 | `student`     | School   | Data sendiri: nilai, tugas, jadwal, perpustakaan     |
| 5 | `parent`      | School   | Data anak: nilai, kehadiran, tagihan, chat           |
| 6 | `receptionist`| School   | Penerimaan siswa, hostel, transportasi               |
| 7 | `accountant`  | School   | SPP, tagihan, payroll, laporan keuangan              |
| 8 | `librarian`   | School   | Katalog buku, peminjaman, pengembalian, denda        |

---

## Modules — Full Map (45 Modules across 11 Phases)

```
┌─────────────────────────────────────────────────────────────┐
│  PHASE 0 — LICENSE                                          │
│  00. License Protection (whitelabel.co.id)                  │
├─────────────────────────────────────────────────────────────┤
│  PHASE 1 — FOUNDATION                                       │
│  01. Multi-Tenant Foundation    (SchoolScope, plans, infra) │
│  02. Auth & Roles               (Sanctum, Spatie, 7 roles)  │
├─────────────────────────────────────────────────────────────┤
│  PHASE 2 — ACADEMIC CORE                                    │
│  03. School Setup               (profil, tahun ajaran)      │
│  03b.School Branding & Whitelabel (logo, color, splash)    │
│  04. Academic Structure         (kelas, jurusan, mata pel.) │
│  05. Attendance                 (absensi harian + laporan)  │
│  06. Timetable                  (jadwal per kelas/guru)     │
│  07. Online Classroom           (materi, tugas, submission) │
│  08. Exam Engine                (ujian online + offline)    │
│  09. Marks & Grades             (penilaian + rapor)         │
├─────────────────────────────────────────────────────────────┤
│  PHASE 3 — FINANCE & ADMIN                                  │
│  10. Admission                  (pendaftaran siswa baru)    │
│  11. Fee & Invoice              (SPP, tagihan, pembayaran)  │
│  11b.Payment Gateway (Dynamic, no hardcode)                 │
│  12. Payroll                    (gaji guru + staff)         │
│  13. Subscription               (billing SaaS per sekolah)  │
├─────────────────────────────────────────────────────────────┤
│  PHASE 4 — FACILITIES                                       │
│  14. Library                    (buku, peminjaman, denda)   │
│  15. Hostel                     (asrama, kamar, penghuni)   │
│  16. Transport                  (rute, kendaraan)           │
├─────────────────────────────────────────────────────────────┤
│  PHASE 5 — COMMUNICATION                                    │
│  17. Notice Board               (pengumuman per role/kelas) │
│  18. Chat                       (1-on-1 real-time chat)     │
│  19. Notifications              (FCM + email + SMS)         │
├─────────────────────────────────────────────────────────────┤
│  PHASE 6 — MOBILE                                           │
│  20. Flutter App                (shell per role)            │
├─────────────────────────────────────────────────────────────┤
│  PHASE 7 — SUPER ADMIN                                      │
│  21. SaaS Panel                 (dashboard platform-wide)   │
├─────────────────────────────────────────────────────────────┤
│  PHASE 8 — STUDENT LIFECYCLE                                │
│  22. PPDB Online                (penerimaan + zonasi)       │
│  23. Bus Tracking + ID Gate     (GPS + tap kartu gerbang)   │
│  24. UKS / Klinik               (medis + vaksinasi)         │
│  25. BP/BK + Discipline         (counseling + tata tertib)  │
├─────────────────────────────────────────────────────────────┤
│  PHASE 9 — TEACHING TOOLS                                   │
│  26. Lesson Plan / RPP          (RPP + AI generator)        │
│  27. Cafeteria / Kantin Cashless                            │
│  28. Pesantren / Madrasah Mode  (tahfidz, ibadah, kitab)    │
│  31. AI Assistant (Dynamic Provider)                        │
│  35. Live Class                 (Zoom/Meet/Jitsi dynamic)   │
│  36. Question Bank              (soal reusable + IRT)       │
│  40. Curriculum Mapping         (CP/TP, KI-KD)              │
├─────────────────────────────────────────────────────────────┤
│  PHASE 10 — ENGAGEMENT & GROWTH                             │
│  29. Donations / Fundraising                                │
│  30. Alumni Network             (job, mentor, reuni)        │
│  37. Achievement Tracker        (sertifikat + badge)        │
│  38. Scholarship Management     (beasiswa + auto-discount)  │
│  39. Career Guidance            (test minat, PKL, sertif)   │
│  42. Event Management           (RSVP + ticket QR)          │
│  43. Daily Report to Parent     (auto-laporan harian)       │
│  44. Extracurricular            (ekskul + absensi)          │
├─────────────────────────────────────────────────────────────┤
│  PHASE 11 — OPERATIONS & INTELLIGENCE                       │
│  32. Dapodik Sync               (Kemdikbud compliance)      │
│  33. Visitor Management         (tamu sekolah + badge)      │
│  34. Inventory / Aset           (lab, alat, maintenance)    │
│  41. Yayasan Dashboard          (multi-school foundation)   │
│  45. Learning Analytics         (predictive drop-out)       │
└─────────────────────────────────────────────────────────────┘
```

---

## Laravel Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/                      ← REST API (Flutter + Student Web)
│   │   │   ├── AuthController.php
│   │   │   ├── AttendanceController.php
│   │   │   └── ...
│   │   └── Web/                      ← Blade web panel
│   │       ├── DashboardController.php
│   │       └── ...
│   ├── Middleware/
│   │   ├── EnsureSchoolAccess.php    ← CRITICAL: isolasi antar sekolah
│   │   ├── ResolveSchool.php         ← resolve subdomain → school_id
│   │   └── RoleMiddleware.php
│   ├── Requests/                     ← Form validation
│   └── Resources/                    ← API response shaping
├── Models/
│   ├── SchoolModel.php               ← Abstract base (SchoolScope + SoftDeletes)
│   ├── Scopes/SchoolScope.php
│   ├── School.php
│   ├── User.php
│   └── ...
├── Services/                         ← Business logic
├── Repositories/                     ← DB queries
├── Policies/                         ← RBAC per model
└── Jobs/                             ← Queued jobs (notifikasi, dll)

modules/                              ← nwidart/laravel-modules
├── Academic/
│   ├── Http/Controllers/
│   ├── Models/
│   ├── Services/
│   ├── Repositories/
│   └── Routes/
├── Finance/
├── Facilities/
├── Communication/
└── Auth/

database/
├── migrations/
└── seeders/
    ├── PlanSeeder.php
    ├── RolePermissionSeeder.php
    └── DemoSchoolSeeder.php

resources/
├── views/
│   ├── layouts/
│   │   ├── app.blade.php             ← main layout (Tailwind + Alpine)
│   │   ├── auth.blade.php
│   │   └── super-admin.blade.php
│   ├── dashboard/
│   ├── academic/
│   ├── finance/
│   └── ...
└── js/
    └── components/                   ← Alpine.js components

tests/
├── Feature/
│   ├── MultiTenancy/
│   ├── Auth/
│   ├── Attendance/
│   ├── Library/
│   └── ...
└── Unit/
```

---

## Flutter App Structure

```
lib/
├── main.dart
├── app/
│   ├── app.dart                      ← MaterialApp + routing
│   ├── router/
│   │   └── app_router.dart           ← GoRouter, role-based routing
│   └── theme/
│       └── app_theme.dart
├── core/
│   ├── api/
│   │   ├── api_client.dart           ← Dio HTTP client
│   │   ├── api_endpoints.dart
│   │   └── interceptors/
│   │       ├── auth_interceptor.dart
│   │       └── school_interceptor.dart
│   ├── storage/
│   │   └── secure_storage.dart       ← flutter_secure_storage
│   ├── notifications/
│   │   └── fcm_service.dart          ← Firebase Cloud Messaging
│   └── utils/
├── features/
│   ├── auth/
│   │   ├── data/
│   │   │   ├── auth_repository.dart
│   │   │   └── models/user_model.dart
│   │   ├── domain/
│   │   └── presentation/
│   │       ├── bloc/auth_bloc.dart
│   │       └── pages/login_page.dart
│   ├── dashboard/
│   ├── attendance/
│   ├── timetable/
│   ├── classroom/
│   ├── exam/
│   ├── marks/
│   ├── fees/
│   ├── library/
│   ├── hostel/
│   ├── transport/
│   ├── notice/
│   ├── chat/
│   └── profile/
└── shells/
    ├── student_shell.dart
    ├── parent_shell.dart
    ├── teacher_shell.dart
    ├── admin_shell.dart
    └── staff_shell.dart             ← receptionist, accountant, librarian
```

---

## API Structure

```
/api/v1/
├── auth/
│   ├── POST   login
│   ├── POST   logout
│   ├── GET    me
│   ├── PUT    profile
│   ├── POST   avatar
│   ├── POST   fcm-token
│   ├── POST   forgot-password
│   └── POST   reset-password
│
├── academic/
│   ├── GET    classes
│   ├── GET    subjects
│   ├── GET    sections
│   └── GET    academic-years
│
├── attendance/
│   ├── GET    class/{classSectionId}
│   ├── POST   class/{classSectionId}   (bulk mark)
│   ├── PUT    {id}
│   ├── GET    student/{studentId}
│   ├── GET    report
│   └── GET    summary/{studentId}
│
├── timetable/
│   ├── GET    class/{classSectionId}
│   ├── POST   /                        (admin)
│   └── PUT    {id}
│
├── classroom/
│   ├── GET    lessons
│   ├── POST   lessons
│   ├── GET    assignments
│   ├── POST   assignments
│   ├── POST   assignments/{id}/submit
│   └── GET    assignments/{id}/submissions
│
├── exams/
│   ├── GET    /
│   ├── POST   /
│   ├── GET    {id}/questions
│   ├── POST   {id}/start
│   ├── POST   {id}/submit
│   └── GET    {id}/results
│
├── marks/
│   ├── GET    student/{studentId}
│   ├── POST   bulk
│   └── GET    report-card/{studentId}
│
├── admission/
│   ├── GET    applications
│   ├── POST   applications
│   ├── PUT    applications/{id}
│   └── POST   applications/{id}/approve
│
├── fees/
│   ├── GET    invoices
│   ├── POST   invoices
│   ├── GET    invoices/{id}
│   ├── POST   invoices/{id}/pay
│   └── GET    student/{studentId}/dues
│
├── payroll/
│   ├── GET    /
│   ├── POST   /
│   └── GET    {staffId}/slips
│
├── library/
│   ├── GET    books
│   ├── POST   books
│   ├── PUT    books/{id}
│   ├── DELETE books/{id}
│   ├── POST   books/barcode
│   ├── POST   issue
│   ├── POST   return/{issueId}
│   ├── GET    issues
│   ├── GET    issues/overdue
│   ├── GET    member/{userId}
│   └── POST   fine/{issueId}/pay
│
├── hostel/
│   ├── GET    rooms
│   ├── POST   rooms
│   ├── GET    allocations
│   ├── POST   allocations
│   └── DELETE allocations/{id}
│
├── transport/
│   ├── GET    routes
│   ├── POST   routes
│   ├── GET    vehicles
│   └── GET    student/{studentId}/route
│
├── notices/
│   ├── GET    /
│   ├── POST   /
│   ├── PUT    {id}
│   └── DELETE {id}
│
├── chat/
│   ├── GET    conversations
│   ├── GET    conversations/{id}/messages
│   ├── POST   conversations/{id}/messages
│   └── GET    users/searchable
│
└── notifications/
    ├── GET    /
    ├── PUT    {id}/read
    └── PUT    read-all
```

---

## Database Schema Overview

```
Foundation Tables (no school_id):
  plans           ← Free, Basic, Pro
  roles           ← 7 roles
  permissions     ← spatie permissions
  schools         ← tenant registry

User & Auth:
  users           ← school_id nullable (null = super_admin)
  model_has_roles
  model_has_permissions
  personal_access_tokens

Academic:
  academic_years      school_id
  class_rooms         school_id
  sections            school_id
  subjects            school_id
  class_sections      school_id  (class + section + subject + teacher)
  students            school_id
  student_subjects    pivot
  timetables          school_id
  attendances         school_id  (unique: school_id, student_id, date)

Online Classroom:
  lessons             school_id
  lesson_topics       school_id
  assignments         school_id
  assignment_submissions school_id
  study_materials     school_id

Exam:
  exams               school_id
  exam_questions      school_id
  exam_question_options
  exam_submissions    school_id
  exam_answers        school_id

Marks & Grades:
  grade_systems       school_id
  marks               school_id
  report_cards        school_id

Admission:
  admission_inquiries school_id
  admission_forms     school_id

Finance:
  fee_categories      school_id
  fee_structures      school_id
  fee_invoices        school_id
  fee_payments        school_id
  payroll_structures  school_id
  payroll_slips       school_id
  expenses            school_id

Facilities:
  book_categories     school_id
  books               school_id
  book_issues         school_id
  hostel_blocks       school_id
  hostel_rooms        school_id
  hostel_allocations  school_id
  transport_routes    school_id
  vehicles            school_id
  student_transports  school_id

Communication:
  notices             school_id
  conversations       school_id
  messages            school_id
  notifications       school_id
  activity_log        school_id (spatie activitylog)
```

---

## Key Technical Decisions

### Multi-Tenancy: Shared DB + SchoolScope
Semua model yang school-owned extend `SchoolModel` yang otomatis menerapkan
`SchoolScope` global scope. Setiap query difilter `WHERE school_id = X`.

### Money: Integer Only
Semua nominal uang (SPP, gaji, denda) disimpan sebagai `UNSIGNED INT` dalam
sen/paise/cents. Accessor `getAmountFormattedAttribute()` untuk tampilan.

### Queue: Redis
Job yang berat (notifikasi FCM, export CSV, mark overdue) dijalankan via Laravel
Queue dengan driver Redis untuk non-blocking response.

### Real-time Chat: Laravel Echo + Pusher/Soketi
Chat 1-on-1 menggunakan Laravel Broadcasting. Opsi: Pusher (cloud) atau Soketi
(self-hosted, open-source Pusher alternative).

### File Storage: S3-Compatible
Avatar, materi pelajaran, lampiran tugas, cover buku disimpan di S3-compatible
storage. Local `storage/` untuk development.

### Auth: Laravel Sanctum
Token-based auth untuk API (Flutter + Student Web). Cookie-based SPA auth untuk
Blade web panel. Token memuat permissions array untuk frontend routing.

---

## SaaS Subscription Model

```
Plan      Price     Max Students  Features
────────  ────────  ────────────  ──────────────────────────────
Free      Rp 0      50            attendance, notice
Basic     Rp 99rb   500           + library, fee, timetable
Pro       Rp 199rb  Unlimited     semua fitur + priority support
```

School yang plannya expired → read-only mode, tidak bisa tambah data.
Super admin dapat perpanjang, upgrade, atau suspend sekolah dari SaaS panel.

---

## Deployment Architecture

```
┌──────────────────────────────────────────────────────────┐
│                    Production                             │
│                                                          │
│  Nginx (reverse proxy + SSL termination)                 │
│    ├── *.sikadpro.app  → Laravel (PHP-FPM)                │
│    └── admin.sikadpro.app → Laravel (PHP-FPM)             │
│                                                          │
│  Docker Compose:                                         │
│    ├── app (PHP 8.3 + Laravel)                           │
│    ├── nginx                                             │
│    ├── mysql:8                                           │
│    ├── redis:7                                           │
│    ├── worker (Laravel queue worker)                     │
│    └── scheduler (Laravel scheduler)                     │
│                                                          │
│  SSL: Let's Encrypt (wildcard cert *.sikadpro.app)        │
└──────────────────────────────────────────────────────────┘
```

---

## Feature Matrix per Role

| Feature              | Super | Admin | Teacher | Student | Parent | Recept | Account | Librar |
|----------------------|:-----:|:-----:|:-------:|:-------:|:------:|:------:|:-------:|:------:|
| Manage Schools       | ✓     | —     | —       | —       | —      | —      | —       | —      |
| School Setup         | ✓     | ✓     | —       | —       | —      | —      | —       | —      |
| Manage Teachers      | ✓     | ✓     | —       | —       | —      | —      | —       | —      |
| Manage Students      | ✓     | ✓     | view    | own     | child  | ✓      | —       | —      |
| Timetable            | ✓     | ✓     | view    | view    | view   | —      | —       | —      |
| Mark Attendance      | ✓     | ✓     | own cls | —       | —      | —      | —       | —      |
| View Attendance      | ✓     | ✓     | own cls | own     | child  | view   | —       | —      |
| Online Classroom     | ✓     | ✓     | own cls | submit  | view   | —      | —       | —      |
| Exams                | ✓     | ✓     | own cls | take    | view   | —      | —       | —      |
| Marks & Grades       | ✓     | ✓     | own cls | own     | child  | —      | —       | —      |
| Admission            | ✓     | ✓     | —       | —       | —      | ✓      | —       | —      |
| Fee Management       | ✓     | ✓     | —       | view    | view   | —      | ✓       | —      |
| Payroll              | ✓     | ✓     | own     | —       | —      | —      | ✓       | —      |
| Library              | ✓     | ✓     | —       | view    | —      | —      | —       | ✓      |
| Hostel               | ✓     | ✓     | —       | view    | view   | ✓      | —       | —      |
| Transport            | ✓     | ✓     | —       | view    | view   | ✓      | —       | —      |
| Notice Board         | ✓     | ✓     | ✓       | view    | view   | ✓      | view    | view   |
| Chat                 | ✓     | ✓     | ✓       | ✓       | ✓      | ✓      | ✓       | ✓      |
| Analytics Dashboard  | ✓     | ✓     | limited | —       | —      | —      | ✓       | limited|
| Subscription Billing | ✓     | —     | —       | —       | —      | —      | —       | —      |

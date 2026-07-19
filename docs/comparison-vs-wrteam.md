# eSchool Kita vs WRTeam eSchool SaaS — Feature Comparison

> **Sumber WRTeam:** https://www.wrteam.in/product-details/eschool-saas-school-management-system-with-student-parents-teacher-flutter-app-laravel-admin
> **WRTeam Envato ID:** 49307764 | Rating: 4.93/5 | 934 sales | v1.9.2 (Apr 2026)
> **Tanggal review:** 26 May 2026

---

## Ringkasan Eksekutif

| Dimensi | WRTeam eSchool SaaS | eSchool Kita |
|---|---|---|
| **Laravel** | 10/11 | **13** |
| **Flutter** | 3.x (2 apps) | **3.27+** (6+ role shells) |
| **Harga** | $99-$250 (CodeCanyon) | Whitelabel custom |
| **Rilis awal** | Nov 2023 | May 2026 |
| **Modul** | ~25 | **45+** |
| **Tabel DB** | ~80-100 (estimasi) | **150+** |
| **Integrasi** | Hardcoded per vendor | **Format-based, user-configured** |
| **Multi-tenancy** | Separate DB per school | Shared DB + school_id scope |
| **Demo** | Play Store + TestFlight live | Belum live |

---

## Feature Matrix

### Tech Stack

| Feature | WRTeam | Kita | Notes |
|---|---|---|---|
| Laravel version | 10/11 | **13** | Kita lebih baru |
| Flutter version | 3.x | **3.27+** | |
| Database | MySQL | MySQL 8.4 | |
| Cache/Queue | Unknown | **Redis 7** | |
| Realtime | Laravel Reverb | Laravel Broadcasting + Pusher/Soketi | |
| Permissions | Custom | **Spatie Laravel-Permission** | |
| Modules | Monolith | **nwidart/laravel-modules** | |
| Activity Log | No | **Spatie Activitylog** | |
| PDF | Unknown | **Barryvdh/DomPDF** | |
| Testing | Unknown | **Pest PHP** (22 tests) | |

### SaaS / Multi-Tenancy

| Feature | WRTeam | Kita | Notes |
|---|---|---|---|
| Multi-school management | ✅ | ✅ | |
| Separate DB per school | ✅ | ❌ | Kita: shared DB + Global Scope |
| Subscription plans | ✅ | ✅ | |
| Package/feature gating | ✅ | ✅ | |
| School self-registration | ✅ | ✅ | |
| Custom domain per school | ✅ | ❌ | **GAP** |
| Demo schools | ✅ | ✅ | |
| Installation wizard | ✅ | ❌ | WRTeam punya UI wizard |
| School data backup/import | ✅ | ✅ | |
| School inquiry/lead form | ✅ | ❌ | |
| Wildcard domain validation | ✅ | ❌ | |

### Admin Panel

| Feature | WRTeam | Kita | Notes |
|---|---|---|---|
| Super Admin Panel | ✅ | ✅ | |
| School Admin Panel | ✅ | ✅ | |
| Teacher Panel | ✅ | ✅ | |
| Staff Panel | ✅ | ❌ | Kita: staff masuk Teacher Portal |
| Student Web Portal | ✅ (v1.9.0) | ✅ | |
| Parent Portal | ✅ (via Student) | ✅ | Kita punya portal terpisah |
| Foundation/Yayasan Dashboard | ❌ | ✅ | |
| Accountant Portal | ❌ | ✅ | |
| Librarian Portal | ❌ | ✅ | |
| Dark mode toggle | ✅ | ❌ | **GAP** |
| Sidebar menu search | ✅ | ❌ | |
| Multi-language admin | ✅ (14+ bahasa) | ❌ | **GAP** |
| RTL support | ✅ | ❌ | **GAP** |
| Role-based access | ✅ | ✅ | |
| Export CSV/Excel tables | ✅ | ✅ | |

### Akademik

| Feature | WRTeam | Kita | Notes |
|---|---|---|---|
| Academic years / semesters | ✅ | ✅ | |
| Class & section management | ✅ | ✅ | |
| Subject management | ✅ | ✅ | |
| Elective subjects | ✅ | ✅ | |
| Syllabus pattern | ✅ (v1.9.2) | ✅ | |
| Timetable (class + teacher) | ✅ | ✅ | |
| Attendance (student + staff) | ✅ | ✅ | |
| Student promotion/transfer | ✅ | ✅ | |
| Educational mediums | ✅ | ✅ | |
| Study materials & lessons | ✅ | ✅ | |
| Assignments + submission | ✅ | ✅ | |
| Lesson plans (RPP) | ❌ | ✅ | |
| Curriculum frameworks | ❌ | ✅ | |
| Competency assessments | ❌ | ✅ | |
| Live class sessions | ❌ | ✅ | Zoom/Meet/Jitsi |
| AI study assistant | ❌ | ✅ | |
| AI RPP generator | ❌ | ✅ | |
| AI essay grader | ❌ | ✅ | |
| Question bank | ❌ | ✅ | |
| Daily reports (parent) | ❌ | ✅ | |

### Ujian

| Feature | WRTeam | Kita | Notes |
|---|---|---|---|
| Online exams (MCQ) | ✅ | ✅ | |
| Offline exams | ✅ | ✅ | |
| Bulk MCQ upload | ✅ (v1.7.0) | ✅ | |
| Random question assignment | ✅ (v1.7.0) | ✅ | |
| Difficulty levels | ✅ (v1.7.0) | ✅ | |
| Draft/publish marks | ✅ | ✅ | |
| Exam timetable | ✅ | ✅ | |
| Class-wise questions | ✅ | ✅ | |
| Grade system + rules | ✅ | ✅ | |
| Report cards | ✅ | ✅ | |
| Screen recording protection | ✅ (v1.9.2) | ❌ | **GAP** |
| Certificate generation | ✅ | ❌ | **GAP** |
| Student result PDF | ✅ | ✅ | |

### Keuangan

| Feature | WRTeam | Kita | Notes |
|---|---|---|---|
| Fee structures | ✅ | ✅ | |
| Fee invoices | ✅ | ✅ | |
| Installment payments | ✅ | ✅ | |
| Partial payment + receipt | ✅ | ✅ | |
| Fee due charges (fixed/%) | ✅ | ✅ | |
| Fee statistics | ✅ | ✅ | |
| Optional fees filter | ✅ (v1.7.0) | ❌ | |
| Expense management | ✅ | ✅ | |
| Staff payroll | ✅ | ✅ | |
| Salary slip generation | ✅ | ✅ | |
| Account selection in fees | ✅ | ❌ | |
| Prepaid subscription | ✅ (v1.3.0) | ❌ | |
| Finance reports | ✅ | ✅ | P&L, AR/AP aging |
| Cash flow chart | ❌ | ✅ | |
| Rekening manual | ❌ | ✅ | |

### Payment Gateway

| Feature | WRTeam | Kita | Notes |
|---|---|---|---|
| Stripe | ✅ (hardcoded) | ✅ (via RedirectCheckout) | |
| Razorpay | ✅ (hardcoded) | ✅ (via RedirectCheckout) | |
| Paystack | ✅ (hardcoded) | ✅ (via RedirectCheckout) | |
| Flutterwave | ✅ (hardcoded) | ✅ (via RedirectCheckout) | |
| Midtrans | ❌ | ✅ (via RedirectCheckout) | |
| Virtual Account | ❌ | ✅ | |
| GoPay / OVO / ShopeePay | ❌ | ✅ | |
| QRIS Dynamic | ❌ | ✅ | |
| QRIS Static | ❌ | ✅ | |
| Transfer Manual | ❌ | ✅ | |
| Cash | ✅ (subscription only) | ✅ | |
| Arsitektur | Hardcoded per vendor | **Format-based dynamic** | |
| Per-school config | No | ✅ | Tiap sekolah BYOK sendiri |

### Komunikasi

| Feature | WRTeam | Kita | Notes |
|---|---|---|---|
| 1-on-1 Chat | ✅ (Laravel Reverb) | ✅ (Pusher/Soketi) | |
| Push notification (FCM) | ✅ | ✅ | |
| Multi-device FCM tokens | ✅ (v1.8.2) | ✅ | |
| SMS notification | ❌ | ✅ | REST generic adapter |
| WhatsApp notification | ❌ | ✅ | REST generic adapter |
| Notice board | ✅ | ✅ | |
| Role-based notifications | ✅ | ✅ | |
| Guardian notification (absent) | ✅ | ✅ | |
| Guardian notification (fees) | ✅ | ✅ | |
| Overdue notification | ✅ (v1.5.3) | ✅ | |
| Email queue for bulk | ✅ | ✅ | |
| Outbound webhooks | ❌ | ✅ | HMAC-SHA256 signed |
| Student diary | ✅ (v1.8.0) | ❌ | **GAP** |

### Fasilitas

| Feature | WRTeam | Kita | Notes |
|---|---|---|---|
| Library management | ❌ | ✅ | + barcode scanner |
| Hostel / Asrama | ❌ | ✅ | |
| Canteen / Kantin | ❌ | ✅ | + wallet system |
| Transport management | ✅ (v1.8.0) | ✅ | + GPS tracking |
| Bus live tracking | ❌ | ✅ | |
| Vehicle trips | ❌ | ✅ | |
| Medical / UKS | ❌ | ✅ | |
| Counseling / BP/BK | ❌ | ✅ | |
| Wellness check-in | ❌ | ✅ | |
| Bullying reports | ❌ | ✅ | |
| Visitor management | ❌ | ✅ | |
| Asset / Inventory | ❌ | ✅ | |

### Kesiswaan

| Feature | WRTeam | Kita | Notes |
|---|---|---|---|
| Student admissions | ✅ | ✅ | |
| Online admission form | ✅ (v1.4.0) | ✅ | |
| PPDB (zonasi, seleksi) | ❌ | ✅ | |
| Student ID card | ✅ | ✅ | + QR token rotation |
| Staff ID card | ✅ (v1.3.2) | ❌ | **GAP** |
| Bulk upload students | ✅ | ✅ | |
| Bulk upload profile images | ✅ (v1.2.0) | ❌ | |
| Student custom fields | ✅ (v1.9.1) | ❌ | |
| Discipline records | ❌ | ✅ | |
| Extracurricular | ❌ | ✅ | |
| Alumni | ❌ | ✅ | |
| Achievements + badges | ❌ | ✅ | |
| Scholarship | ❌ | ✅ | |
| Career & internship | ❌ | ✅ | |

### Pengajaran Khusus

| Feature | WRTeam | Kita | Notes |
|---|---|---|---|
| Religious / Pesantren mode | ❌ | ✅ | |
| Hafalan tracking | ❌ | ✅ | |
| Ibadah logs | ❌ | ✅ | |
| Kitab kuning progress | ❌ | ✅ | |
| Dapodik integration | ❌ | ✅ | Indonesia-specific |

### Engagement

| Feature | WRTeam | Kita | Notes |
|---|---|---|---|
| School events | ❌ | ✅ | + RSVP |
| Donation campaigns | ❌ | ✅ | |
| School gallery | ✅ (v1.2.0) | ❌ | **GAP** |
| Polls / surveys | ❌ | ❌ | |

### Marketing & SEO

| Feature | WRTeam | Kita | Notes |
|---|---|---|---|
| SaaS landing page | ✅ | ✅ | |
| School website CMS | ✅ | ❌ | **GAP — HIGH** |
| Custom domain per school | ✅ | ❌ | **GAP — HIGH** |
| Programmatic SEO | ❌ | ✅ | 18 PSEO routes |
| Dynamic sitemap.xml | ❌ | ✅ | |
| robots.txt | ✅ | ✅ | |
| /docs page | ✅ (GitHub) | ✅ | |
| OpenAPI docs | ❌ | ✅ | ReDoc |
| Pricing page | ✅ (landing) | ✅ | |
| Multi-language website | ✅ (v1.7.0) | ❌ | **GAP** |
| Google reCAPTCHA | ✅ (v1.4.0) | ❌ | |
| JSON-LD schema | ✅ | ✅ | |

### Mobile App (Flutter) — Perbandingan Detail

#### Jumlah & Jenis App

| Aspek | WRTeam | Kita |
|---|---|---|
| **Jumlah app** | 2 apps | **1 app (6 role shells)** |
| Student/Parent | 1 app terpisah | Student shell + Parent shell dalam 1 app |
| Teacher/Staff | 1 app terpisah | Teacher shell dalam 1 app |
| Admin | ❌ | Admin shell (full admin access) |
| Accountant | ❌ | Staff shell (finance role) |
| Librarian | ❌ | Staff shell (library role) |
| Receptionist | ❌ | Staff shell |
| Nurse | ❌ | Staff shell |
| Counselor | ❌ | Staff shell |
| Foundation Admin | ❌ | Staff shell |
| **Arsitektur** | 2 codebase terpisah | 1 codebase, 6 shell, role-based routing |

#### Struktur Navigasi Per Role

| Role | WRTeam | Kita (bottom nav tabs) |
|---|---|---|
| **Student** | Tab-based (unknown detail) | Beranda | Jadwal | Kelas | Chat | Profil |
| **Parent** | Tab-based (unknown detail) | Beranda | Nilai | Kehadiran | Tagihan | Profil |
| **Teacher** | Tab-based (unknown detail) | Beranda | Absensi | Kelas | Ujian | Profil |
| **Admin** | ❌ (no mobile app) | Dashboard | Admisi | Keuangan | Pengumuman | Profil |
| **Staff** | ❌ (no mobile app) | Beranda | Profil |

#### Student/Parent App — Feature Detail

| Fitur | WRTeam | Kita | Detail Kita |
|---|---|---|---|
| Online exam + MCQ | ✅ | ✅ | Exam list + timer + submit + result |
| Fee payment | ✅ (Stripe/Razorpay/Paystack/Flutterwave) | ✅ | **7 format adapter**: Redirect, VA, eWallet, QRIS Dynamic, QRIS Static, Transfer Manual, Cash |
| Payment method selection | Static (hardcoded) | ✅ | Dynamic dari backend, konfigurasi per sekolah |
| QRIS display | ❌ | ✅ | `qr_flutter` package |
| Exam timetable | ✅ | ✅ | Via timetable page |
| Result view | ✅ | ✅ | Marks page: nilai + predikat huruf |
| Elective subjects | ✅ | ✅ | Via classroom |
| Assignments | ✅ | ✅ | Tugas tab: submit file + lihat deadline |
| Timetable | ✅ | ✅ | Weekly, tabbed by day (Sen-Sab) |
| Attendance | ✅ | ✅ | History list + status badges (H/S/I/A) |
| Holidays | ✅ | ❌ | |
| Lessons & topics | ✅ | ✅ | Materi tab: per subject + description |
| Chat with teachers | ✅ | ✅ | Real-time via Pusher WebSocket |
| Notice board | ✅ | ✅ | Read-only announcement list |
| Student diary | ✅ | ❌ | **GAP** |
| Teacher directory | ✅ | ❌ | |
| Photo gallery | ✅ (v1.2.0) | ❌ | **GAP** |
| AI study assistant | ❌ | ✅ | Chat UI + streaming ke AI backend (OpenAI/Claude/Gemini) |
| Canteen ordering | ❌ | ✅ | Menu list + wallet + cart + checkout |
| Wellness check-in | ❌ | ✅ | Mood slider + feeling tags + private note |
| Library barcode scanner | ❌ | ✅ | `mobile_scanner` (EAN-13, QR, Code128) |
| Hafalan tracking | ❌ | ✅ | Islamic school: surah, ayah, quality, notes |
| PPDB registration | ❌ | ✅ | Public form: zonasi, prestasi, afirmasi, reguler |

#### Teacher/Staff App — Feature Detail

| Fitur | WRTeam | Kita | Detail Kita |
|---|---|---|---|
| Teacher timetable | ✅ | ✅ | Weekly tabbed view |
| Leave management | ✅ | ❌ | |
| Students info lookup | ✅ | ✅ | Via classroom roster |
| Guardians info | ✅ | ❌ | |
| Mark attendance | ✅ | ✅ | Date picker + roster + H/T/S/A segmented buttons |
| View attendance | ✅ | ✅ | Student attendance history |
| Manage lessons | ✅ | ✅ | Via classroom tab |
| Manage assignments | ✅ | ✅ | Create + review + grade |
| Enter exam marks | ✅ | ✅ | Via exam list |
| Payroll history | ✅ | ✅ | Admin shell → payroll slips |
| Allowances view | ✅ | ❌ | |
| Chat | ✅ | ✅ | Real-time with students/parents |
| Hafalan input | ❌ | ✅ | Pesantren: record student memorization |
| Dashboard stats | ❌ | ✅ | Classes today + pending grading + unmarked attendance alert |
| Schedule view | ❌ | ✅ | Today's class schedule inline |

#### Admin App — Feature Detail

| Fitur | WRTeam | Kita | Detail Kita |
|---|---|---|---|
| Admin mobile app | ❌ (web only) | ✅ | **Full admin shell in Flutter** |
| Dashboard analytics | ❌ mobile | ✅ | 4 stat cards + 7-day attendance line chart (fl_chart) + quick actions |
| Student admissions | ❌ mobile | ✅ | Enrollment list with status badges |
| Fee management | ❌ mobile | ✅ | Tabbed: Belum Bayar / Lunas lists |
| Payroll | ❌ mobile | ✅ | Slip gaji list |
| Announcement management | ❌ mobile | ✅ | List + create FAB (bottom sheet form) |
| Quick actions | ❌ mobile | ✅ | Asrama | Transport | Library shortcuts |

#### Infrastruktur Flutter

| Aspek | WRTeam | Kita |
|---|---|---|
| **State management** | Unknown | **flutter_bloc** (BLoC pattern) |
| **Navigation** | Unknown | **go_router** (declarative, 5 ShellRoutes) |
| **HTTP client** | Unknown | **Dio** + interceptors (auth, error, logging) |
| **Push notification** | Firebase FCM | Firebase FCM + local notifications |
| **Real-time** | Unknown | **Pusher Channels** WebSocket |
| **Secure storage** | Unknown | **flutter_secure_storage** (encrypted) |
| **Offline resilience** | Unknown | Token + user + school cached, auto-restore on boot |
| **Theme** | Unknown | Material 3, full light/dark, role accent colors |
| **Charts** | Unknown | **fl_chart** (line charts on admin dashboard) |
| **Animations** | Unknown | Lottie + shimmer loading |
| **Dark mode** | ✅ (web admin) | ✅ di Flutter app |
| **Multi-language** | ✅ | ✅ id/en/ar (locale per-user, synced to backend) |
| **Amount handling** | Unknown | **Integer cents** — all finance values stored as cents |

#### Deployment & Distribution

| Aspek | WRTeam | Kita | Status |
|---|---|---|---|
| Play Store | ✅ Live | ❌ | **GAP — HIGH** |
| App Store / TestFlight | ✅ Live | ❌ | **GAP — HIGH** |
| App package name | `com.wrteam.saas.school` | `com.eschool.app` | |
| App version | Unknown | 1.0.0+1 | |
| Android SDK | Unknown | 3.27+ | |
| Firebase setup | ✅ | ✅ | FCM + Analytics |

---

#### Keunggulan Flutter Kita vs WRTeam

| # | Keunggulan | Impact |
|---|---|---|
| 1 | **6 role shells dalam 1 app** vs 2 app terpisah | Single codebase, maintainable |
| 2 | **Admin full mobile** — WRTeam admin web-only | Manajemen sekolah dari HP |
| 3 | **7 format payment adapter** — dynamic, no hardcode | Tambah payment provider tanpa update app |
| 4 | **QRIS display** native di app | Standar pembayaran Indonesia |
| 5 | **AI study assistant** chat di Flutter | Differentiator unik |
| 6 | **Canteen cashless** ordering | Full school ecosystem |
| 7 | **Barcode scanner** untuk perpustakaan | Self-service library |
| 8 | **Bus GPS tracking** real-time untuk orang tua | Safety feature |
| 9 | **Hafalan tracking** untuk pesantren | Niche market Islamic schools |
| 10 | **Wellness check-in** siswa | Mental health awareness |
| 11 | **PPDB mobile registration** | Admission from anywhere |
| 12 | **47 screens** total coverage | Hampir semua modul ada di mobile |

#### Gap Flutter (yang WRTeam punya, kita belum)

| # | Fitur | Prioritas |
|---|---|---|
| 1 | Publish Play Store + TestFlight | HIGH |
| 2 | Holiday calendar view | LOW |
| 3 | Teacher directory | LOW |
| 4 | Guardian info lookup | LOW |
| 5 | Staff leave management | MEDIUM |
| 6 | Photo gallery | LOW |

### Security

| Feature | WRTeam | Kita | Notes |
|---|---|---|---|
| 2FA toggle | ✅ (v1.8.0) | ✅ | RFC 6238 TOTP |
| Admin email verification | ✅ (v1.4.0) | ✅ | |
| Screen recording protection | ✅ (v1.9.2) | ❌ | **GAP** |
| Google reCAPTCHA | ✅ (v1.4.0) | ❌ | |
| Demo school restrictions | ✅ (v1.5.2) | ❌ | |
| Data delete protection | ✅ | ✅ | |
| License protection | ❌ | ✅ | AES-256-GCM v3 |
| Encrypted API keys at rest | ❌ | ✅ | |
| HMAC-SHA256 webhooks | ❌ | ✅ | |
| Rate limiting | Unknown | ✅ | Multi-tier |
| ID Gate + QR token | ❌ | ✅ | |

### Analytics & Reporting

| Feature | WRTeam | Kita | Notes |
|---|---|---|---|
| Dashboard analytics | ✅ | ✅ | |
| Expense/attendance graphs | ✅ | ✅ | |
| Student reports | ✅ | ✅ | |
| Exam reports (yearly/subject/rank) | ✅ (v1.6.0) | ✅ | |
| Teacher report | ✅ (v1.8.1) | ✅ | |
| Staff leave report | ✅ (v1.3.0) | ✅ | |
| Transportation report | ✅ (v1.8.1) | ✅ | |
| Predictive risk scores | ❌ | ✅ | Dropout prediction |
| Learning analytics | ❌ | ✅ | |
| Platform analytics (super admin) | ❌ | ✅ | Revenue, growth charts |
| COA / journal | ❌ | ✅ | |

### Otomatisasi

| Feature | WRTeam | Kita | Notes |
|---|---|---|---|
| Scheduler commands | Unknown | ✅ | 17 commands |
| Overdue book marking | ❌ | ✅ | |
| Monthly fee generation | ❌ | ✅ | |
| Payment reminders | ❌ | ✅ | |
| Subscription reminders | ✅ | ✅ | |
| Daily report generation | ❌ | ✅ | |
| Risk score computation | ❌ | ✅ | |
| Database backup | ✅ | ✅ | |

### Lain-lain

| Feature | WRTeam | Kita | Notes |
|---|---|---|---|
| User-wise default language | ✅ (v1.3.0) | ❌ | **GAP** |
| Date format options | ✅ (v1.2.0) | ❌ | |
| Calendar drag-and-drop | ✅ | ❌ | |
| PWA support | ❌ | ✅ | |
| Responsive design | ✅ | ✅ | |
| Print mode | Unknown | ✅ | |
| Reduced motion | Unknown | ✅ | |
| Bulk school data export | ❌ | ✅ | Queued ZIP + CSV/JSON |

---

## Gap Prioritas (yang WRTeam punya, kita belum)

### HIGH Priority

| # | Fitur | Kenapa penting |
|---|---|---|
| 1 | **Multi-language + RTL** | Market global, 14+ bahasa = jual lebih luas |
| 2 | **School Website CMS** | Setiap sekolah butuh website publik sendiri |
| 3 | **Custom domain per school** | `sman1.sch.id` → website sekolah |
| 4 | **Publish Play Store + TestFlight** | WRTeam sudah live, kita belum |
| 5 | **Dark mode toggle** | Ekspektasi user modern, standar UI 2026 |

### MEDIUM Priority

| # | Fitur | Kenapa penting |
|---|---|---|
| 6 | **Certificate generation** | Standar fitur admin sekolah |
| 7 | **Screen recording protection** | Keamanan ujian online |
| 8 | **Demo schools pre-built** | Onboarding prospek lebih cepat |
| 9 | **CodeCanyon marketplace** | Channel distribusi alternatif |
| 10 | **Staff ID card** | Pelengkap student ID card |
| 11 | **School gallery** | Website sekolah lebih hidup |

### LOW Priority

| # | Fitur | Kenapa kurang urgent |
|---|---|---|
| 12 | **Student diary** | Bisa diganti daily report yang sudah ada |
| 13 | **Calendar drag-and-drop** | Nice-to-have UI |
| 14 | **Installation wizard UI** | Bisa pakai installer CLI |
| 15 | **Google reCAPTCHA** | Rate limiting sudah cukup |
| 16 | **Sidebar menu search** | Menu kita sudah terorganisir baik |
| 17 | **Student custom fields** | Bisa ditambahkan nanti |

---

## Keunggulan Kita yang WRTeam Tidak Punya

### Arsitektur

| # | Keunggulan | Dampak |
|---|---|---|
| 1 | **Laravel 13** | Latest framework, security patches terbaru |
| 2 | **Fully dynamic integrations** | User BYOK, tidak locked-in ke vendor |
| 3 | **Format-based adapters** (AI, Payment, Notification) | Tambah provider tanpa code release |
| 4 | **Encrypted API keys at rest** | Security enterprise-grade |
| 5 | **17 scheduler commands** | Otomatisasi penuh |

### Modul Eksklusif

| # | Modul | Dampak |
|---|---|---|
| 6 | **AI features** (study, RPP, essay) | Differentiator kuat, belum ada di competitor |
| 7 | **Pesantren mode** | Market niche (Indonesia), zero competition |
| 8 | **PPDB + Zonasi** | Full admission lifecycle |
| 9 | **BP/BK + Wellness + Bullying** | Compliance Kemendikbud |
| 10 | **Dapodik integration** | Sinkronisasi data pemerintah |
| 11 | **Predictive learning analytics** | Dropout prevention, data-driven |
| 12 | **Yayasan multi-school dashboard** | Untuk jaringan sekolah besar |
| 13 | **QRIS payment** | Standar payment Indonesia |
| 14 | **Programmatic SEO (18 routes)** | Organic traffic jangka panjang |
| 15 | **Live class (Zoom/Meet/Jitsi)** | Hybrid learning ready |

### Mobile App

| # | Keunggulan | Dampak |
|---|---|---|
| 16 | **6+ role shells** (vs WRTeam 2 app) | UX tailored per role |
| 17 | **Barcode scanner** | Library self-service |
| 18 | **Bus tracking map** | Real-time GPS transport |
| 19 | **QRIS display** | Pembayaran QR code |

---

## Kesimpulan

**eSchool kita unggul signifikan** dalam hal:
- Kedalaman modul (45+ vs ~25)
- Arsitektur dynamic integration (no vendor lock-in)
- AI & analytics capabilities
- Market-specific features (PPDB, Pesantren, Dapodik)
- Security (license v3, encrypted keys, HMAC webhooks)

**WRTeam unggul dalam hal:**
- Maturity (2.5 tahun development, 934 sales)
- Multi-language support (14+ bahasa)
- School website CMS
- Marketplace presence (CodeCanyon, Play Store)

**Rekomendasi:** Tutup 5 HIGH priority gap dulu sebelum go-to-market.

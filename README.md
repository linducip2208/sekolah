# Sikad Pro — School Management ERP

Platform manajemen sekolah multi-tenant berbasis cloud. Mencakup akademik, keuangan, PPDB, perpustakaan, transportasi, kantin cashless, dashboard yayasan, hingga AI assistant. Satu platform untuk seluruh ekosistem sekolah Indonesia.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13 (PHP 8.3) |
| Frontend | Blade + Alpine.js + Tailwind CSS v4 |
| Mobile | Flutter 3.x (Dart) |
| Database | MySQL 8 (primary), Redis 7 (cache/queue) |
| Storage | S3-compatible (local dev) |
| Queue | Laravel Queue via Redis |
| Auth | Laravel Sanctum (SPA + mobile tokens) |
| Permissions | Spatie Laravel-Permission |
| Real-time | Laravel Broadcasting + Reverb (WebSocket) |
| PDF | Barryvdh/DomPDF |
| Charts | Chart.js |
| Calendar | FullCalendar.js |

---

## Fitur Lengkap — 60+ Modul

### Akademik Inti
- Tahun Ajaran & Semester
- Mata Pelajaran, Kelas, Section, Rombel
- Kurikulum (CP/ATP — Merdeka & K13)
- Jadwal Pelajaran + Auto Timetable Generator
- Absensi Harian + QR Code Absensi
- Ujian & Penilaian + Bank Soal
- Raport Interaktif (Chart.js radar, bar, line)
- Online Classroom (Materi, Tugas, Submission)
- PKG — Penilaian Kinerja Guru (14 Kompetensi Permendiknas 16/2007)
- Lesson Plan / RPP + AI Generator

### Keuangan SPP
- Struktur Biaya & Invoice SPP
- Generate Invoice Massal Bulanan
- Rekam Pembayaran & Status Tracking
- Laporan Keuangan (Outstanding, Cash Flow)
- Slip Gaji & Komponen Payroll
- Anggaran Sekolah (RKAS) — Planned vs Actual
- Koperasi Sekolah — Simpanan, Pinjaman, SHU
- Pengadaan Barang & Jasa + Multi-level Approval

### Payment Gateway (Dynamic — No Hardcode)
- Format-based Adapters: RedirectCheckout, VirtualAccount, QRIS, E-Wallet, Recurring
- Midtrans, Xendit, QRIS, VA, transfer manual
- BYOK — admin input API key sendiri, ganti kapan saja

### PPDB & Student Lifecycle
- PPDB Online — periode, formulir publik, verifikasi, seleksi, pengumuman
- Zonasi PPDB
- UKS / Klinik — kunjungan, vaksinasi, rekam medis
- BP/BK — sesi konseling, laporan bullying
- Disiplin — kategori pelanggaran, catatan, poin

### Fasilitas
- Perpustakaan — katalog, peminjaman, denda otomatis
- Asrama / Hostel — gedung, kamar, alokasi siswa
- Transportasi — kendaraan, rute, penugasan siswa, GPS tracking
- Inventaris / Aset — CRUD, depresiasi, QR label, maintenance, write-off

### Pengajaran
- RPP Generator (AI-powered)
- Kantin Cashless — menu, order, wallet, topup
- Mode Pesantren — hafalan Al-Quran, Kitab Kuning, ibadah
- Live Class — provider video, sesi, absensi
- Tugas & PR Online — MCQ builder, auto-grading, essay AI similarity
- Ekstrakurikuler

### Engagement & Alumni
- Event Sekolah + RSVP
- Donasi & Fundraising — campaign publik
- Prestasi Siswa — kategori, record, badge digital
- Beasiswa — program, aplikasi, grant ke invoice
- Career Guidance — assessment, internship placement
- Alumni Network — profile, tracer study BAN-S/M, BKK SMK
- OSIS Manager — e-voting, kandidat, program kerja
- Komite Sekolah — rapat, notulen, voting keputusan
- Forum Komunitas — diskusi wali murid per kategori

### Komunikasi
- Pengumuman / Notice Board
- Chat Real-time antar role
- Notifikasi Push (FCM), Email, SMS, WhatsApp (multi-provider dynamic)
- Emergency Alert System — panic button, mass broadcast WhatsApp
- Konferensi Orang Tua-Guru — booking slot, reminder WhatsApp

### Administrasi
- Surat-Menyurat — template, auto-numbering, PDF, tanda tangan digital
- Manajemen Dokumen — versioning, approval, share link
- Laporan Harian Siswa
- Import CSV (Siswa & Staff)
- Export Data (CSV, PDF)

### Laporan & Analitik
- Custom Report Builder — drag & drop, 6 data sources, live preview
- Laporan SPP Aging
- Laporan Absensi
- Distribusi Nilai
- Leaderboard Disiplin
- Cash Flow
- Benchmark Antar Sekolah (Yayasan) — radar chart, ranking
- Learning Analytics — student risk score
- AI Dropout Prediction — weekly auto-predict + WhatsApp alert

### AI & Automation (Dynamic Provider)
- AI Provider Management — OpenAI, Anthropic, Gemini, DeepSeek, Ollama, vLLM
- AI Essay Grading — batch grading, rubric breakdown
- AI Lesson Plan Generator — RPP 1 lembar dari KD/topik
- AI Dropout Detection — 6 faktor kontributor, auto-notify
- BYOK — admin pilih model sendiri, ganti kapan saja

### SaaS & Platform
- Multi-tenant — shared DB, school_id isolation
- Super Admin — kelola sekolah, plans, subscriptions, billing
- Whitelabel — custom domain, branding, logo per sekolah
- Platform Analytics — MRR, churn rate, growth
- Lisensi — RSA-signed payload, AES-256-GCM encrypted lock

### Mobile & Infrastructure
- Flutter App (Android + iOS)
- PWA — installable, offline-ready
- Offline Mode — IndexedDB + Service Worker v2 + auto-sync
- Laravel Reverb — WebSocket real-time
- Docker + Nginx + Let's Encrypt

### SEO & Marketing
- Blog Module — artikel, kategori, RSS feed, IndexNow
- Programmatic SEO — 18+ route patterns, 1M+ halaman
- Sitemap.xml dinamis + robots.txt
- IndexNow — auto-submit ke Bing, Yandex, Seznam, Naver
- Landing Page — elite editorial theme, demo accounts, screenshot gallery
- Dokumentasi Publik (`/docs`)
- API Documentation — OpenAPI Redoc

### Keamanan
- Role-Based Access Control (Spatie)
- Multi-tenancy isolation (SchoolScope)
- 2FA / TOTP
- Audit Log (Spatie Activitylog)
- Encryption at-rest untuk semua API key
- Rate limiting
- Daily encrypted DB backup

---

## Quick Start

```bash
# Clone
git clone https://github.com/linducip2208/sekolah.git
cd sekolah

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate
php artisan db:seed

# Dev server
php artisan serve
npm run dev
```

## Demo Accounts

| Role | Email | Password |
|---|---|---|
| Super Admin | super@sikadpro.app | SuperAdmin123! |
| Admin Sekolah | admin@sman1demo.sch.id | Admin123! |
| Guru | guru1@sman1demo.sch.id | Guru123! |
| Siswa | siswa0_0@sman1demo.sch.id | Siswa123! |

## Screenshot

```bash
npm install --save-dev playwright
npx playwright install chromium
node scripts/screenshot.cjs        # 25 desktop screenshots
node scripts/screenshot-mobile.cjs # 5 mobile screenshots
```

## License

Sikad Pro — Source code dijual melalui [whitelabel.co.id](https://whitelabel.co.id). Lisensi pairing wajib untuk production use.

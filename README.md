# Sikad Pro — School Management ERP

> Dokumentasi tersedia dalam tiga bahasa: [Bahasa Indonesia](#bahasa-indonesia), [English](#english), dan [العربية](#العربية).

Sikad Pro adalah platform manajemen sekolah multi-tenant berbasis Laravel untuk mengelola akademik, siswa, keuangan, PPDB, SDM, fasilitas, komunikasi, analitik, portal orang tua/siswa, dan operasional sekolah dalam satu sistem.

Dokumentasi ini mencatat fitur yang tersedia di repository saat ini. Untuk status kedalaman workflow, permission, tenant isolation, integrasi, dan coverage test, lihat [Feature Maturity Audit](docs/FEATURE-MATURITY-AUDIT.md) dan [Roadmap & Feature Audit](docs/ROADMAP.md).

## Bahasa Indonesia

### Ringkasan fitur

#### 1. Platform sekolah dan akses pengguna

- Multi-tenant dengan isolasi data berbasis `school_id`.
- Super Admin untuk mengelola sekolah, yayasan, paket, langganan, billing, konfigurasi, kupon, backup, dan analitik platform.
- Role-based access control dengan Spatie Laravel Permission.
- Dashboard berbeda sesuai peran pengguna, termasuk Owner/Yayasan, Admin, Kepala Sekolah, Guru, Wali Kelas, Akuntan, HR, Petugas PPDB, Pustakawan, UKS, Transportasi, Asrama, Pengadaan, Orang Tua, dan Siswa.
- Manajemen user, permission, audit log, 2FA/TOTP, recovery code, rate limiting, dan session security.
- Navigasi berbasis domain dengan favorit, breadcrumb, command palette, ikon SVG, dan pencarian cepat.

#### 2. Akademik dan administrasi guru

- Tahun ajaran, semester, mata pelajaran, kelas, section, rombel, medium, dan kalender akademik.
- Kurikulum beserta versi kurikulum, CP, ATP, TP, kompetensi, learning outcome, dan pemetaan kompetensi.
- Jadwal pelajaran, konflik ruangan/waktu, guru pengganti, kelas susulan, wali kelas, dan auto timetable generator.
- Absensi harian, absensi massal, QR attendance, koreksi dengan approval, lock/reopen, notifikasi ketidakhadiran, dan audit trail.
- Jurnal mengajar, PROTA, PROMES, RPP/lesson plan, lesson study, rubric, observasi siswa, portofolio, PKG, pelatihan, dan sertifikasi guru.
- AI Teacher Assistant untuk modul ajar, RPP, rubric, worksheet, variasi soal, remedial, enrichment, dan essay grading; provider serta model dipilih dan dikonfigurasi admin.

#### 3. Siswa dan Student 360

- Master siswa, profil, dokumen, orang tua, tag, timeline, dan riwayat aktivitas.
- Lifecycle siswa: pendaftaran, enrollment, kenaikan kelas, transfer, kelulusan, dan batch promotion.
- Student 360 dengan tab akademik, absensi, disiplin, konseling, kesehatan, keuangan, perpustakaan, transportasi, prestasi, timeline, dan early warning.
- Student risk score, at-risk monitoring, dropout prediction, dan rekomendasi intervensi.

#### 4. Ujian, bank soal, nilai, dan raport

- Bank soal dengan tag, tipe soal, tingkat kesulitan, level kognitif, HOTS/AKM, versioning, review, approval, blueprint, dan package generator.
- Ujian online/CBT dengan jadwal, token, timer, navigasi soal, autosave, penanda ragu-ragu, auto-submit, review, dan auto-grading.
- Generate ujian dan kuis dari bank soal.
- Input nilai, batas skor, skala/bobot nilai, grade resolution otomatis, approval/lock nilai, dan audit perubahan.
- Item analysis: difficulty, discrimination, distractor, rata-rata skor, dan agregasi hasil ke bank soal.
- Raport PDF, raport interaktif, transkrip, publikasi massal, QR verification, dan halaman verifikasi publik.

#### 5. LMS dan pembelajaran digital

- Online classroom, materi, modul, tugas, submission, penilaian, deadline, dan progres belajar.
- Kursus, enrollment, prerequisite, modul, lesson, quiz engine, diskusi, sertifikat, dan progress tracking.
- Live class dengan provider video yang dapat dikonfigurasi.
- Digital library, reading progress, kuis, survei, dan portofolio siswa.

#### 6. PPDB dan student admission

- Periode PPDB, formulir pendaftaran publik, form builder dinamis, jalur, kuota, zonasi, dan konfigurasi biaya.
- Aplikasi calon siswa, validasi duplikasi NISN, verifikasi dokumen, tes, wawancara, penilaian, seleksi, waiting list, dan pengumuman.
- Admission letter, notifikasi email, laporan funnel, batch enrollment, dan konversi otomatis menjadi siswa.
- Integrasi lifecycle PPDB → siswa → rombel → billing dengan validasi lintas sekolah dan audit log.

#### 7. Keuangan, pembayaran, dan akuntansi

- Struktur biaya sekolah, invoice SPP, recurring invoice, generate invoice massal, pembayaran, status tracking, outstanding, dan reminder.
- Cicilan, overdue otomatis, late fee, diskon, refund, dan rekonsiliasi pembayaran.
- Payment provider dinamis berbasis format API: redirect checkout, virtual account, QRIS, e-wallet, recurring, dan transfer manual.
- BYOK: admin memasukkan endpoint, credential terenkripsi, header, dan konfigurasi provider sendiri tanpa mapping provider yang dipaksakan aplikasi.
- Double-entry accounting: chart of accounts, jurnal, neraca saldo, laba rugi, neraca, source reference, posting otomatis, refund journal, dan rekonsiliasi bank.
- Anggaran sekolah/RKAS, planned vs actual, cash flow, koperasi sekolah, simpanan, pinjaman, dan SHU.
- Payroll structure, slip gaji, proses pembayaran, BPJS Kesehatan/Ketenagakerjaan, profil pajak, bracket PPh21 progresif, laporan pajak, dan KPI appraisal.

#### 8. SDM, pengadaan, inventaris, dan aset

- Data staff, profil, kontrak kerja, cuti, lembur, dokumen kedaluwarsa, KPI template, kriteria, goal, appraisal, dan skor.
- Supplier, purchase request, purchase order, approval bertingkat, partial receiving, validasi anggaran, dan audit transaksi.
- Inventaris: stok, mutasi, transfer, adjustment, stock opname, movement type, dan invariant stok non-negatif.
- Aset: kategori, assignment, transfer, depresiasi, maintenance, QR label, write-off/disposal, dan keterkaitan dengan procurement/accounting.

#### 9. Perpustakaan, transportasi, asrama, dan fasilitas

- Perpustakaan: katalog buku, kategori, eksemplar, peminjaman, pengembalian, denda, overdue, anggota, dan analitik.
- Transportasi: kendaraan, rute, halte, penugasan siswa, trip, jadwal driver, absensi transportasi, GPS device, dan live tracking map.
- Asrama: gedung, kamar, tempat tidur, alokasi siswa, warden, absensi malam, gate pass, mess, dan meal plan.
- Inventaris fasilitas, maintenance, dashboard TV/signage, gate device, visitor registration, QR badge, check-in/out, approval host, blacklist, dan notifikasi petugas.

#### 10. Kesejahteraan, organisasi, dan student life

- UKS/klinik: kunjungan, tindakan, vaksinasi, riwayat kesehatan, dan notifikasi orang tua dengan akses terbatas.
- BK/konseling: sesi terjadwal/selesai, deteksi bentrok, catatan konseling, bullying report, assignment, closure, dan audit.
- Disiplin: kategori pelanggaran, poin, threshold sanksi, riwayat, intervensi, dan notifikasi orang tua.
- Ekstrakurikuler: program, coach, pendaftaran, kapasitas, anggota aktif, absensi, dan sertifikat.
- Prestasi siswa, badge digital, leaderboard, OSIS, kandidat, e-voting, program kerja, klub, komite sekolah, rapat, notulen, dan voting keputusan.
- Beasiswa, program, aplikasi, approval, dan grant yang dapat dikaitkan ke invoice.
- Kantin cashless: menu, order, wallet, top-up, dan histori transaksi.
- Mode pesantren: hafalan Al-Qur'an, target, progres, Kitab Kuning, dan log ibadah.

#### 11. Portal, komunikasi, dan layanan publik

- Portal orang tua: dashboard, children switcher, absensi, nilai, invoice, pembayaran, prestasi, kesehatan, konseling, disiplin, raport, konferensi, dan survei.
- Portal siswa: dashboard, jadwal, nilai, absensi, pelajaran, tugas, ujian, kuis, leaderboard, portofolio, QR attendance, survei, BKK, dan OSIS election.
- Pengumuman, notice board, chat real-time, notifikasi, broadcast tersegmentasi/terjadwal, preferensi notifikasi, reminder, dan emergency panic button.
- Adapter notifikasi dinamis untuk email, SMS, WhatsApp, push, dan webhook; credential disimpan terenkripsi dan tidak dikirim ke response/API.
- Booking konferensi orang tua-guru, slot jadwal, reminder, dan histori komunikasi.
- Website sekolah, homepage builder, custom page, hero, fitur, galeri, statistik, berita, kontak, CTA, dan widget custom HTML.
- Event sekolah, RSVP, donasi, fundraising campaign publik, alumni event, alumni directory, tracer study, job board, dan BKK.

#### 12. Laporan, analitik, dan intelligence

- Custom report builder dengan drag-and-drop, enam sumber data, filter, preview, dan export.
- Laporan SPP aging, kehadiran, distribusi nilai, disiplin, cash flow, PPDB funnel, HR, perpustakaan, procurement, inventaris, dan transportasi.
- Dashboard eksekutif, School Intelligence, learning analytics, risk distribution, dropout prediction, benchmark antar sekolah, radar chart, ranking, dan platform analytics.
- Chart interaktif, drill-down, filter periode, export PDF/CSV/XLSX, dan laporan berbasis role.
- Anomaly detection dari absensi dan transaksi; AI Chat-with-data untuk analisis data sekolah.

#### 13. Workflow, automation, dan dokumen

- Generic Workflow & Approval Engine untuk approval/rejection, alasan, My Tasks, threshold transaksi, dan audit trail.
- Automation engine berbasis trigger → action untuk invoice, reminder pembayaran, laporan harian, nilai, risk alert, expiry dokumen/kontrak, dan reminder PTM.
- Manajemen dokumen dengan versioning, approval, share link, metadata, dan ownership.
- Surat-menyurat, template, nomor otomatis, surat masuk/keluar, disposisi, agenda rapat, notulen, task kantor, sertifikat, dan PDF.
- Tanda tangan digital, PIN/hash verification, QR verifikasi raport/dokumen, dan public verification URL.
- Import CSV dengan preview/error handling, export data, API v1, OpenAPI/Redoc, webhooks, dan offline sync.

#### 14. SaaS, white-label, dan branding

- Subscription, plan, trial, billing, coupon, usage analytics, MRR, churn, growth, dan tenant management.
- Custom domain, branding sekolah, logo, warna, font, radius, header/sidebar/table color, serta white-label.
- Lima tema sekolah dan lima tema landing melalui registry; konfigurasi dapat diubah per sekolah/platform.
- Lisensi pairing dengan payload RSA-signed dan lock file terenkripsi AES-256-GCM.

#### 15. Mobile, PWA, dan infrastruktur

- Flutter app untuk Android/iOS dengan API Sanctum.
- PWA installable, offline-ready, IndexedDB, Service Worker v2, dan auto-sync.
- Laravel Reverb/WebSocket untuk fitur real-time.
- Redis untuk cache/queue, storage S3-compatible, Docker, Nginx, Let's Encrypt, scheduler, queue worker, dan encrypted database backup.
- Responsive mobile-first untuk dashboard, tabel, form, portal, landing page, dan halaman publik.

#### 16. SEO, marketing, dan dokumentasi publik

- Landing page publik dengan hero, value proposition, feature showcase, screenshot aplikasi, pricing, demo account, CTA source code, dan responsive animation.
- Blog dengan post, kategori, SEO metadata, RSS feed, sitemap inclusion, dan IndexNow ping saat publish/update.
- Programmatic SEO dengan route patterns untuk sekolah terbaik, alternatif, perbandingan, PPDB berdasarkan kota, alumni, donasi, event, dan halaman source-code.
- Meta title/description, canonical, Open Graph, Twitter Card, JSON-LD, sitemap dinamis/cache, robots.txt, dan halaman dokumentasi `/docs`.
- API documentation OpenAPI/Redoc dan halaman verifikasi publik.

### Integrasi end-to-end penting

| Alur | Hasil |
|---|---|
| CBT → Nilai → Raport | Auto-grading menulis nilai, analisis tersedia, raport dapat dipublikasikan dan diverifikasi via QR. |
| Bank Soal → Ujian/Kuis → Item Analysis | Soal dapat dipakai ulang dan metrik analisis dikembalikan ke bank soal. |
| PPDB → Siswa → Rombel → Billing | Pendaftar lolos dapat dikonversi menjadi siswa dan masuk proses akademik/keuangan. |
| Pembayaran → Invoice → Akuntansi | Pembayaran dan refund dapat diposting sebagai jurnal dengan reference idempoten. |
| Payroll → BPJS/PPh21 → Akuntansi | Pajak dihitung, laporan disimpan, dan jurnal payroll dibuat otomatis. |
| Automation → Notifikasi | Trigger bisnis menghasilkan log/notifikasi melalui provider yang dikonfigurasi. |
| Kursus → Progres → Sertifikat | Enrollment, prerequisite, progres modul, quiz, dan sertifikat terhubung. |
| Tanda tangan digital → Verifikasi publik | Dokumen ditandatangani lalu diverifikasi dengan PIN/hash atau QR. |

### Teknologi

| Lapisan | Teknologi |
|---|---|
| Backend | Laravel 13, PHP 8.3 |
| Frontend | Blade, Alpine.js, Tailwind CSS v4, Vite |
| Mobile | Flutter 3.x, Dart |
| Database | MySQL 8, Redis 7 |
| Storage | Local development atau storage S3-compatible |
| Auth/API | Laravel Sanctum |
| Permission | Spatie Laravel Permission |
| Real-time | Laravel Broadcasting dan Reverb |
| PDF/QR | Barryvdh DomPDF dan chillerlan QRCode |
| Charts/calendar | Chart.js dan FullCalendar.js |

### Menjalankan project

```bash
git clone https://github.com/linducip2208/sekolah.git
cd sekolah
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
npm run build
php artisan serve
```

PowerShell Windows:

```powershell
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
npm run build
Start-Process powershell -ArgumentList '-NoExit','-Command',"Set-Location '$((Get-Location).Path)'; `$host.UI.RawUI.WindowTitle = 'terminal · eschool'; php artisan serve --host=127.0.0.1 --port=8765"
```

Untuk konfigurasi production, lihat [DEPLOYMENT.md](DEPLOYMENT.md), [deploy/nginx.conf](deploy/nginx.conf), dan [deploy/supervisor.conf](deploy/supervisor.conf).

### Akun demo

| Peran | Email | Password | Cakupan |
|---|---|---|---|
| Super Admin | `super@sikadpro.app` | `SuperAdmin123!` | Semua tenant, plan, subscription, dan analitik platform |
| Admin Sekolah | `admin@sman1demo.sch.id` | `Admin123!` | Operasional dan konfigurasi satu sekolah |
| Guru | `guru1@sman1demo.sch.id` | `Guru123!` | Kelas, jadwal, tugas, nilai, dan presensi |
| Siswa | `siswa0_0@sman1demo.sch.id` | `Siswa123!` | Portal siswa dan pembelajaran pribadi |

### Screenshot dan verifikasi responsive

| Set | Lokasi | Isi |
|---|---|---|
| Desktop 1440×900 | `public/marketing/screens/` | Landing, dashboard, siswa, invoice, PPDB, laporan, dan halaman fitur |
| Mobile 414×896 | `public/marketing/screens-mobile/` | Dashboard, list, form, dan portal versi mobile |
| Dark mode | `public/marketing/screens-dark/` | Login, dashboard, siswa, invoice, dan My Work |
| Portal | `public/marketing/screens-portal/` | Portal siswa dan orang tua desktop/mobile |

```bash
node scripts/screenshot.cjs
node scripts/screenshot-mobile.cjs
node scripts/screenshot-dark.cjs
node scripts/screenshot-portal.cjs
node scripts/responsive-audit.cjs
```

### Quality dan dokumentasi lanjutan

- Test suite tersedia di `tests/Feature` dan `tests/Unit`.
- Jalankan test dengan `php artisan test`.
- Audit database, tenant isolation, workflow, UX, dan maturity tersedia di folder `docs/`.
- Seluruh provider pihak ketiga yang dapat dikonfigurasi menggunakan credential terenkripsi dan input admin; aplikasi tidak mengunci user pada vendor atau model tertentu.

### Lisensi

Sikad Pro — source code tersedia melalui [whitelabel.co.id](https://whitelabel.co.id). Lisensi pairing diperlukan untuk penggunaan production.

## English

### Product overview

Sikad Pro is a multi-tenant school management ERP built with Laravel. It centralizes academic administration, student lifecycle, admissions, finance, payroll, facilities, communication, analytics, AI-assisted workflows, parent/student portals, and SaaS white-label operations.

### Complete feature inventory

- **School platform and access:** multi-tenancy with school isolation, Super Admin, school/foundation administration, subscriptions, billing, plan management, role-based access control, per-role dashboards, user/permission management, audit logs, 2FA/TOTP, rate limiting, favorites, breadcrumbs, command palette, and domain-based navigation.
- **Academic administration:** academic years, semesters, subjects, classes, sections, homerooms, curricula and versions, CP/ATP/TP competencies, learning outcomes, competency mapping, calendars, holidays, timetables, conflict checks, substitute teachers, make-up classes, QR attendance, attendance corrections, locking/reopening, and audit trails.
- **Teacher operations:** teaching journals, PROTA, PROMES, lesson plans/RPP, lesson study, rubrics, student observations, portfolios, teacher performance assessment, training, certification, and a configurable AI Teacher Assistant for lesson modules, rubrics, worksheets, question variations, remedial/enrichment, and essay grading.
- **Student 360:** student profiles, documents, parents, tags, timeline, enrollment, promotion, transfer, graduation, academic/attendance/discipline/counseling/health/finance/library/transport/achievement tabs, early warnings, risk scores, at-risk monitoring, recommendations, and dropout prediction.
- **Assessment and reporting:** question bank metadata, tags, difficulty, cognitive level, HOTS/AKM, versioning, review/approval, blueprints, packages, CBT, tokens, timers, autosave, auto-submit, quizzes, grading scales, weighted grades, grade approval/locking, item analysis, report-card PDF, interactive report cards, transcripts, QR verification, and public verification pages.
- **LMS:** online classrooms, lessons, materials, assignments, submissions, courses, enrollment, prerequisites, quizzes, discussions, certificates, progress tracking, live classes, digital library, reading progress, surveys, and portfolios.
- **Admissions/PPDB:** public registration periods, dynamic form builder, tracks, quotas, zoning, document verification, tests, interviews, scoring, selection, waiting lists, announcements, admission letters, email notices, funnel reports, batch enrollment, duplicate NISN detection, and PPDB-to-student conversion.
- **Finance and accounting:** fee structures, recurring/bulk invoices, payments, outstanding tracking, installments, overdue handling, late fees, discounts, refunds, reconciliation, budget/RKAS, planned vs actual, cash flow, school cooperative, double-entry accounting, chart of accounts, journals, trial balance, profit and loss, balance sheet, bank reconciliation, payroll, BPJS, progressive PPh21, tax profiles, reports, and KPI appraisal.
- **Dynamic integrations:** format-based payment, notification, webhook, and AI provider adapters. Administrators enter their own endpoint, credentials, headers, model, and rates; secrets are encrypted at rest and never exposed in API responses. No vendor-specific feature mapping is required.
- **HR, procurement, inventory, and assets:** staff, contracts, leave, overtime, document expiry, KPI templates/goals/appraisals, suppliers, purchase requests/orders, approval, partial receiving, budget validation, stock, transfers, adjustments, stocktaking, assets, depreciation, maintenance, QR labels, write-offs, and disposal.
- **Facilities:** library catalog, issues/returns, fines, transport vehicles/routes/stops/trips, driver schedules, GPS devices, live maps, transport attendance, hostels, rooms/beds, allocations, wardens, nightly attendance, gate passes, meal plans, visitor registration, QR badges, check-in/out, host approval, blacklist, gate devices, and digital signage.
- **Student life:** clinic/UKS visits, treatments, vaccinations, restricted health access, counseling, bullying workflows, discipline points and sanctions, extracurricular programs and capacity, attendance, certificates, achievements, digital badges, leaderboards, OSIS elections, clubs, committees, meetings, minutes, voting, scholarships, canteen cashless wallets, pesantren mode, Quran memorization, Kitab Kuning, and worship logs.
- **Portals and communication:** parent portal with child switching, attendance, marks, invoices, payments, achievements, health, counseling, discipline, report cards, conferences, and surveys; student portal with schedule, lessons, assignments, exams, quizzes, leaderboard, portfolio, QR attendance, BKK, surveys, and OSIS elections; notices, real-time chat, segmented/scheduled broadcasts, notification preferences, reminders, emergency alerts, and parent-teacher conference booking.
- **Alumni and public services:** school events and RSVP, donation/fundraising campaigns, alumni directory, tracer study, job board, BKK, alumni events, school website builder, custom pages, news, gallery, statistics, contact, CTA, and custom HTML widgets.
- **Analytics and intelligence:** report builder, fee aging, attendance, grade distribution, discipline, cash flow, PPDB funnel, HR, library, procurement, inventory, transport, executive dashboards, School Intelligence, learning analytics, risk distribution, dropout prediction, inter-school benchmarking, radar/ranking views, anomaly detection, and AI chat with school data.
- **Workflow, documents, and APIs:** generic approval engine, transaction thresholds, approve/reject reasons, My Tasks, trigger-to-action automation, invoice/payment/grade/risk/expiry/PTM reminders, document versioning, approvals, share links, correspondence, incoming/outgoing mail, meetings, tasks, certificates, digital signatures, PIN/hash verification, QR verification, CSV import preview, exports, offline sync, webhooks, API v1, and OpenAPI/Redoc documentation.
- **SaaS and white-label:** subscriptions, plans, trials, billing, coupons, MRR/churn/growth analytics, custom domains, school branding, configurable fonts/colors/radii, five school themes, five landing themes, and RSA-signed/AES-256-GCM encrypted license pairing.
- **Mobile, infrastructure, SEO, and marketing:** Flutter Android/iOS app, Sanctum API, installable PWA, IndexedDB offline sync, Reverb WebSockets, Redis queues/cache, S3-compatible storage, Docker, Nginx, Let's Encrypt, scheduler, encrypted backups, responsive mobile-first UI, animated marketing landing page, public docs, blog/RSS, programmatic SEO, JSON-LD, canonical/OG/Twitter metadata, dynamic sitemap, robots.txt, IndexNow, and public report verification.

### Key end-to-end workflows

CBT → marks → report card; question bank → exams/quizzes → item analysis; PPDB → student → class → billing; payment → invoice → accounting; payroll → tax/BPJS → accounting; automation → notifications; course → progress → certificate; and digital signature → public verification.

### Stack, setup, demo access, screenshots, testing, deployment, and license

The application uses Laravel 13/PHP 8.3, Blade/Alpine/Tailwind v4/Vite, Flutter/Dart, MySQL, Redis, Sanctum, Spatie Permission, Reverb, DomPDF, QRCode, Chart.js, and FullCalendar. Follow the setup commands, demo-account table, screenshot scripts, quality notes, [DEPLOYMENT.md](DEPLOYMENT.md), and license terms in the [Bahasa Indonesia section](#bahasa-indonesia).

## العربية

### نظرة عامة على المنتج

Sikad Pro هو نظام متكامل لإدارة المدارس مبني باستخدام Laravel ويدعم تعدد المدارس والمستأجرين. يجمع الإدارة الأكاديمية، شؤون الطلاب، القبول، المالية، الرواتب، المرافق، التواصل، التحليلات، الذكاء الاصطناعي، بوابات أولياء الأمور والطلاب، وإدارة SaaS والعلامة البيضاء في منصة واحدة.

### قائمة الميزات الكاملة

- **منصة المدرسة والصلاحيات:** تعدد المستأجرين مع عزل بيانات المدرسة، مدير النظام، إدارة المدارس والمؤسسات، الخطط والاشتراكات والفوترة، التحكم في الصلاحيات حسب الدور، لوحات معلومات خاصة بكل دور، إدارة المستخدمين، سجل التدقيق، المصادقة الثنائية 2FA/TOTP، تحديد المعدل، المفضلة، مسار التنقل، لوحة الأوامر، والتنقل المنظم حسب المجال.
- **الإدارة الأكاديمية:** السنوات الدراسية، الفصول، المواد، الصفوف، الشعب، الفصول الدراسية، المناهج وإصداراتها، CP/ATP/TP ومخرجات التعلم، خرائط الكفاءات، التقويم والعطلات، الجداول، فحص تعارض الوقت والغرف، المعلم البديل، الحصص التعويضية، الحضور بواسطة QR، تصحيح الحضور، القفل وإعادة الفتح، وسجل التدقيق.
- **عمليات المعلمين:** يوميات التدريس، PROTA، PROMES، خطط الدروس/RPP، دراسة الدرس، rubrics، ملاحظات الطلاب، ملفات الإنجاز، تقييم أداء المعلم، التدريب، الشهادات، ومساعد معلم بالذكاء الاصطناعي لإنشاء الوحدات والخطط والمعايير وأوراق العمل وتنوع الأسئلة والعلاج والإثراء وتصحيح المقالات.
- **ملف الطالب 360:** ملف الطالب، الوثائق، أولياء الأمور، الوسوم، الخط الزمني، التسجيل، الترفيع، النقل، التخرج، تبويبات الأكاديميات والحضور والانضباط والإرشاد والصحة والمالية والمكتبة والنقل والإنجازات، الإنذار المبكر، درجة المخاطر، متابعة الطلاب المعرضين للخطر، والتنبؤ بالتسرب.
- **الاختبارات والتقييم والتقارير:** بنك أسئلة بالوسوم ومستوى الصعوبة والمستوى المعرفي وHOTS/AKM، الإصدارات والمراجعة والموافقة والمخططات والحزم، اختبارات CBT، الرموز والمؤقت والحفظ التلقائي والإرسال التلقائي، الاختبارات القصيرة، مقاييس الدرجات، اعتماد وقفل الدرجات، تحليل صعوبة وتمييز ومشتتات الأسئلة، تقارير PDF، تقارير تفاعلية، كشوف الدرجات، والتحقق بواسطة QR.
- **التعلم الإلكتروني LMS:** الفصول الافتراضية، الدروس، المواد، الواجبات، التسليمات، الدورات، التسجيل، المتطلبات السابقة، الاختبارات، المناقشات، الشهادات، تتبع التقدم، الفصول المباشرة، المكتبة الرقمية، تقدم القراءة، الاستبيانات، وملفات إنجاز الطلاب.
- **القبول PPDB:** فترات التسجيل العامة، منشئ النماذج الديناميكي، المسارات، الحصص، التوزيع الجغرافي، التحقق من الوثائق، الاختبارات والمقابلات والتقييم، الاختيار وقائمة الانتظار والإعلانات وخطابات القبول، الإشعارات والتقارير، التسجيل الجماعي، منع تكرار NISN، وتحويل المقبولين إلى طلاب.
- **المالية والمحاسبة:** هياكل الرسوم، الفواتير المتكررة والجماعية، المدفوعات، الأقساط، المتأخرات والغرامات والخصومات والاسترداد والتسوية، الميزانية/RKAS، التدفق النقدي، التعاونية المدرسية، المحاسبة ذات القيد المزدوج، دليل الحسابات، القيود، ميزان المراجعة، الأرباح والخسائر، الميزانية العمومية، تسوية البنك، الرواتب، BPJS، ضريبة PPh21 التصاعدية، وملفات تقييم الأداء.
- **التكاملات الديناميكية:** موصلات مبنية على تنسيق API للدفع والإشعارات والويب هوك والذكاء الاصطناعي. يدخل المسؤول الرابط وبيانات الاعتماد والرؤوس والنموذج والأسعار بنفسه؛ تُشفّر الأسرار أثناء التخزين ولا تظهر في API. لا يوجد ربط إلزامي بمورّد محدد.
- **الموارد البشرية والمشتريات والمخزون والأصول:** الموظفون والعقود والإجازات والعمل الإضافي وانتهاء الوثائق وقوالب وأهداف وتقييمات KPI، الموردون وطلبات وأوامر الشراء والموافقات والاستلام الجزئي، المخزون والنقل والتعديل والجرد، الأصول والإهلاك والصيانة وملصقات QR والشطب والتخلص.
- **المرافق:** فهرس المكتبة والإعارة والإرجاع والغرامات، المركبات والطرق والمحطات والرحلات وجداول السائقين وأجهزة GPS والخرائط المباشرة وحضور النقل، السكن والغرف والأسرة والتخصيص والمشرف والحضور الليلي وتصاريح الخروج وخطط الوجبات، تسجيل الزوار وشارات QR والدخول والخروج والقائمة السوداء وأجهزة البوابة واللافتات الرقمية.
- **حياة الطالب:** زيارات العيادة والعلاج والتطعيم مع صلاحيات صحية مقيدة، الإرشاد والتنمر، نقاط الانضباط والعقوبات، الأنشطة اللامنهجية والحضور والشهادات، الإنجازات والشارات الرقمية ولوحات المتصدرين، انتخابات OSIS والأندية واللجان والاجتماعات والتصويت، المنح، المقصف غير النقدي والمحفظة، ووضع المدارس الدينية وحفظ القرآن وKitab Kuning وسجل العبادة.
- **البوابات والتواصل:** بوابة ولي الأمر مع تبديل الأبناء والحضور والدرجات والفواتير والمدفوعات والإنجازات والصحة والإرشاد والانضباط والتقارير والمؤتمرات والاستبيانات؛ بوابة الطالب للجدول والدروس والواجبات والاختبارات والملف والـQR وBKK وانتخابات OSIS؛ الإعلانات والدردشة المباشرة والبث المجزأ والمجدول والتذكيرات والتنبيهات الطارئة وحجز مؤتمرات المعلمين وأولياء الأمور.
- **الخريجون والخدمات العامة:** الفعاليات والتسجيل RSVP والتبرعات وحملات جمع التمويل ودليل الخريجين ودراسة التتبع ولوحة الوظائف وBKK وفعاليات الخريجين ومنشئ موقع المدرسة والصفحات المخصصة والأخبار والمعرض والإحصاءات ووسائل التواصل.
- **التقارير والتحليلات والذكاء:** منشئ التقارير، أعمار رسوم الدراسة، الحضور، توزيع الدرجات، الانضباط، التدفق النقدي، مسار PPDB، تحليلات الموارد البشرية والمكتبة والمشتريات والمخزون والنقل، لوحة الإدارة التنفيذية، ذكاء المدرسة، تحليلات التعلم، المخاطر، التنبؤ بالتسرب، المقارنة بين المدارس، اكتشاف الشذوذ، والدردشة مع بيانات المدرسة.
- **الموافقات والوثائق وواجهات API:** محرك موافقات عام، حدود للمعاملات، أسباب القبول والرفض، المهام الشخصية، أتمتة trigger → action، تذكيرات الفواتير والدفع والدرجات والمخاطر وانتهاء الوثائق وPTM، إدارة الإصدارات والروابط المشتركة، المراسلات والبريد والاجتماعات والمهام والشهادات، التوقيع الرقمي والتحقق العام، استيراد CSV، التصدير، المزامنة دون اتصال، webhooks، API v1، وتوثيق OpenAPI/Redoc.
- **SaaS والعلامة البيضاء:** الاشتراكات والخطط والتجارب والفوترة والقسائم وتحليلات MRR/churn/growth، النطاقات المخصصة، العلامة التجارية، الألوان والخطوط وأنصاف الأقطار، خمس سمات للمدرسة وخمس سمات للصفحة التعريفية، وترخيص pairing بتوقيع RSA وتشفير AES-256-GCM.
- **الهاتف والبنية التحتية وSEO والتسويق:** تطبيق Flutter لنظامي Android وiOS، API Sanctum، PWA قابلة للتثبيت، IndexedDB والمزامنة دون اتصال، Reverb/WebSockets، Redis، تخزين S3، Docker، Nginx، Let's Encrypt، المجدول والنسخ الاحتياطي المشفر، واجهة متجاوبة، صفحة تسويقية متحركة، مدونة وRSS، SEO برمجي، JSON-LD، canonical وOG وTwitter، sitemap ديناميكي، robots.txt، IndexNow، وتحقق عام من التقارير.

### تدفقات العمل المتكاملة

CBT → الدرجات → التقرير؛ بنك الأسئلة → الاختبارات → تحليل العناصر؛ PPDB → الطالب → الصف → الفوترة؛ الدفع → الفاتورة → المحاسبة؛ الرواتب → الضرائب/BPJS → المحاسبة؛ الأتمتة → الإشعارات؛ الدورة → التقدم → الشهادة؛ والتوقيع الرقمي → التحقق العام.

### التقنية والتشغيل والوصول التجريبي

يعتمد المشروع على Laravel 13 وPHP 8.3 وBlade وAlpine.js وTailwind CSS v4 وVite وFlutter وMySQL وRedis وSanctum وSpatie Permission وReverb وDomPDF وQRCode وChart.js وFullCalendar. توجد تعليمات التثبيت والنشر، حسابات العرض، لقطات الشاشة، الاختبارات، وملفات الترخيص في [قسم Bahasa Indonesia](#bahasa-indonesia)، كما توجد تفاصيل النضج والـTODO في [Feature Maturity Audit](docs/FEATURE-MATURITY-AUDIT.md) و[Roadmap](docs/ROADMAP.md).

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DocsController extends Controller
{
    private array $roles = [
        'admin' => [
            'title'    => 'Panduan Administrator Sekolah',
            'kicker'   => 'Manus Magistri',
            'lead'     => 'Untuk kepala sekolah, admin TU, dan staf operasional yang mengelola seluruh sistem dari panel sekolah. Mencakup semua 47 modul ERP.',
            'login_url' => '/admin/login',
            'login_label' => 'Login Administrator',
            'credentials' => [
                'note' => 'Akun demo default — wajib diganti saat go-live production.',
                'accounts' => [
                    ['email' => 'admin@sman1demo.sch.id', 'password' => 'Admin123!', 'school' => 'SMA Negeri 1 Demo (sman1demo)', 'role' => 'admin'],
                    ['email' => 'admin@demo.sikadpro.app', 'password' => 'password',  'school' => 'Demo School (demo)',          'role' => 'admin'],
                ],
            ],
            'sections' => [
                ['Akses Awal & Onboarding', [
                    'Buka {url}/admin/login pada browser desktop atau mobile (responsive).',
                    'Masukkan email dan kata sandi yang diberikan operator platform saat aktivasi sekolah.',
                    'Login otomatis redirect ke /admin/dashboard berdasarkan role Anda.',
                    'Selesaikan onboarding wizard: profil sekolah, logo, tahun ajaran, dan rekening keuangan.',
                    'Edit profil & ganti password kapan saja melalui /profile.',
                    'Tersedia switcher bahasa Indonesia/English di topbar (?lang=id atau ?lang=en).',
                    'Tekan Cmd/Ctrl+K untuk Global Search lintas modul (siswa, staff, invoice, pengumuman).',
                ]],
                ['Modul Akademik (11 sub-modul)', [
                    'Tahun Ajaran — buat & set aktif (CRUD lengkap).',
                    'Mata Pelajaran — kode, nama, KKM per kurikulum.',
                    'Kelas (Class) — tingkat kelas (10/11/12 atau 1-6).',
                    'Section — kelompok kelas (A, B, C, dst).',
                    'ClassSection (Rombel) — kombinasi class+section dengan wali kelas & ruang.',
                    'Medium — bahasa pengantar (ID/EN/Bilingual).',
                    'Siswa — CRUD + bulk import CSV + export + filter per rombel.',
                    'Staff & Guru — CRUD, role assignment, mapel yang diampu.',
                    'Absensi Harian — input per pertemuan, rekap bulanan, persentase.',
                    'Timetable Builder — grid 7 hari × 8 slot waktu drag-and-drop.',
                    'Ujian — buat exam group, exam term, mata uji, jadwal.',
                    'Input Nilai Batch — auto-grade A-E berdasar KKM, generate raport PDF.',
                ]],
                ['Modul Keuangan (SPP & Payroll)', [
                    'Fee Structure — komponen biaya per rombel (SPP, gedung, seragam, kegiatan).',
                    'Invoice Generate Batch — buat invoice untuk seluruh rombel sekaligus.',
                    'Pembayaran Manual — record bayar tunai/transfer dengan kuitansi PDF.',
                    'Pembayaran Online — Midtrans/Xendit/Tripay/dll via Payment Provider config.',
                    'Laporan Keuangan — Cash Summary (income vs expense), SPP Aging 30/60/90, Outstanding list.',
                    'Cash Flow Chart — grafik 12 bulan terakhir.',
                    'Export CSV — semua transaksi keuangan ter-tanggal.',
                    'Komponen Gaji — buat struktur gaji per kategori staff.',
                    'Slip Gaji Bulk — generate semua slip sekaligus, cetak PDF.',
                ]],
                ['Modul Komunikasi', [
                    'Notice Board — pengumuman target audience (semua, role tertentu, rombel tertentu) + scheduled publish.',
                    'Chat — inbox internal antara guru, admin, orang tua, siswa.',
                    'Notifikasi — log push notif (FCM siap), email (SMTP), SMS gateway.',
                    'Bulk action — broadcast ke ribuan penerima sekaligus.',
                ]],
                ['Modul Fasilitas', [
                    'Perpustakaan — buku, kategori, peminjaman + return + denda otomatis.',
                    'Asrama (Hostel) — bangunan, kamar, alokasi siswa.',
                    'Transportasi — kendaraan, rute, supir, jadwal.',
                    'Bus Tracking — vehicle trips realtime (ID Gate ready).',
                ]],
                ['Modul Siswa & Pendaftaran', [
                    'PPDB Online — buat periode + form, jalur (zonasi/prestasi/reguler).',
                    'PPDB Zonasi — set district/subdistrict + jarak.',
                    'UKS / Klinik — visit log, vaccination tracking, medical history.',
                    'BP/BK — counseling sessions + bullying reports + risk score.',
                    'Disiplin — kategori pelanggaran + records + leaderboard.',
                    'ID Gate — devices, scan events (RFID/QR check-in/out).',
                ]],
                ['Modul Pengajaran (AI-powered)', [
                    'Lesson Plan / RPP — template per pertemuan, kompetensi, durasi.',
                    'Live Class — sesi Zoom/Meet/Jitsi + recording link.',
                    'AI Assistant — BYOK provider (OpenAI/Claude/Gemini/DeepSeek/Ollama, dll). User input API key di /admin/ai-providers.',
                    'Kantin Cashless — kategori, menu, dompet siswa, topup.',
                    'Pesantren / Madrasah — hafalan target ranges, kitab kuning, ibadah log harian.',
                    'Question Bank — bank soal kategori + auto-shuffle.',
                ]],
                ['Modul Engagement', [
                    'Event Management — kalender, RSVP, kapasitas.',
                    'Donasi — campaigns, list donatur, target progress bar.',
                    'Prestasi — kategori, records per siswa.',
                    'Beasiswa — programs + application form + workflow approval.',
                    'Alumni — profile, alumni events, alumni jobs board.',
                    'Career Guidance — assessment, college database, internship matching.',
                    'Digital Badges — gamification.',
                ]],
                ['Modul Operasional', [
                    'Visitor Management — log tamu + blacklist + photo capture.',
                    'Inventaris / Aset — kategori, barang, peminjaman, depresiasi.',
                    'Dapodik Sync — config NPSN + encrypted credentials.',
                    'Yayasan Dashboard — multi-school overview untuk owner foundation.',
                    'Daily Report — laporan harian per kelas (template flexible).',
                    'Extracurricular — kegiatan, registrasi, attendance.',
                    'Learning Analytics — engagement metrics, dropout risk.',
                ]],
                ['Konfigurasi Penting', [
                    'Branding — Pengaturan → Branding: logo, favicon, warna, latar login.',
                    'Payment Provider — Pengaturan → Provider: input API key Midtrans/Xendit/Tripay/dll. Format-agnostic, BYOK.',
                    'Metode Pembayaran — pilih channel aktif (QRIS, VA, e-wallet, transfer).',
                    'Roles & Permissions — Spatie Laravel-Permission per role.',
                    'AI Provider — input API key sendiri (OpenAI compatible/Anthropic/Gemini).',
                ]],
                ['Tools Produktivitas', [
                    'Bulk Select Actions — sticky action bar di list page (siswa, staff, invoice, pengumuman): activate/deactivate/delete massal.',
                    'Bulk Import CSV — template download untuk siswa & staff.',
                    'Cetak PDF — Invoice, Kuitansi A5, Slip Gaji, ID Card 86×54mm landscape, Raport.',
                    'Global Search — Cmd/Ctrl+K, debounced, lintas-modul.',
                    'PWA — install ke homescreen mobile, offline mode untuk halaman yg sudah dikunjungi.',
                    'Multi-language — switch ID/EN dari topbar, tersimpan ke session & user.locale.',
                    'API Docs — buka {url}/api-docs (Redoc) untuk dokumentasi REST.',
                ]],
                ['Best Practice', [
                    'Buat akun terpisah untuk setiap staf — jangan share kredensial.',
                    'Aktifkan 2FA di profil personal untuk akun dengan akses keuangan.',
                    'Lakukan import data master (siswa, guru) sebelum tahun ajaran dimulai.',
                    'Set up auto-backup harian + simpan snapshot mingguan ke S3/cloud.',
                    'Review SPP Aging report mingguan — tindak lanjuti yang 60+ hari.',
                ]],
            ],
        ],
        'parent' => [
            'title'    => 'Panduan Orang Tua &amp; Wali Murid',
            'kicker'   => 'Familiae Liber',
            'lead'     => 'Akses transparan ke perkembangan dan kewajiban administrasi anak Anda. 7 tab per anak — semua data terbuka untuk wali.',
            'login_url' => '/portal/dashboard',
            'login_label' => 'Buka Portal Keluarga',
            'credentials' => [
                'note' => 'Akun orang tua dibuatkan otomatis oleh TU sekolah ketika anak terdaftar. Hubungi TU jika belum menerima kredensial.',
                'accounts' => [],
            ],
            'sections' => [
                ['Akses Awal', [
                    'Buka {url}/admin/login atau klik tautan dari notifikasi email/SMS.',
                    'Login menggunakan email atau nomor HP terdaftar di sekolah.',
                    'Setelah login, otomatis redirect ke /portal/dashboard.',
                    'Jika belum terdaftar, hubungi TU sekolah untuk diberikan akses.',
                    'Tersedia switcher bahasa Indonesia/English di topbar.',
                ]],
                ['Dashboard Wali Multi-Anak', [
                    'Lihat semua anak Anda dalam satu tampilan kartu.',
                    'Click anak → 7 tab detail (overview, absensi, nilai, UKS, disiplin, prestasi, konseling).',
                    'Quick action: bayar tagihan terdekat, chat wali kelas, lacak bus.',
                ]],
                ['7 Tab per Anak', [
                    'Overview — info sekolah, kelas, NIS, foto, ringkasan terkini.',
                    'Absensi — riwayat hadir/sakit/izin/alpa per bulan + persentase.',
                    'Nilai — raport, nilai harian, capaian per mata pelajaran.',
                    'UKS — riwayat klinik, vaksinasi, alergi, kondisi medis penting.',
                    'Disiplin — pelanggaran, sanksi, rekam jejak.',
                    'Prestasi — sertifikat, kategori, level (sekolah/kota/provinsi/nasional).',
                    'Konseling — sesi BK + catatan (privasi terjaga, hanya wali yang lihat).',
                ]],
                ['Pembayaran SPP & Non-SPP', [
                    'Lihat semua tagihan SPP & non-SPP anak Anda.',
                    'Bayar online via QRIS, virtual account, e-wallet, atau transfer manual.',
                    'Unduh kuitansi pembayaran berformat PDF (A5).',
                    'History transaksi tersimpan rapi per anak.',
                ]],
                ['Layanan Tambahan', [
                    'Lacak posisi bus sekolah secara realtime.',
                    'Cek saldo kantin cashless anak & top-up langsung.',
                    'Pantau buku perpustakaan yang sedang dipinjam + denda jika ada.',
                    'Daftar event sekolah & RSVP.',
                    'Donasi ke kampanye sekolah (alumni network ready).',
                ]],
                ['Komunikasi', [
                    'Terima pengumuman & chat langsung dengan wali kelas.',
                    'Notifikasi push (mobile) + email saat ada update penting.',
                    'Reply pengumuman / izin sakit langsung dari portal.',
                ]],
                ['Aplikasi Mobile (Flutter)', [
                    'Tersedia aplikasi Android & iOS untuk pengalaman terbaik.',
                    'Aktifkan notifikasi push untuk update realtime.',
                    'Gunakan ID anak (QR code) untuk gerbang elektronik.',
                    'Install PWA dari browser: tap "Add to Home Screen" — bisa dipakai offline (halaman yang sudah dikunjungi).',
                ]],
                ['Pertanyaan Pembayaran?', [
                    'Pembayaran berhasil tapi status belum berubah? Tunggu maksimal 10 menit untuk reconciliation otomatis.',
                    'Hubungi bagian keuangan sekolah dengan menyertakan reference number transaksi.',
                ]],
            ],
        ],
        'teacher' => [
            'title'    => 'Panduan Guru &amp; Tenaga Pendidik',
            'kicker'   => 'Magister Disciplinarum',
            'lead'     => 'Untuk guru kelas, guru mata pelajaran, dan wali kelas — mengajar lebih efisien dengan AI assistant, RPP digital, dan bank soal otomatis.',
            'login_url' => '/admin/login',
            'login_label' => 'Login Guru',
            'credentials' => [
                'note' => 'Akun guru demo (3 wali kelas — semua di sekolah SMA Negeri 1 Demo).',
                'accounts' => [
                    ['email' => 'guru1@sman1demo.sch.id', 'password' => 'Guru123!', 'school' => 'Wali Kelas 10', 'role' => 'teacher'],
                    ['email' => 'guru2@sman1demo.sch.id', 'password' => 'Guru123!', 'school' => 'Wali Kelas 11', 'role' => 'teacher'],
                    ['email' => 'guru3@sman1demo.sch.id', 'password' => 'Guru123!', 'school' => 'Wali Kelas 12', 'role' => 'teacher'],
                ],
            ],
            'sections' => [
                ['Akses Awal', [
                    'Login melalui {url}/admin/login dengan kredensial yang diberikan TU.',
                    'Otomatis redirect ke /guru (Teacher Dashboard).',
                    'Aplikasi mobile guru tersedia di Android & iOS untuk akses lapangan.',
                    'PWA installable — tap "Add to Home Screen" di browser mobile.',
                ]],
                ['Dashboard Guru', [
                    'Rombel saya — daftar kelas yang Anda ampu.',
                    'Jadwal hari ini — slot mengajar dengan ruang & topik.',
                    'Quick actions — input absensi, upload materi, koreksi tugas, dll.',
                    'Notifikasi tugas perlu dikoreksi.',
                ]],
                ['Tugas Sehari-Hari', [
                    'Input absensi siswa per pertemuan (manual atau otomatis dari ID gate).',
                    'Upload materi pelajaran (PDF, video, link YouTube).',
                    'Buat tugas dengan deadline + rubric.',
                    'Koreksi submission siswa langsung dari panel.',
                    'Input nilai harian, UTS, UAS, deskripsi sikap.',
                    'Lihat capaian kompetensi per siswa.',
                ]],
                ['RPP / Lesson Plan Digital', [
                    'Template per pertemuan — kompetensi dasar, tujuan, durasi, alur.',
                    'Tautkan ke materi & tugas terkait.',
                    'Print PDF untuk arsip / kepala sekolah.',
                ]],
                ['AI Assistant (BYOK)', [
                    'Generate ringkasan materi otomatis dari teks panjang.',
                    'Buat soal latihan dari materi yang sudah ada (multiple-choice, essay).',
                    'Translate materi multi-bahasa.',
                    'Provider AI dipilih oleh administrator (OpenAI / Claude / Gemini / DeepSeek / Ollama lokal).',
                    'Tidak ada vendor lock-in — sekolah pakai API key sendiri.',
                ]],
                ['Live Class', [
                    'Sesi virtual via integrasi Zoom / Google Meet / Jitsi.',
                    'Auto-record (jika provider mendukung).',
                    'Attendance otomatis via join time.',
                ]],
                ['Bank Soal & Ujian', [
                    'Akses bank soal sekolah (kategori per mapel).',
                    'Buat ujian dengan auto-shuffle question + multi-version.',
                    'Auto-grade untuk multiple-choice, manual untuk essay.',
                    'Adaptive learning — soal menyesuaikan tingkat siswa.',
                ]],
                ['Komunikasi', [
                    'Chat langsung dengan orang tua melalui inbox internal.',
                    'Kirim pengumuman ke kelas / wali murid.',
                    'Notifikasi push saat ada balasan.',
                ]],
                ['Pesantren / Madrasah Mode', [
                    'Tracking hafalan (target ranges juz/surah).',
                    'Kitab kuning progress per siswa.',
                    'Ibadah log harian (sholat, tilawah, dzikir).',
                ]],
            ],
        ],
        'student' => [
            'title'    => 'Panduan Siswa',
            'kicker'   => 'Discipulus',
            'lead'     => 'Untuk siswa SD, SMP, SMA, dan SMK — pembelajaran terstruktur, komunikasi terhubung, gamifikasi prestasi.',
            'login_url' => '/admin/login',
            'login_label' => 'Login Siswa',
            'credentials' => [
                'note' => '90 akun siswa demo (10 siswa × 9 class section). Pola email: siswa{0-8}_{0-9}@sman1demo.sch.id. Password sama untuk semua.',
                'accounts' => [
                    ['email' => 'siswa0_0@sman1demo.sch.id', 'password' => 'Siswa123!', 'school' => 'Kelas 10-A · Admission ADM-0001', 'role' => 'student'],
                    ['email' => 'siswa0_1@sman1demo.sch.id', 'password' => 'Siswa123!', 'school' => 'Kelas 10-A · Admission ADM-0002', 'role' => 'student'],
                    ['email' => 'siswa3_0@sman1demo.sch.id', 'password' => 'Siswa123!', 'school' => 'Kelas 11-A · Admission ADM-0031', 'role' => 'student'],
                    ['email' => 'siswa6_0@sman1demo.sch.id', 'password' => 'Siswa123!', 'school' => 'Kelas 12-A · Admission ADM-0061', 'role' => 'student'],
                    ['email' => 'siswa8_9@sman1demo.sch.id', 'password' => 'Siswa123!', 'school' => 'Kelas 12-C · Admission ADM-0090', 'role' => 'student'],
                ],
            ],
            'sections' => [
                ['Akses Awal', [
                    'Login melalui {url}/admin/login dengan email/NIS dan kata sandi.',
                    'Otomatis redirect ke /siswa (Student Portal).',
                    'Aplikasi mobile tersedia di Android & iOS.',
                    'PWA installable di browser — buka {url}/siswa lalu tap "Add to Home Screen".',
                ]],
                ['6 Menu Utama Portal Siswa', [
                    'Beranda — ringkasan kelas, absensi hari ini, tugas akan datang.',
                    'Jadwal — pelajaran harian + kalender ujian.',
                    'Nilai — harian, UTS, UAS, raport (download PDF).',
                    'Absensi — riwayat hadir/sakit/izin/alpa.',
                    'Materi — file pelajaran, video, buku digital.',
                    'Tugas — submit online, deadline jelas, status koreksi.',
                ]],
                ['Bank Soal & Latihan Mandiri', [
                    'Latihan soal mandiri dengan koreksi otomatis.',
                    'Adaptive learning — soal menyesuaikan tingkat kemampuan.',
                    'Riwayat skor dan progress kompetensi.',
                ]],
                ['Live Class & Materi Digital', [
                    'Ikuti live class lewat Zoom/Meet/Jitsi.',
                    'Akses rekamannya jika tidak hadir.',
                    'Download materi offline.',
                ]],
                ['Layanan Sekolah', [
                    'Pinjam buku perpustakaan via katalog online.',
                    'Pesan makan kantin cashless dari dompet digital + topup wallet.',
                    'Lacak posisi bus sekolah ke rumah.',
                    'Daftar ekstrakurikuler.',
                    'Daftar event sekolah & RSVP.',
                ]],
                ['Karir & Beasiswa (SMA/SMK)', [
                    'Career assessment — tes minat & bakat.',
                    'College database — universitas Indonesia & luar negeri.',
                    'Daftar beasiswa terbuka untuk diaplikasi langsung.',
                    'Program magang & internship matching.',
                    'Digital badges — gamification capaian.',
                ]],
                ['Komunikasi', [
                    'Chat dengan guru / wali kelas.',
                    'Lihat pengumuman sekolah / kelas.',
                    'Notifikasi tugas baru, nilai keluar, deadline mendekat.',
                ]],
                ['Profile & Settings', [
                    'Buka /profile untuk edit nama, email, phone, locale.',
                    'Ganti password kapan saja.',
                    'Switcher bahasa Indonesia / English.',
                ]],
            ],
        ],
        'super-admin' => [
            'title'    => 'Panduan Operator Platform (Super Admin)',
            'kicker'   => 'Magisterium',
            'lead'     => 'Untuk pemilik platform SaaS yang mengelola banyak sekolah / yayasan dalam satu instance. Akses cross-tenant dengan tools tata kelola lengkap.',
            'login_url' => '/super/login',
            'login_label' => 'Login Operator',
            'credentials' => [
                'note' => 'Akun super admin platform (cross-tenant, school_id = null). WAJIB ganti password sebelum production.',
                'accounts' => [
                    ['email' => 'superadmin@sikadpro.app', 'password' => 'password',         'school' => 'Platform-level (semua sekolah)', 'role' => 'super_admin'],
                    ['email' => 'super@sikadpro.app',      'password' => 'SuperAdmin123!',   'school' => 'Platform-level (semua sekolah)', 'role' => 'super_admin'],
                ],
            ],
            'sections' => [
                ['Akses Awal', [
                    'Buka {url}/super/login dengan akun super_admin.',
                    'Otomatis redirect ke /super/dashboard saat login berhasil.',
                    'Akun super admin pertama dibuat saat instalasi via seeder.',
                    'Login lewat /admin/login juga otomatis ter-redirect ke /super.',
                ]],
                ['Dashboard Platform', [
                    'Total tenant aktif, MRR, growth rate, churn.',
                    'Top 5 sekolah by revenue.',
                    'Recent transactions cross-tenant.',
                    'Plan distribution & sebaran tenant.',
                ]],
                ['Tata Kelola Tenant', [
                    'Sekolah CRUD — buat sekolah baru: subdomain, paket, masa trial.',
                    'Suspend / aktifkan sekolah dengan satu klik.',
                    'Extend langganan — pilih plan + tanggal expire.',
                    'Monitor penggunaan storage, jumlah siswa, API call per tenant.',
                    'Audit log lintas tenant (siapa melakukan apa).',
                ]],
                ['Plans & Subscriptions', [
                    'Plan CRUD — nama, slug, harga, max_students, features (JSON).',
                    'Aktif/non-aktifkan plan.',
                    'Subscription transactions — semua riwayat pembayaran tenant.',
                    'Manual extend / refund / mark paid.',
                ]],
                ['Penerimaan Pembayaran Sekolah ke Anda', [
                    'Rekening Manual — bank account untuk konfirmasi transfer manual.',
                    'Gateway Online — Midtrans/Xendit/Tripay (BYOK platform-level).',
                    'CATATAN: Gateway untuk sekolah ke Anda BERBEDA dengan gateway untuk wali murid ke sekolah.',
                ]],
                ['Whitelabel Platform', [
                    'Menu Whitelabel — atur logo, favicon, warna brand, kontak yang tampil di landing page.',
                    'Atur popup, hero copy, motto, testimonials.',
                    'Semua perubahan langsung berlaku di semua halaman publik.',
                    'Sales-ready: subdomain partner, custom domain.',
                ]],
                ['Analytics & Reports', [
                    'Halaman /super/analytics — 4 KPI cards + 3 Chart.js charts.',
                    'Bar chart: revenue 12 bulan terakhir.',
                    'Line chart: pertumbuhan sekolah & siswa.',
                    'Doughnut chart: distribusi plan.',
                    'Tabel detail revenue per bulan.',
                    'Reports panel — export CSV, daily/weekly/monthly.',
                ]],
                ['System Administration', [
                    'Health Check — cek DB, cache, queue, storage.',
                    'License — config license key whitelabel.co.id.',
                    'Email Templates — editor untuk semua email transactional.',
                    'Webhook Logs — semua webhook masuk (PG, PPDB, SMS provider) untuk debug.',
                    'Maintenance Mode — toggle untuk maintenance window.',
                ]],
                ['Backup & Restore Database', [
                    'Buka /super/backups untuk backup management.',
                    'Trigger Backup — generate snapshot SQL dump.',
                    'Download — ⬇️ klik per file untuk save SQL ke local (simpan ke S3/cloud).',
                    'Upload Restore — ⬆️ upload .sql dari local untuk restore later.',
                    'Restore — ⚠️ DESTRUCTIVE. Konfirmasi ketik "RESTORE" sebelum eksekusi.',
                    'Hapus — clean up backup lama.',
                    'Otomatisasi: cron job harian "0 2 * * * curl -X POST /super/backups".',
                ]],
                ['System Configuration', [
                    'Atur trial days default, grace period, support email.',
                    'Toggle maintenance mode.',
                    'Allow / block self-registration sekolah baru.',
                    'App name, logo platform, favicon.',
                ]],
                ['Yayasan / Foundation Multi-School', [
                    'Yayasan dashboard — owner foundation lihat overview semua sekolah miliknya.',
                    'Assign foundation_admin role untuk akses cross-school dalam yayasan.',
                ]],
            ],
        ],
        'developer' => [
            'title'    => 'Panduan Developer / Integrator',
            'kicker'   => 'Codex Technicus',
            'lead'     => 'Untuk tim IT internal yayasan, developer freelance, atau partner integrator yang menghubungkan Sikad Pro dengan sistem lain. REST API + webhook + BYOK adapters.',
            'login_url' => '/api-docs',
            'login_label' => 'Buka API Documentation',
            'credentials' => [
                'note' => 'Untuk test API gunakan akun seeder di bawah. Login via POST /api/v1/auth/login → dapat Bearer token.',
                'accounts' => [
                    ['email' => 'admin@sman1demo.sch.id', 'password' => 'Admin123!',      'school' => 'Test sebagai school admin',  'role' => 'admin'],
                    ['email' => 'guru1@sman1demo.sch.id', 'password' => 'Guru123!',       'school' => 'Test sebagai teacher',       'role' => 'teacher'],
                    ['email' => 'siswa0_0@sman1demo.sch.id', 'password' => 'Siswa123!',   'school' => 'Test sebagai student',       'role' => 'student'],
                    ['email' => 'super@sikadpro.app',      'password' => 'SuperAdmin123!', 'school' => 'Test sebagai super admin',   'role' => 'super_admin'],
                ],
            ],
            'sections' => [
                ['Stack Teknologi', [
                    'Backend: Laravel 11 + PHP 8.3 + MySQL 8 + Redis 7.',
                    'Frontend Web: Blade + Alpine.js + Tailwind CSS (CDN-first, build-optional).',
                    'Mobile: Flutter 3.x.',
                    'Auth: Laravel Sanctum (SPA + token).',
                    'Permissions: Spatie Laravel-Permission.',
                    'PDF: barryvdh/laravel-dompdf.',
                    'Modules: nwidart/laravel-modules.',
                    'Multi-tenancy: shared DB + school_id global scope.',
                ]],
                ['REST API', [
                    'Base URL: {url}/api/v1',
                    'Authentication: Bearer token via /api/v1/auth/login.',
                    'Health endpoints: /api/v1/health, /api/v1/health/deep, /api/v1/health/metrics.',
                    'Multi-tenant: setiap request mengikat school_id otomatis dari token.',
                    'Rate-limited per role.',
                    'Response: JSON konsisten { success, data, message }.',
                ]],
                ['API Documentation Page', [
                    'Buka {url}/api-docs untuk Redoc (rendered OpenAPI 3.0 spec).',
                    'OpenAPI spec raw: {url}/api-docs/openapi.json',
                    'Interactive try-it-out via Redoc UI.',
                ]],
                ['Webhook Inbound', [
                    'Payment gateway → POST /api/v1/webhooks/payment/{provider}',
                    'PPDB → konfigurable per sekolah.',
                    'SMS delivery report → POST /api/v1/webhooks/sms/{provider}',
                    'Webhook logs di /super/webhook-logs untuk debugging.',
                ]],
                ['BYOK Integration Pattern', [
                    'AI providers — adapter format-based (OpenAICompatible, Anthropic, Gemini, ImageGeneric).',
                    'Tidak ada hardcoded vendor — semua format-based.',
                    'Payment — adapter per format (REDIRECT_CHECKOUT, VIRTUAL_ACCOUNT, EMBED, QR, MANUAL).',
                    'Tambah provider baru: implement contract interface + register di factory.',
                    'Semua API key encrypted-at-rest dengan Laravel Crypt.',
                    'Preset templates di storage/app/{type}-presets/*.json untuk autofill — bukan hardcode.',
                ]],
                ['Multi-Language Setup', [
                    'lang/id/* dan lang/en/* (common, modules, nav).',
                    'Switcher: ?lang=id atau ?lang=en di URL.',
                    'Middleware SetLocale auto-pick: query → session → user.locale → default.',
                    'Tambah bahasa baru: tambahkan lang/{code}/, register di middleware.',
                ]],
                ['PWA / Service Worker', [
                    'manifest.webmanifest — installable di Android/iOS/desktop.',
                    'public/sw.js — network-first untuk HTML, cache-first untuk static asset.',
                    'Offline mode — fallback ke halaman yang sudah dikunjungi.',
                    'Skip cache untuk login & API auth route (security).',
                ]],
                ['Bulk Action API', [
                    'POST /admin/students/bulk { action, ids[] }',
                    'Action: delete | activate | deactivate | assign_class.',
                    'Berlaku juga untuk staff, invoices, notices.',
                ]],
                ['CSV Bulk Import', [
                    'Endpoint: /admin/import/students dan /admin/import/staff.',
                    'Template download: /admin/import/students/template.',
                    'Format: kolom standar nama, nis, email, kelas, dll.',
                ]],
                ['Self Hosting', [
                    'docker-compose.yml siap pakai (Nginx + PHP-FPM + MySQL + Redis).',
                    'Migration: php artisan migrate --seed.',
                    'Queue worker: php artisan queue:work redis.',
                    'Scheduler: tambahkan cron entry "* * * * * php artisan schedule:run".',
                    'Backup cron: "0 2 * * * curl -X POST /super/backups".',
                    'SSL: Let\'s Encrypt via certbot.',
                ]],
                ['Programmatic SEO', [
                    '/best-{category}, /alternatives-to-{slug}, /compare/{a}-vs-{b}.',
                    '18 pSEO routes built-in untuk landing dapat traffic organik.',
                    'JSON-LD schema (ItemList, FAQPage) auto-generated.',
                    'Sitemap dinamis di /sitemap.xml.',
                ]],
            ],
        ],
    ];

    public function index(): View
    {
        return view('docs.index', [
            'roles' => collect($this->roles)->map(fn ($data, $slug) => [
                'slug'   => $slug,
                'title'  => $data['title'],
                'kicker' => $data['kicker'],
                'lead'   => $data['lead'],
            ])->values()->all(),
        ]);
    }

    public function show(string $role): View
    {
        abort_unless(isset($this->roles[$role]), 404);

        $url      = rtrim(config('app.url'), '/');
        $data     = $this->roles[$role];
        $sections = collect($data['sections'])->map(fn ($section) => [
            'title' => $section[0],
            'items' => array_map(fn ($i) => str_replace('{url}', $url, $i), $section[1]),
        ])->all();

        return view('docs.show', [
            'role'        => $role,
            'title'       => $data['title'],
            'kicker'      => $data['kicker'],
            'lead'        => $data['lead'],
            'login_url'   => $data['login_url'],
            'login_label' => $data['login_label'],
            'credentials' => $data['credentials'] ?? null,
            'sections'    => $sections,
            'roles'       => collect($this->roles)->map(fn ($d, $s) => [
                'slug' => $s, 'title' => $d['title'], 'kicker' => $d['kicker'],
            ])->values()->all(),
        ]);
    }
}

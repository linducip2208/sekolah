<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\FontRegistry;
use App\Services\LandingThemeRegistry;
use App\Services\PlatformSettingsService;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __construct(private PlatformSettingsService $platform) {}

    public function index(): View
    {
        $theme = LandingThemeRegistry::get($this->platform->get('landing_theme'));

        // Apply platform-level color/typography overrides on top of the template.
        $p = $this->platform->all();
        foreach ([
            '--lp-primary'     => 'landing_primary',
            '--lp-accent'      => 'landing_accent',
            '--lp-background'  => 'landing_background',
            '--lp-ink'         => 'landing_text',
            '--lp-muted'       => 'landing_text_muted',
        ] as $var => $setting) {
            if (!empty($p[$setting])) {
                $theme['vars'][$var] = $p[$setting];
            }
        }

        if (!empty($p['landing_font']) && ($font = FontRegistry::get($p['landing_font']))) {
            $theme['fonts']['body']    = $font['family'];
            $theme['fonts']['display'] = $font['family'];
            $theme['fonts']['url']     = $font['url'];
        }

        $radiusMap = ['small' => ['8px', '10px', '14px'], 'large' => ['12px', '16px', '22px'], 'medium' => ['10px', '12px', '16px']];
        $radii = $radiusMap[$p['landing_radius_scale'] ?? 'medium'] ?? $radiusMap['medium'];
        $theme['vars']['--lp-radius-sm']  = $radii[0];
        $theme['vars']['--lp-radius-md']  = $radii[1];
        $theme['vars']['--lp-radius-lg']  = $radii[2];
        $theme['vars']['--lp-radius-btn'] = $radii[1];

        $screens = static fn (string $file) => asset("marketing/screens/{$file}");

        $landing = [
            'stats' => [
                ['value' => '45+', 'label' => 'Modul terintegrasi'],
                ['value' => 'Multi', 'label' => 'Tenant per sekolah'],
                ['value' => '3', 'label' => 'Portal: admin, orang tua, siswa'],
                ['value' => '100%', 'label' => 'White-label & self-host'],
            ],
            'valueProps' => [
                ['title' => 'Satu Platform', 'desc' => 'Akademik, keuangan, PPDB, HR, komunikasi, dan laporan dalam satu sistem terpadu.'],
                ['title' => 'Terhubung', 'desc' => 'Sekolah, guru, siswa, dan orang tua terhubung dalam satu alur informasi realtime.'],
                ['title' => 'Akses di Mana Saja', 'desc' => 'Desktop untuk administrasi, aplikasi mobile untuk orang tua, siswa, dan guru.'],
                ['title' => 'Dirancang untuk Sekolah', 'desc' => 'Role-based access control dan alur kerja yang mengikuti kebutuhan pendidikan.'],
            ],
            'features' => [
                'Akademik' => ['Kurikulum & CP/ATP', 'Manajemen kelas & rombel', 'Jadwal pelajaran', 'Absensi harian & QR', 'Ujian & bank soal', 'Nilai & raport PDF'],
                'Kesiswaan' => ['Database siswa', 'PPDB online', 'Disiplin & tata tertib', 'BP/BK & konseling', 'UKS / klinik', 'Ekstrakurikuler'],
                'Keuangan' => ['Struktur biaya & SPP', 'Invoice & tagihan', 'Payment gateway (BYOK)', 'Payroll guru', 'Anggaran (RKAS)', 'Laporan keuangan'],
                'Operasional' => ['Perpustakaan & e-library', 'Asrama / hostel', 'Transportasi & bus tracking', 'Inventaris & aset', 'Visitor management', 'Kantin cashless'],
                'Komunikasi' => ['Pengumuman', 'Chat antar peran', 'Notifikasi FCM / email / SMS', 'WhatsApp bot', 'Surat-menyurat', 'Webhook'],
                'AI & Analitik' => ['AI assistant', 'Penilaian essay otomatis', 'Deteksi risiko dropout', 'Learning analytics', 'Dashboard yayasan', 'Sinkronisasi Dapodik'],
            ],
            'preview' => [
                ['tab' => 'Ringkasan', 'img' => $screens('07-dashboard.png'), 'alt' => 'Dashboard admin Sikad Pro'],
                ['tab' => 'Akademik', 'img' => $screens('12-timetable.png'), 'alt' => 'Jadwal pelajaran'],
                ['tab' => 'Siswa', 'img' => $screens('09-students.png'), 'alt' => 'Manajemen data siswa'],
                ['tab' => 'Keuangan', 'img' => $screens('15-fee-invoices.png'), 'alt' => 'Invoice & tagihan SPP'],
            ],
            'gallery' => [
                ['img' => $screens('09-students.png'), 'title' => 'Manajemen Siswa', 'desc' => 'Data diri, wali, kelas, asrama, dan import CSV massal.'],
                ['img' => $screens('11-attendance.png'), 'title' => 'Absensi Harian', 'desc' => 'Check-in per kelas, rekap bulanan, notifikasi ke orang tua.'],
                ['img' => $screens('15-fee-invoices.png'), 'title' => 'Keuangan & SPP', 'desc' => 'Struktur biaya, invoice massal, rekap pembayaran, laporan outstanding.'],
                ['img' => $screens('13-exams.png'), 'title' => 'Ujian & Penilaian', 'desc' => 'Buat ujian, input nilai, kalkulasi otomatis, cetak raport.'],
                ['img' => $screens('20-ppdb-applications.png'), 'title' => 'PPDB Online', 'desc' => 'Pendaftaran publik, verifikasi berkas, seleksi, pengumuman otomatis.'],
                ['img' => $screens('17-library-books.png'), 'title' => 'Perpustakaan', 'desc' => 'Katalog buku, peminjaman, pengembalian, denda otomatis.'],
            ],
            'solutions' => [
                ['role' => 'Administrator Sekolah', 'icon' => 'school', 'desc' => 'Kelola seluruh sekolah dari satu tempat — siswa, staf, akademik, keuangan, dan laporan.', 'bullets' => ['Manajemen siswa & staf', 'Operasional akademik', 'Keuangan & SPP', 'Laporan & analitik', 'Komunikasi massal']],
                ['role' => 'Kepala Sekolah', 'icon' => 'device', 'desc' => 'Pantau kinerja sekolah secara realtime dan ambil keputusan berbasis data.', 'bullets' => ['Dashboard ringkasan', 'Rekap kehadiran & nilai', 'Approval pengadaan', 'Audit log']],
                ['role' => 'Guru', 'icon' => 'edit', 'desc' => 'Fokus mengajar — absensi, nilai, materi, dan RPP dalam satu alur kerja.', 'bullets' => ['Input nilai & absensi', 'Materi & tugas online', 'Lesson plan / RPP', 'Bank soal & ujian']],
                ['role' => 'Keuangan', 'icon' => 'user', 'desc' => 'Kelola tagihan, pembayaran, dan payroll dengan pencatatan yang rapi.', 'bullets' => ['Invoice & penerimaan', 'Payroll guru', 'Anggaran & realisasi', 'Laporan kas & piutang']],
                ['role' => 'Orang Tua', 'icon' => 'bell', 'desc' => 'Pantau perkembangan anak dari ponsel — nilai, absensi, tagihan, dan komunikasi.', 'bullets' => ['Nilai & absensi anak', 'Tagihan SPP', 'Pengumuman & chat', 'Laporan harian']],
                ['role' => 'Siswa', 'icon' => 'inbox', 'desc' => 'Akses jadwal, tugas, materi, dan nilai dalam aplikasi yang ringan.', 'bullets' => ['Jadwal & tugas', 'Materi pelajaran', 'Nilai & raport', 'Leaderboard']],
                ['role' => 'Yayasan', 'icon' => 'school', 'desc' => 'Kelola banyak sekolah cabang dengan visibilitas lintas unit yang terpusat.', 'bullets' => ['Multi-sekolah', 'Benchmark antar cabang', 'Konsolidasi laporan', 'White-label per cabang']],
            ],
            'benefits' => [
                ['title' => 'Hemat Waktu', 'desc' => 'Otomasi absensi, tagihan, dan laporan mengurangi pekerjaan administratif harian.'],
                ['title' => 'Visibilitas Jelas', 'desc' => 'Data sekolah terpusat dan realtime — tidak lagi tersebar di spreadsheet.'],
                ['title' => 'Komunikasi Lancar', 'desc' => 'Hubungkan sekolah, guru, siswa, dan orang tua lewat satu saluran notifikasi.'],
                ['title' => 'Keputusan Berbasis Data', 'desc' => 'Analitik dan laporan membantu kepala sekolah mengambil keputusan tepat.'],
            ],
            'security' => [
                ['title' => 'Role-based access', 'desc' => 'Hak akses granular per peran dengan Policy & RBAC.'],
                ['title' => 'Isolasi multi-tenant', 'desc' => 'Data terpisah per school_id pada seluruh tabel.'],
                ['title' => 'Enkripsi at-rest', 'desc' => 'API key & secret dienkripsi, tidak pernah terekspos.'],
                ['title' => '2FA & session security', 'desc' => 'Autentikasi TOTP, throttle login, dan session invalidation.'],
                ['title' => 'Audit log', 'desc' => 'Jejak aktivitas pengguna tercatat lengkap.'],
                ['title' => 'Backup otomatis', 'desc' => 'Jadwal backup database harian untuk pemulihan cepat.'],
            ],
            'testimonials' => [
                ['quote' => 'Ini adalah ruang testimoni pelanggan. Ganti dengan kutipan asli dari sekolah yang menggunakan platform.', 'name' => 'Nama Kepala Sekolah', 'role' => 'SMA Negeri Contoh', 'placeholder' => true],
                ['quote' => 'Tampilkan kisah sukses nyata — misalnya penghematan waktu administrasi atau transisi dari Excel ke sistem terpadu.', 'name' => 'Nama Administrator', 'role' => 'Yayasan Pendidikan Contoh', 'placeholder' => true],
                ['quote' => 'Gunakan testimoni yang spesifik dan jujur. Hindari klaim angka yang tidak dapat dibuktikan.', 'name' => 'Nama Bendahara', 'role' => 'Sekolah Swasta Contoh', 'placeholder' => true],
            ],
            'faqs' => [
                ['q' => 'Apa itu Sikad Pro?', 'a' => 'Platform manajemen sekolah multi-tenant berbasis cloud yang mencakup akademik, keuangan, PPDB, perpustakaan, transportasi, kantin cashless, hingga AI assistant — dalam satu sistem terintegrasi.'],
                ['q' => 'Siapa yang bisa menggunakannya?', 'a' => 'Sekolah, yayasan, dan pesantren. Tersedia portal untuk administrator, kepala sekolah, guru, keuangan, orang tua, dan siswa.'],
                ['q' => 'Apakah mendukung banyak sekolah (multi-tenant)?', 'a' => 'Ya. Setiap sekolah terisolasi datanya sendiri, dan yayasan dapat mengelola banyak cabang dari satu dashboard dengan branding terpisah.'],
                ['q' => 'Apakah bisa digunakan di mobile?', 'a' => 'Ya. Aplikasi mobile (Flutter) tersedia untuk orang tua, siswa, dan guru di Android & iOS dengan notifikasi realtime.'],
                ['q' => 'Apakah data saya aman?', 'a' => 'Isolasi multi-tenant per school_id, enkripsi at-rest untuk kunci integrasi, role-based access control, 2FA, audit log, dan backup harian.'],
                ['q' => 'Apakah ada demo?', 'a' => 'Ya. Tersedia akun demo untuk semua peran — jelajahi panel admin, portal orang tua, dan dashboard siswa tanpa mendaftar.'],
                ['q' => 'Bagaimana implementasinya?', 'a' => 'Daftar paket, lengkapi data sekolah, lalu mulai kelola. Tim mendukung migrasi data dari sistem lama dan pelatihan.'],
                ['q' => 'Apakah bisa custom branding?', 'a' => 'Ya. Logo, warna, nama, domain kustom, dan pilihan template tampilan dapat diatur sendiri — white-label siap pakai.'],
            ],
            'integration' => [
                ['title' => 'Payment gateway', 'desc' => 'Midtrans, Xendit, QRIS, VA, e-wallet, transfer manual — input kunci sendiri.'],
                ['title' => 'AI / LLM', 'desc' => 'OpenAI, Anthropic, Gemini, DeepSeek, hingga self-host (Ollama/vLLM).'],
                ['title' => 'SMS & WhatsApp', 'desc' => 'Bebas memilih penyedia notifikasi sesuai preferensi.'],
                ['title' => 'Storage', 'desc' => 'S3-compatible — AWS, Wasabi, Cloudflare R2, atau MinIO.'],
            ],
        ];

        return view('landing.themes.' . $theme['key'], [
            'theme'   => $theme,
            'landing' => $landing,
            'plans'   => Plan::where('is_active', true)->orderBy('price')->get(),
        ]);
    }
}

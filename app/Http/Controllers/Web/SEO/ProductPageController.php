<?php

namespace App\Http\Controllers\Web\SEO;

use App\Http\Controllers\Controller;
use App\Services\SEO\StructuredDataBuilder;
use Illuminate\View\View;

class ProductPageController extends Controller
{
    public function __construct(private StructuredDataBuilder $sd) {}

    private function render(array $page): View
    {
        $jsonLd = [
            $this->sd->webPage([
                'title' => $page['meta']['title'],
                'description' => $page['meta']['description'],
                'url' => url()->current(),
                'lang' => $page['lang'] ?? 'id',
                'breadcrumb' => [
                    ['name' => 'Beranda', 'url' => url('/')],
                    ['name' => $page['short'], 'url' => url()->current()],
                ],
            ]),
            $this->sd->softwareApplication([
                'description' => $page['meta']['description'],
                'url' => url()->current(),
                'lang' => $page['lang'] ?? 'id',
            ]),
        ];
        if (! empty($page['faq'])) {
            $jsonLd[] = $this->sd->faqPage(
                array_map(fn ($f) => ['question' => $f[0], 'answer' => $f[1]], $page['faq'])
            );
        }

        return view('seo.product', [
            'page' => $page,
            'jsonLd' => $jsonLd,
            'meta' => $page['meta'],
        ]);
    }

    public function sistemInformasiSekolah(): View
    {
        return $this->render([
            'short' => 'Sistem Informasi Sekolah',
            'kicker' => 'Education Management Platform',
            'title' => 'Sistem Informasi Sekolah Terpadu untuk Operasional Harian',
            'lead' => 'SIKAD PRO adalah sistem informasi sekolah multi-tenant yang menyatukan akademik, kesiswaan, keuangan, PPDB, SDM, dan komunikasi dalam satu platform. Setiap sekolah memiliki ruang data terisolasi, portal peran sendiri, dan branding sendiri.',
            'answer' => 'Sistem informasi sekolah adalah perangkat lunak yang mengelola seluruh operasional sekolah — data siswa, akademik, keuangan, dan komunikasi — dalam satu database terpusat. SIKAD PRO mengimplementasikannya sebagai School ERP multi-tenant: satu instalasi melayani banyak sekolah, masing-masing dengan isolasi data (school_id), kontrol akses berbasis peran, dan white-label.',
            'sections' => [
                ['h' => 'Satu data, banyak portal', 'p' => 'Administrator mengelola master data sekali. Guru mengisi absensi dan nilai. Orang tua memantau anak dari ponsel. Siswa mengakses jadwal dan tugas. Yayasan memantau semua cabang. Semua membaca dari sumber data yang sama, sehingga tidak ada versi spreadsheet yang berbeda-beda.'],
                ['h' => 'Alur yang saling terhubung', 'p' => 'Pendaftar PPDB yang diterima otomatis menjadi siswa dan langsung mendapat tagihan daftar-ulang. Nilai ujian CBT mengalir ke ledger nilai lalu ke raport PDF bertanda QR. Pembayaran SPP tercatat ke jurnal akuntansi double-entry. Rantai ini mengurangi input ganda dan selisih data.'],
                ['h' => 'Infrastruktur operasional', 'p' => 'Antrean latar untuk notifikasi, penjadwal harian untuk pengingat tagihan dan rekap, pencatatan audit untuk setiap perubahan sensitif, dan API versi 1 untuk integrasi aplikasi mobile maupun sistem pihak ketiga.'],
            ],
            'features' => [
                'Akademik & Kesiswaan' => ['Tahun ajaran & semester', 'Kelas, rombel & jadwal', 'Absensi harian + QR + penguncian', 'Bank soal, CBT & auto-grading', 'Nilai, skala & raport PDF-QR', 'Student 360 & lifecycle'],
                'Keuangan' => ['Struktur biaya & invoice massal', 'Cicilan, denda & refund', 'Payment gateway BYOK', 'Akuntansi double-entry + tutup periode', 'Payroll, BPJS & PPh21', 'Anggaran RKAS'],
                'Operasional' => ['PPDB online end-to-end', 'Perpustakaan & denda', 'Transportasi & GPS', 'Asrama & gate pass', 'Inventaris & aset', 'Kantin cashless'],
            ],
            'usecases' => [
                ['t' => 'Sekolah swasta 500–2000 siswa', 'd' => 'Menggantikan spreadsheet absensi, tagihan manual, dan grup chat yang berantakan dengan satu sistem.'],
                ['t' => 'Yayasan multi-cabang', 'd' => 'Satu dashboard membandingkan kinerja cabang, dengan user yayasan yang hanya melihat datanya sendiri.'],
                ['t' => 'Pesantren & madrasah', 'd' => 'Mode hafalan, kitab kuning, dan log ibadah melengkapi modul akademik umum.'],
            ],
            'faq' => [
                ['Apa itu SIKAD PRO?', 'SIKAD PRO adalah Education Management Platform (School ERP) multi-tenant: akademik, kesiswaan, keuangan, PPDB, SDM, operasional, komunikasi, analitik, dan AI dalam satu sistem.'],
                ['Apakah data tiap sekolah terpisah?', 'Ya. Setiap baris data membawa school_id dan disaring otomatis; kebijakan otorisasi menolak akses lintas sekolah (404), diverifikasi oleh regression test.'],
                ['Siapa saja yang bisa menggunakannya?', 'Administrator, kepala sekolah, guru, wali kelas, staf keuangan/HR, petugas PPDB, pustakawan, orang tua, siswa, dan admin yayasan — masing-masing dengan portal dan hak akses sendiri.'],
                ['Apakah bisa dipasang di server sendiri?', 'Ya. Mendukung Docker + Nginx + MySQL + Redis, dengan lisensi pairing per domain dan mode white-label.'],
            ],
            'related' => [
                ['t' => 'Aplikasi PPDB Online', 'u' => '/aplikasi-ppdb-online'],
                ['t' => 'Aplikasi Pembayaran SPP', 'u' => '/aplikasi-pembayaran-spp'],
                ['t' => 'Aplikasi Rapor Digital', 'u' => '/aplikasi-rapor-digital'],
                ['t' => 'Tentang SIKAD PRO', 'u' => '/tentang-sikad-pro'],
            ],
            'meta' => [
                'title' => 'Sistem Informasi Sekolah Terpadu — SIKAD PRO School ERP',
                'description' => 'SIKAD PRO: sistem informasi sekolah multi-tenant — akademik, PPDB, keuangan/SPP, LMS, absensi, raport digital, dan AI dalam satu School ERP.',
            ],
        ]);
    }

    public function aplikasiPpdbOnline(): View
    {
        return $this->render([
            'short' => 'Aplikasi PPDB Online',
            'kicker' => 'Admissions',
            'title' => 'PPDB Online: Pendaftaran Sampai Daftar Ulang Tanpa Kertas',
            'lead' => 'Kelola seluruh lifecycle penerimaan siswa baru: periode & kuota per jalur, formulir publik dinamis, verifikasi berkas, penilaian & seleksi terkunci, waiting list, pengumuman, hingga konversi otomatis menjadi siswa plus tagihan daftar-ulang.',
            'answer' => 'Aplikasi PPDB online adalah sistem pendaftaran siswa baru via web: calon mengisi formulir dan mengunggah dokumen, panitia memverifikasi dan menilai, sistem menjalankan seleksi sesuai kuota jalur, dan yang diterima otomatis menjadi siswa. Di SIKAD PRO, pendaftar yang di-enroll langsung mendapat invoice daftar-ulang bila periode mengenakan biaya formulir.',
            'sections' => [
                ['h' => 'Kuota yang tidak bisa dibobol', 'p' => 'Seleksi mengunci baris periode di database sehingga dua panitia yang menekan tombol bersamaan tidak akan melebihi kuota jalur. Nomor registrasi dibuat unik dengan retry. Setiap perubahan status tercatat di audit.'],
                ['h' => 'Jalur, zonasi & bobot nilai', 'p' => 'Reguler, prestasi, afirmasi, zonasi — masing-masing dengan kuota dan bobot penilaian sendiri. Jarak rumah ke sekolah dihitung otomatis untuk jalur zonasi. Waiting list terisi otomatis sesuai peringkat.'],
                ['h' => 'Corong yang terlihat', 'p' => 'Dari draft ke submit, verifikasi, diterima, hingga enrolled — setiap tahap terpantau. Draf kedaluwarsa dibersihkan terjadwal oleh scheduler.'],
            ],
            'features' => [
                'Pendaftaran' => ['Formulir publik dinamis', 'Upload dokumen', 'Deteksi NISN ganda', 'Nomor registrasi unik'],
                'Seleksi' => ['Verifikasi & wawancara', 'Skoring berbobot', 'Kuota per jalur terkunci', 'Waiting list otomatis'],
                'Konversi' => ['Satu klik jadi siswa', 'Invoice daftar-ulang otomatis', 'Notifikasi email', 'Laporan funnel'],
            ],
            'usecases' => [
                ['t' => 'PPDB serentak ribuan pendaftar', 'd' => 'Seleksi transaksional mencegah over-kuota saat banyak panitia bekerja paralel.'],
                ['t' => 'Sekolah dengan banyak jalur', 'd' => 'Tiap jalur punya kuota, bobot, dan peringkat sendiri tanpa spreadsheet terpisah.'],
            ],
            'faq' => [
                ['Apakah mendukung jalur zonasi dan prestasi?', 'Ya. Jalur dikonfigurasi per periode beserta kuota dan bobotnya; jarak dihitung otomatis dari koordinat.'],
                ['Bagaimana jika kuota penuh?', 'Pendaftar berperingkat di bawah kuota otomatis masuk waiting list dan naik saat ada kursi kosong.'],
                ['Apakah biaya pendaftaran otomatis ditagih?', 'Ya. Jika periode menetapkan biaya formulir, siswa yang di-enroll otomatis mendapat invoice daftar-ulang.'],
                ['Bisakah pendaftar mendaftar ganda?', 'Sistem menolak NISN yang sudah terdaftar pada periode berjalan.'],
            ],
            'related' => [
                ['t' => 'Sistem Informasi Sekolah', 'u' => '/sistem-informasi-sekolah'],
                ['t' => 'Aplikasi Pembayaran SPP', 'u' => '/aplikasi-pembayaran-spp'],
                ['t' => 'Tentang SIKAD PRO', 'u' => '/tentang-sikad-pro'],
            ],
            'meta' => [
                'title' => 'Aplikasi PPDB Online — Seleksi, Kuota & Konversi Otomatis',
                'description' => 'PPDB online SIKAD PRO: formulir dinamis, verifikasi, seleksi terkunci kuota, waiting list, pengumuman, dan konversi otomatis jadi siswa + tagihan.',
            ],
        ]);
    }

    public function aplikasiPembayaranSpp(): View
    {
        return $this->render([
            'short' => 'Aplikasi Pembayaran SPP',
            'kicker' => 'Finance',
            'title' => 'SPP & Keuangan Sekolah: Tagihan Sampai Jurnal Otomatis',
            'lead' => 'Hasilkan ratusan invoice dalam satu klik, terima pembayaran tunai, transfer, VA, QRIS, maupun e-wallet lewat provider pilihan sendiri (BYOK), kelola cicilan-denda-refund, dan biarkan setiap pembayaran memposting jurnal akuntansi otomatis.',
            'answer' => 'Aplikasi pembayaran SPP mengelola tagihan rutin sekolah: struktur biaya per kelas, invoice bulanan massal, pencatatan pembayaran multi-metode, pengingat otomatis, dan rekonsiliasi. Di SIKAD PRO setiap pembayaran yang tercatat memposting jurnal double-entry (debit kas/bank, kredit pendapatan) secara atomik, dan periode yang ditutup menolak jurnal baru.',
            'sections' => [
                ['h' => 'Uang tidak hilang di tengah jalan', 'p' => 'Pembayaran, cicilan, dan refund berjalan dalam transaksi database terkunci. Callback gateway terverifikasi tanda tangannya, anti-replay via sidik webhook, dan idempoten sehingga retry tidak mencatat ganda.'],
                ['h' => 'Provider milik sendiri', 'p' => 'Kredensial gateway, AI, SMS, dan video conference dimasukkan admin per sekolah dan dienkripsi. Tidak ada vendor yang dipaksakan aplikasi — bawa kunci sendiri.'],
                ['h' => 'Tutup buku tanpa drama', 'p' => 'Periode akuntansi dapat ditutup per bulan; pembuatan dan posting jurnal ke bulan tertutup ditolak dengan kode jelas. Pembukaan kembali membutuhkan izin khusus dan tercatat.'],
            ],
            'features' => [
                'Penagihan' => ['Invoice massal per periode', 'Cicilan & denda otomatis', 'Diskon & beasiswa', 'Pengingat terjadwal'],
                'Pembayaran' => ['Tunai, transfer, VA, QRIS', 'Webhook terverifikasi', 'Anti double-catat', 'Kuitansi & rekonsiliasi bank'],
                'Akuntansi' => ['Double-entry otomatis', 'Tutup/buka periode', 'Neraca & laba rugi', 'Payroll & pajak'],
            ],
            'usecases' => [
                ['t' => 'Awal bulan 1000+ tagihan', 'd' => 'Generate massal transaksional — tidak ada invoice ganda walau tombol ditekan dua kali.'],
                ['t' => 'Orang tua bayar via QRIS', 'd' => 'Callback gateway memverifikasi, mencatat, dan menjurnal dalam satu alur atomik.'],
            ],
            'faq' => [
                ['Gateway apa saja yang didukung?', 'Arsitektur BYOK: admin memasukkan endpoint dan kredensial provider sendiri (transfer bank, VA, QRIS, e-wallet) tanpa mapping vendor yang dipaksakan.'],
                ['Bagaimana mencegah pembayaran ganda?', 'Idempotency key, sidik webhook anti-replay, dan kunci baris database pada invoice.'],
                ['Apakah mendukung cicilan dan denda?', 'Ya. Jadwal cicilan, status overdue otomatis, denda harian, dan refund dengan jurnal koreksi atomik.'],
                ['Bisakah menutup periode akuntansi?', 'Ya. Periode tertutup menolak jurnal baru; reopen butuh izin accounting.reopen dan tercatat di audit.'],
            ],
            'related' => [
                ['t' => 'Sistem Informasi Sekolah', 'u' => '/sistem-informasi-sekolah'],
                ['t' => 'Aplikasi PPDB Online', 'u' => '/aplikasi-ppdb-online'],
                ['t' => 'Tentang SIKAD PRO', 'u' => '/tentang-sikad-pro'],
            ],
            'meta' => [
                'title' => 'Aplikasi Pembayaran SPP & Keuangan Sekolah — SIKAD PRO',
                'description' => 'Kelola SPP end-to-end: invoice massal, VA/QRIS/e-wallet BYOK, cicilan, refund, dan jurnal akuntansi otomatis dengan tutup periode.',
            ],
        ]);
    }

    public function aplikasiRaporDigital(): View
    {
        return $this->render([
            'short' => 'Aplikasi Rapor Digital',
            'kicker' => 'Assessment',
            'title' => 'Rapor Digital: Dari Nilai Harian Sampai Transkrip Terverifikasi',
            'lead' => 'Input nilai massal dengan validasi batas dan resolusi grade otomatis, kunci rapor yang sudah final, generate raport PDF dan transkrip bertanda QR, publikasikan massal, dan biarkan orang tua memverifikasi keaslian lewat halaman publik.',
            'answer' => 'Aplikasi rapor digital mengubah nilai harian menjadi dokumen hasil belajar resmi: guru menginput nilai per mata pelajaran, sistem menghitung rata-rata dan grade, approver mengunci, lalu raport PDF diterbitkan dengan kode QR verifikasi. Di SIKAD PRO, nilai pada rapor terkunci tidak dapat diubah kecuali lewat workflow pembukaan kembali yang tercatat.',
            'sections' => [
                ['h' => 'Nilai tidak bisa disulap', 'p' => 'Grade selalu dihitung server dari persentase dan sistem skala aktif — input grade manual dari API diabaikan. Rapor berstatus terkunci menolak perubahan nilai; pembukaan kembali butuh alasan dan tercatat.'],
                ['h' => 'Ujian sampai ke rapor otomatis', 'p' => 'Hasil CBT yang di-submit dan dinilai otomatis mengalir ke ledger nilai mata pelajaran terkait, lalu dirata-rata ke raport semester beserta peringkat kelas.'],
                ['h' => 'Verifikasi anti-palsu', 'p' => 'Setiap raport/transkrip membawa token QR unik. Halaman verifikasi publik menampilkan data pemilik tanpa perlu login — institusi tujuan bisa memastikan keaslian dalam hitungan detik.'],
            ],
            'features' => [
                'Penilaian' => ['Input massal tervalidasi', 'Skala & bobot fleksibel', 'Remedial & analisis butir', 'CBT auto-grading'],
                'Publikasi' => ['Approval berjenjang', 'Lock & reopen tercatat', 'Raport PDF + QR', 'Transkrip & mass-publish'],
                'Akses' => ['Portal orang tua', 'Halaman verifikasi publik', 'Riwayat perubahan', 'Perbandingan historis'],
            ],
            'usecases' => [
                ['t' => 'Akhir semester 30 rombel', 'd' => 'Generate massal melewati rapor yang sudah terkunci; publikasi satu klik per angkatan.'],
                ['t' => 'Legalisir untuk universitas', 'd' => 'Transkrip QR membuat verifikasi ijazah/rapor mandiri tanpa surat menyurat.'],
            ],
            'faq' => [
                ['Bagaimana jika ada nilai salah setelah rapor dikunci?', 'Ajukan reopen lewat workflow; perubahan tercatat lengkap dengan alasan dan pelaku.'],
                ['Apakah mendukung kurikulum Merdeka dan K13?', 'Ya. Skala grade, deskripsi capaian, dan format raport mengikuti konfigurasi kurikulum sekolah.'],
                ['Bisakah orang tua melihat rapor online?', 'Ya, lewat portal orang tua setelah rapor dipublikasikan; versi cetak PDF tetap tersedia.'],
                ['Bagaimana cara memverifikasi keaslian rapor?', 'Pindai QR pada dokumen atau buka halaman verifikasi publik dan masukkan token.'],
            ],
            'related' => [
                ['t' => 'Sistem Informasi Sekolah', 'u' => '/sistem-informasi-sekolah'],
                ['t' => 'Aplikasi PPDB Online', 'u' => '/aplikasi-ppdb-online'],
                ['t' => 'Tentang SIKAD PRO', 'u' => '/tentang-sikad-pro'],
            ],
            'meta' => [
                'title' => 'Aplikasi Rapor Digital & Penilaian — SIKAD PRO',
                'description' => 'Rapor digital SIKAD PRO: input nilai tervalidasi, grade otomatis, lock approval, raport PDF-QR terverifikasi, dan transkrip.',
            ],
        ]);
    }

    public function schoolManagementSystem(): View
    {
        return $this->render([
            'short' => 'School Management System',
            'kicker' => 'Education Management Platform',
            'title' => 'School Management System for Multi-Tenant School Operations',
            'lead' => 'SIKAD PRO is a multi-tenant school ERP: academics, admissions, finance, HR, operations, communication, analytics, and AI in one Laravel platform with a versioned REST API, PWA support, and white-label theming.',
            'answer' => 'A school management system centralizes student data, academics, finance, and communication. SIKAD PRO implements it as a multi-tenant School ERP: every record carries a school_id filtered automatically, authorization is enforced by policies, financial postings run in locked database transactions, and each school gets its own branding, subdomain, and portals.',
            'sections' => [
                ['h' => 'Tenant isolation by design', 'p' => 'Global query scopes, explicit school checks in services, and regression tests for cross-tenant access. A user from school A receives 404 — never data — for school B resources.'],
                ['h' => 'Money with an audit trail', 'p' => 'Invoices, installments, refunds, and payroll post balanced double-entry journals atomically. Accounting periods can be closed; reopening requires a dedicated permission and is logged.'],
                ['h' => 'API-first and AI-ready', 'p' => 'Versioned REST API with Sanctum tokens, per-school AI provider credentials (encrypted, bring-your-own-key), usage and cost tracking, and graceful degradation on provider failure.'],
            ],
            'features' => [
                'Academic' => ['Academic years & timetables', 'QR attendance with locks', 'Question bank & CBT', 'Grades & verified report cards'],
                'Finance' => ['Bulk invoicing', 'BYOK payment gateways', 'Refunds & reconciliation', 'Payroll & budgeting'],
                'Platform' => ['Multi-tenant SaaS', 'White-label themes', 'PWA + offline sync', 'REST API v1'],
            ],
            'usecases' => [
                ['t' => 'School groups', 'd' => 'Run many branches from one installation with consolidated foundation dashboards.'],
                ['t' => 'Digital transformation', 'd' => 'Replace scattered spreadsheets with one audited source of truth.'],
            ],
            'faq' => [
                ['What is SIKAD PRO?', 'SIKAD PRO is a multi-tenant Education Management Platform (School ERP) covering academics, admissions, finance, HR, operations, communication, analytics, and AI.'],
                ['Is data isolated per school?', 'Yes. Automatic school_id scoping plus policy enforcement, verified by tenant-isolation regression tests.'],
                ['Does it have an API?', 'Yes. Versioned REST API with token auth, used by the PWA/mobile clients.'],
                ['Can we use our own domain and branding?', 'Yes. Custom domains, logos, colors, and themes per school — white-label ready.'],
            ],
            'related' => [
                ['t' => 'Sistem Informasi Sekolah', 'u' => '/sistem-informasi-sekolah'],
                ['t' => 'About SIKAD PRO', 'u' => '/tentang-sikad-pro'],
            ],
            'lang' => 'en',
            'meta' => [
                'title' => 'School Management System — Multi-Tenant School ERP | SIKAD PRO',
                'description' => 'SIKAD PRO school management system: multi-tenant academics, admissions, finance, LMS, PWA, API, and AI in one school ERP.',
            ],
        ]);
    }

    public function tentangSikadPro(): View
    {
        return $this->render([
            'short' => 'Tentang SIKAD PRO',
            'kicker' => 'Product Facts',
            'title' => 'Tentang SIKAD PRO: Fakta Produk, Definisi & FAQ',
            'lead' => 'SIKAD PRO — Education Management Platform, dikenal juga sebagai School ERP, School Management System, dan Education ERP. Untuk sekolah, yayasan, guru, staf, siswa, dan orang tua. Multi-tenant, white-label, API-ready, AI-ready.',
            'answer' => 'SIKAD PRO adalah platform manajemen pendidikan multi-tenant yang mencakup akademik, kesiswaan, pembelajaran (LMS), PPDB, keuangan, SDM, operasional, komunikasi, analitik, otomasi, dan AI. Satu instalasi melayani banyak sekolah; tiap sekolah terisolasi datanya, punya branding dan portal sendiri, dan dikelola lewat panel admin plus API berversi.',
            'sections' => [
                ['h' => 'Apa itu School ERP?', 'p' => 'School ERP (Enterprise Resource Planning untuk sekolah) adalah sistem terpadu yang mengelola sumber daya sekolah — siswa, guru, kelas, keuangan, inventaris — dalam satu database. Berbeda dengan aplikasi tunggal (misalnya hanya absensi), ERP menghubungkan modul sehingga data mengalir otomatis antar fungsi.'],
                ['h' => 'Apa itu Student 360?', 'p' => 'Student 360 adalah tampilan menyeluruh satu siswa: identitas, akademik, absensi, nilai, disiplin, konseling, kesehatan, keuangan, dan aktivitas — ditarik dari modul sumbernya masing-masing, bukan diduplikasi.'],
                ['h' => 'Apa itu School Intelligence?', 'p' => 'Lapisan analitik yang mengubah data operasional menjadi peringatan dini: skor risiko siswa, prediksi dropout, anomali kehadiran dan keuangan — dengan ambang yang dapat dikonfigurasi dan alur tindak lanjut.'],
                ['h' => 'Teknologi apa yang dipakai?', 'p' => 'Backend Laravel (PHP) dengan MySQL dan Redis; frontend Blade + Alpine.js + Tailwind; PWA dengan service worker dan sinkronisasi offline; REST API berversi dengan token; AI berbasis provider pluggable dengan kredensial terenkripsi per sekolah.'],
            ],
            'features' => [
                'Area inti' => ['Akademik & rapor', 'Kesiswaan & lifecycle', 'LMS & CBT', 'PPDB online', 'Keuangan & SPP', 'Payroll & HR'],
                'Platform' => ['Multi-tenant SaaS', 'White-label & domain kustom', 'REST API v1', 'PWA + offline', 'AI BYOK', 'Audit log'],
            ],
            'usecases' => [],
            'faq' => [
                ['Apa itu SIKAD PRO?', 'Education Management Platform (School ERP) multi-tenant untuk operasional sekolah end-to-end.'],
                ['Apa itu sistem informasi sekolah?', 'Perangkat lunak yang mengelola data dan proses sekolah dalam satu database terpusat — SIKAD PRO adalah salah satu implementasinya dalam bentuk School ERP.'],
                ['Apakah mendukung PPDB?', 'Ya: periode, kuota jalur, formulir dinamis, verifikasi, seleksi terkunci, waiting list, dan konversi otomatis menjadi siswa plus tagihan.'],
                ['Apakah mendukung pembayaran SPP?', 'Ya: invoice massal, VA/QRIS/e-wallet BYOK, cicilan, refund, dan jurnal akuntansi otomatis.'],
                ['Apakah memiliki LMS?', 'Ya: kursus, modul, lesson, kuis, progres, sertifikat terverifikasi — via web dan REST API.'],
                ['Apakah mendukung multi sekolah?', 'Ya: multi-tenant dengan dashboard yayasan lintas cabang.'],
                ['Apakah white-label?', 'Ya: logo, warna, font, tema, dan domain kustom per sekolah.'],
                ['Apakah memiliki API?', 'Ya: REST API v1 dengan autentikasi token.'],
                ['Apakah menggunakan AI?', 'Ya: provider pluggable per sekolah (BYOK), pelacakan usage/biaya, dan degradasi graceful saat provider down.'],
                ['Apakah bisa memakai domain sendiri?', 'Ya: subdomain per sekolah plus domain kustom.'],
            ],
            'related' => [
                ['t' => 'Sistem Informasi Sekolah', 'u' => '/sistem-informasi-sekolah'],
                ['t' => 'Aplikasi PPDB Online', 'u' => '/aplikasi-ppdb-online'],
                ['t' => 'Aplikasi Pembayaran SPP', 'u' => '/aplikasi-pembayaran-spp'],
                ['t' => 'Aplikasi Rapor Digital', 'u' => '/aplikasi-rapor-digital'],
            ],
            'meta' => [
                'title' => 'Tentang SIKAD PRO — Fakta Produk School ERP & FAQ',
                'description' => 'Fakta SIKAD PRO: Education Management Platform multi-tenant — fitur, definisi School ERP, LMS, PPDB, SPP, API, AI, white-label, dan FAQ.',
            ],
        ]);
    }
}

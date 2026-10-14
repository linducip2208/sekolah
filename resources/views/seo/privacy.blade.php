@extends('seo.layout')

@section('seo_content')
<section class="max-w-4xl mx-auto px-4 sm:px-6 py-16 lg:py-20">
    <nav class="elite-kicker mb-6" style="color: var(--c-muted);" aria-label="Breadcrumb">
        <a href="/" class="hover:ink-primary">Beranda</a>
        <span class="mx-2 ink-accent">/</span>
        <span>Privasi</span>
    </nav>

    <header class="mb-10 pb-8 border-b border-rule">
        <div class="elite-kicker mb-3">Legal</div>
        <h1 class="elite-h1 text-4xl sm:text-5xl ink-primary mb-4">Kebijakan Privasi Aplikasi SIKAD PRO</h1>
        <div class="elite-rule mb-4"></div>
        <p class="font-serif text-base text-gray-600">Berlaku sejak 11 Oktober 2026 · Diperbarui: 11 Oktober 2026 · Bahasa Indonesia (English summary below)</p>
    </header>

    <div class="space-y-8 font-serif text-lg leading-relaxed text-gray-800">
        <section>
            <h2 class="elite-h2 text-2xl ink-primary mb-3">1. Pengendali data</h2>
            <p>Aplikasi mobile <strong>SIKAD PRO (eSchool)</strong> adalah aplikasi pendamping sistem informasi sekolah. Pengendali data untuk data operasional sekolah adalah <strong>sekolah/lembaga pendidikan Anda</strong> (yang mengoperasikan panel admin). Pengembang platform menyediakan infrastruktur dan tidak menjual data Anda.</p>
        </section>

        <section>
            <h2 class="elite-h2 text-2xl ink-primary mb-3">2. Data yang kami kumpulkan</h2>
            <ul class="list-disc pl-6 space-y-2">
                <li><strong>Data akun:</strong> nama, email, kata sandi ter-hash, peran, foto profil, token FCM/notifikasi per perangkat.</li>
                <li><strong>Data akademik & kesiswaan:</strong> identitas siswa, rombel, kehadiran (termasuk pindaian QR), nilai, tugas, jadwal, rapor.</li>
                <li><strong>Data keuangan:</strong> tagihan, status pembayaran, dan referensi transaksi (detail kartu/bank diproses oleh payment gateway, tidak disimpan di server kami).</li>
                <li><strong>File yang Anda unggah:</strong> foto, tugas, dokumen PPDB, bukti pembayaran — disimpan di penyimpanan scope-sekolah.</li>
                <li><strong>Kamera (opsional):</strong> hanya saat Anda memakai pindai QR kehadiran atau mengambil foto untuk diunggah. Tidak ada rekaman latar.</li>
                <li><strong>Lokasi (opsional):</strong> hanya untuk fitur pelacakan bus sekolah dan presensi berbasis lokasi, dan hanya saat fitur dipakai.</li>
                <li><strong>Notifikasi:</strong> token push perangkat untuk pengumuman, pengingat tagihan, dan informasi akademik.</li>
                <li><strong>Data teknis:</strong> log diagnostik (mis. ID permintaan), versi aplikasi, dan model perangkat untuk keamanan dan perbaikan bug.</li>
            </ul>
        </section>

        <section>
            <h2 class="elite-h2 text-2xl ink-primary mb-3">3. Tujuan penggunaan</h2>
            <p>Data dipakai semata untuk: menjalankan layanan pendidikan (akademik, absensi, pembayaran, komunikasi sekolah), keamanan akun (termasuk 2FA/TOTP), pemberitahuan operasional, dan memenuhi kewajiban hukum sekolah. Tidak untuk iklan pihak ketiga.</p>
        </section>

        <section>
            <h2 class="elite-h2 text-2xl ink-primary mb-3">4. Data anak</h2>
            <p>Layanan ini digunakan oleh sekolah untuk siswa, termasuk anak di bawah umur. Akun siswa dibuat dan dikelola oleh <strong>sekolah/orang tua</strong> melalui proses pendaftaran resmi (PPDB/penerimaan). Dengan mendaftarkan anak, sekolah/orang tua memberikan persetujuan atas pemrosesan data pendidikan anak. Orang tua dapat meminta akses, koreksi, atau penghapusan melalui sekolah (lihat bagian 8).</p>
        </section>

        <section>
            <h2 class="elite-h2 text-2xl ink-primary mb-3">5. Berbagi data</h2>
            <ul class="list-disc pl-6 space-y-2">
                <li><strong>Tidak dijual.</strong> Data tidak dijual atau disewakan.</li>
                <li><strong>Pemroses yang diperlukan:</strong> payment gateway pilihan sekolah (BYOK), Firebase Cloud Messaging (notifikasi push), dan penyedia infrastruktur (hosting/database). Masing-masing tunduk pada kebijakan privasinya sendiri.</li>
                <li><strong>Antar peran sekolah:</strong> guru, wali kelas, dan orang tua hanya melihat data sesuai hak akses perannya; akses lintas sekolah ditolak sistem (403/404).</li>
                <li><strong>Hukum:</strong> diungkap bila diwajibkan peraturan perundang-undangan yang berlaku.</li>
            </ul>
        </section>

        <section>
            <h2 class="elite-h2 text-2xl ink-primary mb-3">6. Penyimpanan & keamanan</h2>
            <p>Data tersimpan di server dengan kontrol akses berbasis peran, isolasi data per sekolah (school_id), kata sandi ter-hash (bcrypt), rahasia 2FA dan kredensial provider terenkripsi, rate limiting, dan audit log perubahan sensitif. Retensi mengikuti kebutuhan operasional sekolah dan ketentuan arsip yang berlaku.</p>
        </section>

        <section>
            <h2 class="elite-h2 text-2xl ink-primary mb-3">7. Izin perangkat</h2>
            <ul class="list-disc pl-6 space-y-2">
                <li><strong>Kamera</strong> — pindai QR & ambil foto unggahan (dapat ditolak; fitur terkait nonaktif).</li>
                <li><strong>Lokasi</strong> — pelacakan bus & presensi lokasi (dapat ditolak; gunakan input manual/QR).</li>
                <li><strong>Notifikasi</strong> — pengumuman & pengingat (dapat dimatikan di pengaturan sistem).</li>
                <li>Aplikasi memakai system picker untuk foto/dokumen sehingga tidak meminta akses media luas.</li>
            </ul>
        </section>

        <section>
            <h2 class="elite-h2 text-2xl ink-primary mb-3">8. Hak Anda & penghapusan akun</h2>
            <p>Anda berhak meminta akses, koreksi, pembatasan, portabilitas, dan penghapusan data pribadi. Untuk <strong>penghapusan akun aplikasi</strong>: (1) hubungi admin/TU sekolah Anda untuk menonaktifkan akun dan menghapus data terkait sesuai kebijakan sekolah; atau (2) hubungi kami di bawah — permintaan diverifikasi lewat sekolah untuk melindungi data anak. Logout tidak menghapus akun; gunakan menu profil/perangkat atau hubungi admin.</p>
        </section>

        <section>
            <h2 class="elite-h2 text-2xl ink-primary mb-3">9. Perubahan kebijakan</h2>
            <p>Perubahan material diumumkan lewat aplikasi/situs sebelum berlaku. Penggunaan berkelanjutan setelah tanggal berlaku dianggap menyetujui versi terbaru.</p>
        </section>

        <section>
            <h2 class="elite-h2 text-2xl ink-primary mb-3">10. Kontak</h2>
            <p>Pertanyaan privasi & penghapusan data: WhatsApp <strong>081296052010</strong> atau admin/TU sekolah Anda. Alamat surat via halaman kontak.</p>
        </section>

        <section class="bg-white border border-rule p-6">
            <h2 class="elite-h2 text-2xl ink-primary mb-3">English summary</h2>
            <p class="text-base">SIKAD PRO (eSchool) is a school companion app. Your school controls operational data; we do not sell data. We collect account, academic, payment-reference, uploaded-file, optional camera/location, push-token, and diagnostic data solely to run educational services. Parent/school consent covers student accounts. Contact your school admin or WhatsApp +62 812-9605-2010 for access, correction, or deletion requests.</p>
        </section>
    </div>
</section>
@endsection

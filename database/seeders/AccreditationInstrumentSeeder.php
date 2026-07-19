<?php

namespace Database\Seeders;

use App\Models\AccreditationInstrument;
use App\Models\AccreditationStandard;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccreditationInstrumentSeeder extends Seeder
{
    public function run(): void
    {
        $standards = [
            [
                'code' => '1',
                'name' => 'Mutu Lulusan',
                'description' => 'Standar mutu lulusan mencakup kompetensi siswa dalam aspek sikap, pengetahuan, dan keterampilan sesuai dengan Standar Kompetensi Lulusan (SKL).',
                'max_score' => 100,
                'weight_percent' => 35.00,
                'instruments' => [
                    ['number' => '1.1', 'description' => 'Siswa menunjukkan perilaku disiplin dalam berbagai situasi', 'max_score' => 4, 'evidence_hint' => 'Buku tata tertib, catatan pelanggaran, laporan kedisiplinan'],
                    ['number' => '1.2', 'description' => 'Siswa menunjukkan perilaku religius dalam aktivitas di sekolah', 'max_score' => 4, 'evidence_hint' => 'Jadwal kegiatan keagamaan, dokumentasi ibadah, laporan'],
                    ['number' => '1.3', 'description' => 'Siswa menunjukkan perilaku tanggung jawab dalam pembelajaran', 'max_score' => 4, 'evidence_hint' => 'Catatan penyelesaian tugas, portofolio siswa, lembar pantauan'],
                    ['number' => '1.4', 'description' => 'Siswa mencapai kompetensi literasi sesuai standar yang ditetapkan', 'max_score' => 4, 'evidence_hint' => 'Hasil asesmen literasi, program literasi sekolah, data perpustakaan'],
                    ['number' => '1.5', 'description' => 'Siswa mencapai kompetensi numerasi sesuai standar yang ditetapkan', 'max_score' => 4, 'evidence_hint' => 'Hasil asesmen numerasi, nilai matematika, program numerasi'],
                    ['number' => '1.6', 'description' => 'Siswa berpartisipasi dalam kegiatan pengembangan minat dan bakat', 'max_score' => 4, 'evidence_hint' => 'Daftar ekstrakurikuler, daftar peserta, piagam prestasi'],
                    ['number' => '1.7', 'description' => 'Lulusan diterima di jenjang pendidikan selanjutnya sesuai kompetensi', 'max_score' => 4, 'evidence_hint' => 'Data kelulusan, data penelusuran alumni, rekapitulasi penerimaan'],
                    ['number' => '1.8', 'description' => 'Siswa memiliki keterampilan abad 21 (4C: critical thinking, creativity, collaboration, communication)', 'max_score' => 4, 'evidence_hint' => 'Hasil proyek, portofolio kolaborasi, rubrik penilaian 4C'],
                ],
            ],
            [
                'code' => '2',
                'name' => 'Proses Pembelajaran',
                'description' => 'Standar proses pembelajaran mencakup perencanaan, pelaksanaan, dan penilaian pembelajaran yang efektif dan efisien.',
                'max_score' => 100,
                'weight_percent' => 30.00,
                'instruments' => [
                    ['number' => '2.1', 'description' => 'Guru menyusun RPP/RPP Modul secara lengkap dan sistematis', 'max_score' => 4, 'evidence_hint' => 'Dokumen RPP, bukti pengesahan oleh kepala sekolah'],
                    ['number' => '2.2', 'description' => 'Guru melaksanakan pembelajaran yang aktif, inovatif, kreatif, efektif, dan menyenangkan (PAIKEM)', 'max_score' => 4, 'evidence_hint' => 'Hasil supervisi kelas, video pembelajaran, lembar observasi'],
                    ['number' => '2.3', 'description' => 'Guru menggunakan media dan sumber belajar yang bervariasi', 'max_score' => 4, 'evidence_hint' => 'Daftar media pembelajaran, daftar sumber belajar, foto dokumentasi'],
                    ['number' => '2.4', 'description' => 'Guru melaksanakan penilaian otentik (sikap, pengetahuan, keterampilan)', 'max_score' => 4, 'evidence_hint' => 'Instrumen penilaian, rubrik, hasil penilaian, analisis hasil'],
                    ['number' => '2.5', 'description' => 'Guru melaksanakan program remedial dan pengayaan', 'max_score' => 4, 'evidence_hint' => 'Program remedial/pengayaan, dokumen pelaksanaan, hasil'],
                    ['number' => '2.6', 'description' => 'Sekolah menerapkan pembelajaran berbasis TIK', 'max_score' => 4, 'evidence_hint' => 'Jadwal lab komputer, daftar aplikasi pembelajaran, laporan penggunaan'],
                    ['number' => '2.7', 'description' => 'Supervisi pembelajaran dilakukan secara terencana dan berkelanjutan', 'max_score' => 4, 'evidence_hint' => 'Program supervisi, instrumen supervisi, laporan hasil supervisi'],
                ],
            ],
            [
                'code' => '3',
                'name' => 'Mutu Guru',
                'description' => 'Standar mutu guru mencakup kualifikasi, kompetensi, dan pengembangan profesional berkelanjutan tenaga pendidik.',
                'max_score' => 100,
                'weight_percent' => 20.00,
                'instruments' => [
                    ['number' => '3.1', 'description' => 'Guru memiliki kualifikasi akademik minimal S1/D4', 'max_score' => 4, 'evidence_hint' => 'Ijazah guru, SK mengajar, data kualifikasi akademik'],
                    ['number' => '3.2', 'description' => 'Guru memiliki sertifikat pendidik', 'max_score' => 4, 'evidence_hint' => 'Sertifikat pendidik, NRG, data guru bersertifikasi'],
                    ['number' => '3.3', 'description' => 'Guru mengajar sesuai dengan latar belakang keilmuan (linieritas)', 'max_score' => 4, 'evidence_hint' => 'Data kesesuaian mengajar, jadwal mengajar, latar belakang pendidikan'],
                    ['number' => '3.4', 'description' => 'Guru mengikuti kegiatan pengembangan keprofesian berkelanjutan (PKB)', 'max_score' => 4, 'evidence_hint' => 'Sertifikat pelatihan, daftar hadir diklat, laporan PKB'],
                    ['number' => '3.5', 'description' => 'Guru aktif dalam kegiatan Musyawarah Guru Mata Pelajaran (MGMP) atau komunitas belajar', 'max_score' => 4, 'evidence_hint' => 'Undangan MGMP, notulen, daftar hadir, produk hasil MGMP'],
                    ['number' => '3.6', 'description' => 'Guru menghasilkan karya inovatif (PTK, artikel, buku, media pembelajaran, karya seni)', 'max_score' => 4, 'evidence_hint' => 'Daftar karya inovatif, dokumen karya, ISBN, HAKI'],
                    ['number' => '3.7', 'description' => 'Guru melaksanakan penelitian tindakan kelas (PTK) dan publikasi ilmiah', 'max_score' => 4, 'evidence_hint' => 'Laporan PTK, artikel jurnal, bukti publikasi'],
                ],
            ],
            [
                'code' => '4',
                'name' => 'Manajemen Sekolah',
                'description' => 'Standar manajemen sekolah mencakup pengelolaan sekolah yang efektif, efisien, dan akuntabel.',
                'max_score' => 100,
                'weight_percent' => 15.00,
                'instruments' => [
                    ['number' => '4.1', 'description' => 'Sekolah memiliki visi, misi, dan tujuan yang jelas dan disosialisasikan', 'max_score' => 4, 'evidence_hint' => 'Dokumen visi misi, notulen sosialisasi, banner/papan visi misi'],
                    ['number' => '4.2', 'description' => 'Sekolah memiliki RKAS yang disusun secara partisipatif', 'max_score' => 4, 'evidence_hint' => 'Dokumen RKAS, notulen penyusunan, berita acara'],
                    ['number' => '4.3', 'description' => 'Sekolah mengelola sarana dan prasarana pembelajaran secara optimal', 'max_score' => 4, 'evidence_hint' => 'Daftar inventaris, laporan pemeliharaan, jadwal penggunaan'],
                    ['number' => '4.4', 'description' => 'Sekolah melibatkan masyarakat/komite dalam pengembangan sekolah', 'max_score' => 4, 'evidence_hint' => 'SK Komite, notulen rapat komite, program kerja komite'],
                    ['number' => '4.5', 'description' => 'Sekolah menerapkan sistem penjaminan mutu internal (SPMI)', 'max_score' => 4, 'evidence_hint' => 'Dokumen SPMI, tim penjaminan mutu, siklus SPMI, peta mutu'],
                    ['number' => '4.6', 'description' => 'Sekolah mengelola keuangan transparan dan akuntabel', 'max_score' => 4, 'evidence_hint' => 'Laporan keuangan, BOS RKAS, papan pengumuman keuangan, audit'],
                    ['number' => '4.7', 'description' => 'Kepala sekolah memiliki kompetensi manajerial yang baik', 'max_score' => 4, 'evidence_hint' => 'SK kepala sekolah, sertifikat pelatihan manajerial, penilaian kinerja'],
                ],
            ],
        ];

        foreach ($standards as $stdData) {
            $instruments = $stdData['instruments'];
            unset($stdData['instruments']);

            $standard = AccreditationStandard::updateOrCreate(
                ['code' => $stdData['code']],
                $stdData
            );

            foreach ($instruments as $instData) {
                AccreditationInstrument::updateOrCreate(
                    [
                        'accreditation_standard_id' => $standard->id,
                        'number' => $instData['number'],
                    ],
                    $instData
                );
            }
        }
    }
}

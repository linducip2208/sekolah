<?php

namespace Database\Seeders;

use App\Models\Academic\PkgCompetency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PkgCompetencySeeder extends Seeder
{
    public function run(): void
    {
        $competencies = [
            // PEDAGOGIK (1-5)
            ['code' => 'PKG-01', 'name' => 'Menguasai Karakteristik Peserta Didik', 'competency_type' => 'pedagogik', 'weight' => 1.0, 'description' => 'Guru memahami karakteristik peserta didik dari aspek fisik, moral, sosial, kultural, emosional, dan intelektual.'],
            ['code' => 'PKG-02', 'name' => 'Menguasai Teori Belajar dan Prinsip Pembelajaran', 'competency_type' => 'pedagogik', 'weight' => 1.0, 'description' => 'Guru menguasai teori belajar dan prinsip-prinsip pembelajaran yang mendidik.'],
            ['code' => 'PKG-03', 'name' => 'Pengembangan Kurikulum', 'competency_type' => 'pedagogik', 'weight' => 1.0, 'description' => 'Guru mampu mengembangkan kurikulum yang terkait dengan mata pelajaran yang diampu.'],
            ['code' => 'PKG-04', 'name' => 'Kegiatan Pembelajaran yang Mendidik', 'competency_type' => 'pedagogik', 'weight' => 1.0, 'description' => 'Guru menyelenggarakan pembelajaran yang mendidik, interaktif, inspiratif, dan menyenangkan.'],
            ['code' => 'PKG-05', 'name' => 'Pengembangan Potensi Peserta Didik', 'competency_type' => 'pedagogik', 'weight' => 1.0, 'description' => 'Guru memfasilitasi pengembangan potensi peserta didik untuk mengaktualisasikan berbagai potensi yang dimiliki.'],

            // KEPRIBADIAN (6-9)
            ['code' => 'PKG-06', 'name' => 'Bertindak Sesuai Norma', 'competency_type' => 'kepribadian', 'weight' => 1.0, 'description' => 'Guru bertindak sesuai norma agama, hukum, sosial, dan kebudayaan nasional Indonesia.'],
            ['code' => 'PKG-07', 'name' => 'Pribadi Dewasa dan Teladan', 'competency_type' => 'kepribadian', 'weight' => 1.0, 'description' => 'Guru menampilkan diri sebagai pribadi yang dewasa, arif, dan berwibawa.'],
            ['code' => 'PKG-08', 'name' => 'Etos Kerja dan Tanggung Jawab', 'competency_type' => 'kepribadian', 'weight' => 1.0, 'description' => 'Guru menunjukkan etos kerja, tanggung jawab yang tinggi, dan rasa bangga menjadi guru.'],
            ['code' => 'PKG-09', 'name' => 'Menjunjung Kode Etik Profesi', 'competency_type' => 'kepribadian', 'weight' => 1.0, 'description' => 'Guru menjunjung tinggi kode etik profesi guru dan menerapkan prinsip-prinsip etika profesi.'],

            // SOSIAL (10-11)
            ['code' => 'PKG-10', 'name' => 'Komunikasi Efektif', 'competency_type' => 'sosial', 'weight' => 1.0, 'description' => 'Guru mampu berkomunikasi dan bergaul secara efektif dengan peserta didik, sesama pendidik, tenaga kependidikan, orang tua/wali, dan masyarakat.'],
            ['code' => 'PKG-11', 'name' => 'Kolaborasi dan Adaptasi Sosial', 'competency_type' => 'sosial', 'weight' => 1.0, 'description' => 'Guru mampu beradaptasi di tempat tugas dan menjalin kerjasama dengan seluruh pemangku kepentingan sekolah.'],

            // PROFESIONAL (12-14)
            ['code' => 'PKG-12', 'name' => 'Penguasaan Materi Pembelajaran', 'competency_type' => 'profesional', 'weight' => 1.0, 'description' => 'Guru menguasai materi, struktur, konsep, dan pola pikir keilmuan yang mendukung mata pelajaran yang diampu.'],
            ['code' => 'PKG-13', 'name' => 'Pengembangan Profesi Berkelanjutan', 'competency_type' => 'profesional', 'weight' => 1.0, 'description' => 'Guru mengembangkan keprofesian berkelanjutan melalui tindakan reflektif, penelitian, dan karya inovatif.'],
            ['code' => 'PKG-14', 'name' => 'Pemanfaatan TIK dalam Pembelajaran', 'competency_type' => 'profesional', 'weight' => 1.0, 'description' => 'Guru memanfaatkan teknologi informasi dan komunikasi untuk kepentingan pembelajaran dan pengembangan diri.'],
        ];

        foreach ($competencies as $comp) {
            PkgCompetency::firstOrCreate(
                ['code' => $comp['code']],
                $comp
            );
        }
    }
}

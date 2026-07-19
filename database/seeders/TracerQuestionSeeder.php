<?php

namespace Database\Seeders;

use App\Models\Alumni\TracerQuestion;
use Illuminate\Database\Seeder;

class TracerQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            ['question_text' => 'Apa status Anda saat ini?', 'question_type' => 'select', 'options' => json_encode(['Bekerja', 'Kuliah', 'Wirausaha', 'Menganggur', 'Lainnya']), 'sort_order' => 1],
            ['question_text' => 'Nama perusahaan / instansi / universitas tempat Anda bekerja/kuliah', 'question_type' => 'text', 'sort_order' => 2],
            ['question_text' => 'Jabatan / Program Studi Anda', 'question_type' => 'text', 'sort_order' => 3],
            ['question_text' => 'Berapa rata-rata penghasilan per bulan?', 'question_type' => 'select', 'options' => json_encode(['< 1 juta', '1-3 juta', '3-5 juta', '5-10 juta', '10-20 juta', '> 20 juta']), 'sort_order' => 4],
            ['question_text' => 'Apakah pekerjaan/kuliah Anda relevan dengan bidang/jurusan saat di sekolah?', 'question_type' => 'radio', 'options' => json_encode(['Ya', 'Tidak']), 'sort_order' => 5],
            ['question_text' => 'Bagaimana penilaian Anda terhadap kualitas pendidikan di sekolah?', 'question_type' => 'radio', 'options' => json_encode(['Sangat Baik', 'Baik', 'Cukup', 'Kurang']), 'sort_order' => 6],
            ['question_text' => 'Kompetensi apa yang paling berguna dari sekolah untuk karir Anda?', 'question_type' => 'text', 'sort_order' => 7],
            ['question_text' => 'Saran dan masukan untuk pengembangan sekolah', 'question_type' => 'textarea', 'sort_order' => 8],
        ];

        // We don't bind to school_id here; let admin customize per school.
        // These serve as defaults for copy purposes.
        foreach ($questions as $q) {
            TracerQuestion::create(array_merge($q, [
                'school_id'  => 1,
                'is_active'  => true,
            ]));
        }
    }
}

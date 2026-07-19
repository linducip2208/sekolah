<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adiwiyata_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adiwiyata_category_id')->constrained('adiwiyata_categories')->cascadeOnDelete();
            $table->string('code');
            $table->text('description');
            $table->text('evidence_hint')->nullable();
            $table->integer('max_score')->default(4);
            $table->string('evidence_type')->default('document');
            $table->timestamps();
        });

        $indicators = [
            // K1: Kebersihan
            ['adiwiyata_category_id' => 1, 'code' => 'A1', 'description' => 'Tersedia jadwal piket kebersihan kelas yang dilaksanakan rutin', 'evidence_hint' => 'Upload jadwal piket + foto pelaksanaan', 'max_score' => 4, 'evidence_type' => 'photo'],
            ['adiwiyata_category_id' => 1, 'code' => 'A2', 'description' => 'Tersedia tempat cuci tangan dengan sabun di setiap kelas', 'evidence_hint' => 'Foto sarana cuci tangan', 'max_score' => 4, 'evidence_type' => 'photo'],
            ['adiwiyata_category_id' => 1, 'code' => 'A3', 'description' => 'Toilet sekolah bersih, tidak bau, dan berfungsi baik', 'evidence_hint' => 'Foto toilet + jadwal pembersihan', 'max_score' => 4, 'evidence_type' => 'photo'],
            ['adiwiyata_category_id' => 1, 'code' => 'A4', 'description' => 'Lingkungan kelas bebas dari coretan dan sampah', 'evidence_hint' => 'Foto kondisi kelas', 'max_score' => 4, 'evidence_type' => 'photo'],
            ['adiwiyata_category_id' => 1, 'code' => 'A5', 'description' => 'Adanya program Jumat Bersih atau kerja bakti rutin', 'evidence_hint' => 'Dokumentasi kegiatan + daftar hadir', 'max_score' => 4, 'evidence_type' => 'document'],
            // K2: Pengelolaan Sampah
            ['adiwiyata_category_id' => 2, 'code' => 'B1', 'description' => 'Tersedia tempat sampah terpilah (organik, anorganik, B3) di seluruh area', 'evidence_hint' => 'Foto tempat sampah terpilah', 'max_score' => 4, 'evidence_type' => 'photo'],
            ['adiwiyata_category_id' => 2, 'code' => 'B2', 'description' => 'Adanya bank sampah atau program daur ulang sampah', 'evidence_hint' => 'Foto bank sampah + catatan transaksi', 'max_score' => 4, 'evidence_type' => 'photo'],
            ['adiwiyata_category_id' => 2, 'code' => 'B3', 'description' => 'Pengolahan sampah organik menjadi kompos', 'evidence_hint' => 'Foto proses + hasil kompos', 'max_score' => 4, 'evidence_type' => 'photo'],
            ['adiwiyata_category_id' => 2, 'code' => 'B4', 'description' => 'Kebijakan pengurangan sampah plastik di lingkungan sekolah', 'evidence_hint' => 'Upload SK kebijakan', 'max_score' => 4, 'evidence_type' => 'document'],
            ['adiwiyata_category_id' => 2, 'code' => 'B5', 'description' => 'Kerjasama dengan pihak ketiga untuk pengelolaan sampah', 'evidence_hint' => 'Upload MOU atau surat kerjasama', 'max_score' => 3, 'evidence_type' => 'document'],
            // K3: Konservasi Air
            ['adiwiyata_category_id' => 3, 'code' => 'C1', 'description' => 'Tersedia sumur resapan atau lubang biopori', 'evidence_hint' => 'Foto sumur resapan / biopori', 'max_score' => 4, 'evidence_type' => 'photo'],
            ['adiwiyata_category_id' => 3, 'code' => 'C2', 'description' => 'Kran air hemat dan tidak ada kebocoran', 'evidence_hint' => 'Foto kran hemat + laporan pengecekan', 'max_score' => 3, 'evidence_type' => 'photo'],
            ['adiwiyata_category_id' => 3, 'code' => 'C3', 'description' => 'Pemanfaatan air bekas wudhu untuk menyiram tanaman', 'evidence_hint' => 'Foto instalasi pemanfaatan', 'max_score' => 4, 'evidence_type' => 'photo'],
            ['adiwiyata_category_id' => 3, 'code' => 'C4', 'description' => 'Adanya kampanye hemat air di lingkungan sekolah', 'evidence_hint' => 'Foto poster/stiker kampanye', 'max_score' => 3, 'evidence_type' => 'photo'],
            ['adiwiyata_category_id' => 3, 'code' => 'C5', 'description' => 'Adanya penampungan air hujan (rainwater harvesting)', 'evidence_hint' => 'Foto instalasi penampungan', 'max_score' => 3, 'evidence_type' => 'photo'],
            // K4: Konservasi Energi
            ['adiwiyata_category_id' => 4, 'code' => 'D1', 'description' => 'Pemasangan stiker hemat energi di setiap ruangan', 'evidence_hint' => 'Foto stiker di ruangan', 'max_score' => 3, 'evidence_type' => 'photo'],
            ['adiwiyata_category_id' => 4, 'code' => 'D2', 'description' => 'Pencahayaan alami optimal di kelas (ventilasi dan jendela cukup)', 'evidence_hint' => 'Foto kondisi kelas', 'max_score' => 4, 'evidence_type' => 'photo'],
            ['adiwiyata_category_id' => 4, 'code' => 'D3', 'description' => 'Penggunaan lampu LED atau hemat energi di seluruh area', 'evidence_hint' => 'Foto lampu + data pemakaian', 'max_score' => 4, 'evidence_type' => 'photo'],
            ['adiwiyata_category_id' => 4, 'code' => 'D4', 'description' => 'Adanya rekapitulasi penggunaan listrik dan air bulanan', 'evidence_hint' => 'Upload rekap tagihan', 'max_score' => 3, 'evidence_type' => 'document'],
            ['adiwiyata_category_id' => 4, 'code' => 'D5', 'description' => 'Penggunaan energi terbarukan (panel surya, biogas, dll)', 'evidence_hint' => 'Foto instalasi energi terbarukan', 'max_score' => 3, 'evidence_type' => 'photo'],
            // K5: Keanekaragaman Hayati
            ['adiwiyata_category_id' => 5, 'code' => 'E1', 'description' => 'Tersedia taman sekolah atau greenhouse', 'evidence_hint' => 'Foto taman/greenhouse', 'max_score' => 4, 'evidence_type' => 'photo'],
            ['adiwiyata_category_id' => 5, 'code' => 'E2', 'description' => 'Penanaman minimal 20 jenis tanaman di lingkungan sekolah', 'evidence_hint' => 'Upload daftar tanaman + foto', 'max_score' => 4, 'evidence_type' => 'document'],
            ['adiwiyata_category_id' => 5, 'code' => 'E3', 'description' => 'Adanya papan nama tanaman (nama lokal dan Latin)', 'evidence_hint' => 'Foto papan nama tanaman', 'max_score' => 3, 'evidence_type' => 'photo'],
            ['adiwiyata_category_id' => 5, 'code' => 'E4', 'description' => 'Adanya program adopsi pohon oleh siswa', 'evidence_hint' => 'Dokumentasi program', 'max_score' => 3, 'evidence_type' => 'document'],
            ['adiwiyata_category_id' => 5, 'code' => 'E5', 'description' => 'Tersedia lahan untuk pembibitan tanaman', 'evidence_hint' => 'Foto lahan pembibitan', 'max_score' => 2, 'evidence_type' => 'photo'],
            // K6: Inovasi Lingkungan
            ['adiwiyata_category_id' => 6, 'code' => 'F1', 'description' => 'Adanya karya inovatif siswa terkait lingkungan (daur ulang, energi, dll)', 'evidence_hint' => 'Foto/dokumentasi karya', 'max_score' => 4, 'evidence_type' => 'photo'],
            ['adiwiyata_category_id' => 6, 'code' => 'F2', 'description' => 'Integrasi pendidikan lingkungan dalam mata pelajaran', 'evidence_hint' => 'Upload RPP yang mengintegrasikan LH', 'max_score' => 4, 'evidence_type' => 'document'],
            ['adiwiyata_category_id' => 6, 'code' => 'F3', 'description' => 'Adanya ekstrakurikuler atau komunitas peduli lingkungan', 'evidence_hint' => 'Foto kegiatan + SK pembentukan', 'max_score' => 3, 'evidence_type' => 'document'],
            ['adiwiyata_category_id' => 6, 'code' => 'F4', 'description' => 'Sekolah menjadi contoh bagi sekolah lain dalam pengelolaan lingkungan', 'evidence_hint' => 'Dokumentasi kunjungan', 'max_score' => 3, 'evidence_type' => 'document'],
            ['adiwiyata_category_id' => 6, 'code' => 'F5', 'description' => 'Adanya kemitraan dengan instansi lingkungan (DLH, LSM, dll)', 'evidence_hint' => 'Upload MOU atau surat kemitraan', 'max_score' => 2, 'evidence_type' => 'document'],
        ];

        foreach ($indicators as $ind) {
            DB::table('adiwiyata_indicators')->insert(array_merge($ind, ['created_at' => now(), 'updated_at' => now()]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('adiwiyata_indicators');
    }
};

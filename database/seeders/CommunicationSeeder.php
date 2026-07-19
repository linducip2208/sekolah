<?php

namespace Database\Seeders;

use App\Models\Communication\ReminderSchedule;
use App\Models\Communication\WaBotCommand;
use App\Models\School;
use Illuminate\Database\Seeder;

class CommunicationSeeder extends Seeder
{
    public function run(): void
    {
        $schools = School::all();

        foreach ($schools as $school) {
            $schoolId = $school->id;

            // Default WA Bot commands
            $defaultCommands = [
                ['nilai', 'text_function', null, 'getNilai', 'Cek nilai terbaru siswa'],
                ['jadwal', 'text_function', null, 'getJadwal', 'Lihat jadwal pelajaran hari ini'],
                ['spp', 'text_function', null, 'getTagihan', 'Cek tagihan SPP'],
                ['absen', 'text_function', null, 'getAbsensi', 'Cek absensi 30 hari terakhir'],
                ['bantuan', 'static', "🤖 *eSchool Bot*\n\nPerintah yang tersedia:\n\n• *nilai* — Cek nilai terbaru\n• *jadwal* — Lihat jadwal hari ini\n• *spp* — Cek tagihan SPP\n• *absen* — Cek absensi 30 hari\n\nKetik kata kunci di atas untuk memulai.", null, 'Daftar perintah bantuan'],
            ];

            foreach ($defaultCommands as $cmd) {
                WaBotCommand::firstOrCreate(
                    ['school_id' => $schoolId, 'command_keyword' => $cmd[0]],
                    [
                        'response_type'   => $cmd[1],
                        'static_response' => $cmd[2],
                        'function_method' => $cmd[3],
                        'description'     => $cmd[4],
                        'is_active'       => true,
                    ]
                );
            }

            // Default SPP Reminder schedule
            ReminderSchedule::firstOrCreate(
                ['school_id' => $schoolId, 'name' => 'Pengingat SPP'],
                [
                    'recipient_type'      => 'parent',
                    'trigger_days_before'  => [7, 3, 1],
                    'reminder_type'       => 'wa',
                    'message_template'    => "Yth. Bapak/Ibu {nama},\n\nKami ingatkan pembayaran SPP an. {nis}:\n💰 Jumlah: {jumlah}\n📅 Jatuh tempo: {jatuh_tempo}\n\nSilakan lakukan pembayaran melalui:\n{link_bayar}\n\nTerima kasih.\n— {sekolah}",
                    'is_active'           => true,
                ]
            );

            // Default Exam Reminder schedule
            ReminderSchedule::firstOrCreate(
                ['school_id' => $schoolId, 'name' => 'Pengingat Ujian'],
                [
                    'recipient_type'      => 'student',
                    'trigger_days_before'  => [3, 1],
                    'reminder_type'       => 'wa',
                    'message_template'    => "Halo {nama},\n\nPengingat ujian akan dilaksanakan pada:\n📅 {tanggal}\n\nPersiapkan diri dengan baik. Semangat!\n— {sekolah}",
                    'is_active'           => true,
                ]
            );

            // Default Event Reminder schedule
            ReminderSchedule::firstOrCreate(
                ['school_id' => $schoolId, 'name' => 'Pengingat Event Sekolah'],
                [
                    'recipient_type'      => 'parent',
                    'trigger_days_before'  => [7, 1],
                    'reminder_type'       => 'wa',
                    'message_template'    => "Yth. Bapak/Ibu {nama},\n\nJangan lewatkan event sekolah:\n📅 {tanggal}\n\nKehadiran Anda sangat berarti.\n— {sekolah}",
                    'is_active'           => true,
                ]
            );
        }
    }
}

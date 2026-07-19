<?php

namespace App\Services\Communication;

use App\Models\Academic\Student;
use App\Models\Communication\WaBotCommand;
use App\Models\Communication\WaBotConversation;
use App\Models\Communication\NotificationProvider;
use App\Services\Communication\Adapters\WhatsAppAdapter;
use Carbon\Carbon;

class WaBotService
{
    public function processIncoming(string $phone, string $message): array
    {
        $phone = $this->normalizePhone($phone);
        $student = Student::where('guardian_phone', $phone)
            ->orWhere('whatsapp_phone', $phone)
            ->first();

        $schoolId = $student?->school_id;

        if (!$schoolId) {
            $studentByUser = Student::whereHas('user', fn($q) => $q->where('phone', $phone))->first();
            $schoolId = $studentByUser?->school_id;
            if (!$studentByUser && !$schoolId) {
                return ['reply' => $this->fallbackReply(), 'matched' => false];
            }
            $student = $studentByUser;
        }

        $command = $this->matchCommand($schoolId, $message);

        WaBotConversation::create([
            'school_id'         => $schoolId,
            'phone'             => $phone,
            'student_id'        => $student?->id,
            'message_direction' => 'incoming',
            'message_text'      => $message,
            'matched_command'   => $command?->command_keyword,
            'session_active'    => true,
        ]);

        if (!$command) {
            $reply = $this->fallbackReply();
            $this->saveReply($schoolId, $phone, $student?->id, null, $reply);
            return ['reply' => $reply, 'matched' => false];
        }

        $reply = $this->executeCommand($command, $phone, $student);
        $this->saveReply($schoolId, $phone, $student?->id, $command->command_keyword, $reply);
        return ['reply' => $reply, 'matched' => true, 'command' => $command->command_keyword];
    }

    private function matchCommand(int $schoolId, string $message): ?WaBotCommand
    {
        $msg = strtolower(trim($message));

        $commands = WaBotCommand::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        foreach ($commands as $cmd) {
            $keyword = strtolower($cmd->command_keyword);
            if (str_contains($msg, $keyword)) {
                return $cmd;
            }
        }

        return null;
    }

    private function executeCommand(WaBotCommand $command, string $phone, ?Student $student): string
    {
        if ($command->response_type === 'static') {
            return $command->static_response ?? 'Maaf, tidak ada respons yang tersedia.';
        }

        return match ($command->function_method) {
            'getNilai'   => $this->getNilai($phone, $student),
            'getJadwal'  => $this->getJadwal($phone, $student),
            'getTagihan' => $this->getTagihan($phone, $student),
            'getAbsensi' => $this->getAbsensi($phone, $student),
            default      => $command->static_response ?? 'Fungsi tidak tersedia. Ketik *bantuan* untuk daftar perintah.',
        };
    }

    public function getNilai(string $phone, ?Student $student): string
    {
        if (!$student) {
            return "Maaf, nomor ini belum terdaftar sebagai wali siswa.\nKetik *bantuan* untuk daftar perintah.";
        }

        $marks = \App\Models\Academic\Mark::where('student_id', $student->id)
            ->with('subject:id,name')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        if ($marks->isEmpty()) {
            return "📚 *Nilai {$student->user?->name}*\n\nBelum ada data nilai yang tersedia saat ini.";
        }

        $text = "📚 *Nilai Terbaru — {$student->user?->name}*\n\n";
        foreach ($marks as $m) {
            $subject = $m->subject?->name ?? 'Mata Pelajaran';
            $score = $m->marks_obtained ?? $m->score ?? '-';
            $max = $m->total_marks ?? $m->max_score ?? '-';
            $text .= "• {$subject}: {$score}/{$max}\n";
        }
        $text .= "\nKetik *nilai* lagi untuk melihat nilai.";

        return $text;
    }

    public function getJadwal(string $phone, ?Student $student): string
    {
        if (!$student || !$student->class_section_id) {
            return "Maaf, data siswa tidak ditemukan atau siswa belum memiliki rombel.\nKetik *bantuan* untuk bantuan.";
        }

        $today = Carbon::now()->locale('id')->dayName;
        $slots = \App\Models\Academic\TimetableSlot::where('class_section_id', $student->class_section_id)
            ->with(['subject:id,name', 'staff.user:id,name'])
            ->where('day_of_week', Carbon::now()->dayOfWeek)
            ->orderBy('start_time')
            ->get();

        if ($slots->isEmpty()) {
            return "📅 *Jadwal {$today} — {$student->user?->name}*\n\nTidak ada jadwal pelajaran hari ini.";
        }

        $text = "📅 *Jadwal {$today} — {$student->user?->name}*\n\n";
        foreach ($slots as $s) {
            $start = Carbon::parse($s->start_time)->format('H:i');
            $end = Carbon::parse($s->end_time)->format('H:i');
            $subject = $s->subject?->name ?? 'Pelajaran';
            $teacher = $s->staff?->user?->name ?? '';
            $text .= "• {$start}-{$end} {$subject}";
            if ($teacher) $text .= " ({$teacher})";
            $text .= "\n";
        }

        return $text;
    }

    public function getTagihan(string $phone, ?Student $student): string
    {
        if (!$student) {
            return "Maaf, nomor ini belum terdaftar sebagai wali siswa.\nKetik *bantuan* untuk daftar perintah.";
        }

        $invoices = \App\Models\Finance\FeeInvoice::where('student_id', $student->id)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->orderBy('due_date')
            ->get();

        if ($invoices->isEmpty()) {
            return "💰 *Tagihan — {$student->user?->name}*\n\n✅ Tidak ada tagihan tertunda. Semua sudah lunas!";
        }

        $total = 0;
        $text = "💰 *Tagihan — {$student->user?->name}*\n\n";
        foreach ($invoices as $inv) {
            $remaining = ($inv->amount ?? 0) - ($inv->paid_amount ?? 0);
            $total += $remaining;
            $due = $inv->due_date?->format('d M Y') ?? '-';
            $rupiah = number_format($remaining / 100, 0, ',', '.');
            $text .= "• {$inv->invoice_no}: Rp {$rupiah} (Jatuh tempo: {$due})\n";
        }
        $totalRp = number_format($total / 100, 0, ',', '.');
        $text .= "\n💰 Total: Rp {$totalRp}\n\nSilakan lakukan pembayaran melalui portal orang tua atau transfer bank.";

        return $text;
    }

    public function getAbsensi(string $phone, ?Student $student): string
    {
        if (!$student) {
            return "Maaf, nomor ini belum terdaftar sebagai wali siswa.\nKetik *bantuan* untuk daftar perintah.";
        }

        $attendances = \App\Models\Academic\Attendance::where('student_id', $student->id)
            ->where('date', '>=', now()->subDays(30))
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        if ($attendances->isEmpty()) {
            return "📊 *Absensi — {$student->user?->name}*\n\nBelum ada data absensi 30 hari terakhir.";
        }

        $text = "📊 *Absensi 30 Hari — {$student->user?->name}*\n\n";
        foreach ($attendances as $status => $count) {
            $label = match ($status) {
                'present' => 'Hadir',
                'absent' => 'Alpha',
                'late' => 'Terlambat',
                'sick' => 'Sakit',
                'permission' => 'Izin',
                'holiday' => 'Libur',
                default => ucfirst($status),
            };
            $text .= "• {$label}: {$count} hari\n";
        }
        $text .= "\nKetik *absen* lagi untuk cek absensi.";

        return $text;
    }

    private function fallbackReply(): string
    {
        return "🤖 *eSchool Bot*\n\nHalo! Saya asisten virtual sekolah. Berikut perintah yang tersedia:\n\n• *nilai* — Cek nilai terbaru\n• *jadwal* — Lihat jadwal hari ini\n• *spp* — Cek tagihan SPP\n• *absen* — Cek absensi 30 hari\n\nKetik kata kunci di atas untuk memulai.\nAtau ketik *bantuan* untuk melihat daftar ini lagi.";
    }

    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        if (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    private function saveReply(int $schoolId, string $phone, ?int $studentId, ?string $command, string $reply): void
    {
        WaBotConversation::create([
            'school_id'         => $schoolId,
            'phone'             => $phone,
            'student_id'        => $studentId,
            'message_direction' => 'outgoing',
            'message_text'      => $reply,
            'matched_command'   => $command,
            'response_text'     => $reply,
            'session_active'    => true,
        ]);
    }

    public function sendReply(string $phone, string $reply, ?int $schoolId = null): array
    {
        $schoolId = $schoolId ?? auth()->user()?->school_id;

        if (!$schoolId) {
            return ['success' => false, 'error' => 'No school context'];
        }

        $provider = NotificationProvider::where('school_id', $schoolId)
            ->where('transport', 'whatsapp')
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->first();

        if (!$provider) {
            return ['success' => false, 'error' => 'No active WhatsApp provider'];
        }

        $adapter = new WhatsAppAdapter($provider);
        return $adapter->send($this->normalizePhone($phone), $reply);
    }
}

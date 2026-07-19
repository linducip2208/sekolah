<?php

namespace App\Services\Communication;

use App\Jobs\SendWhatsAppNotification;
use App\Models\Academic\Student;
use App\Models\Finance\FeeInvoice;
use Illuminate\Support\Facades\Log;

class AutoWhatsAppNotifier
{
    public function notifyAttendance(int $studentId, string $status, string $date, ?string $notes = null): void
    {
        $student = Student::with('user')->find($studentId);
        if (!$student) return;

        $phone = $student->whatsapp_phone ?? $student->guardian_phone;
        if (!$phone) return;

        $statusLabel = match ($status) {
            'present' => 'Hadir', 'absent' => 'Tidak Hadir',
            'late' => 'Terlambat', 'sick' => 'Sakit',
            'leave' => 'Izin', default => $status,
        };

        $nama = $student->user?->name ?? 'Siswa';
        $sekolah = config('app.name', 'Sekolah');

        $message = "*Notifikasi Kehadiran*\n\n";
        $message .= "Yth. Orang Tua/Wali dari:\n";
        $message .= "*{$nama}* (NIS: {$student->admission_no})\n\n";
        $message .= "Status Kehadiran: *{$statusLabel}*\n";
        $message .= "Tanggal: {$date}\n";

        if ($notes) {
            $message .= "Catatan: {$notes}\n";
        }

        $message .= "\n— {$sekolah}";

        SendWhatsAppNotification::dispatch($phone, $message, $student->school_id);
    }

    public function notifyInvoice(int $invoiceId): void
    {
        $invoice = FeeInvoice::with('student.user', 'feeStructure')->find($invoiceId);
        if (!$invoice) return;

        $student = $invoice->student;
        if (!$student) return;

        $phone = $student->whatsapp_phone ?? $student->guardian_phone;
        if (!$phone) return;

        $nama = $student->user?->name ?? 'Siswa';
        $sekolah = config('app.name', 'Sekolah');
        $amount = 'Rp ' . number_format($invoice->total_amount, 0, ',', '.');
        $due = $invoice->due_date ? $invoice->due_date->format('d F Y') : '-';
        $desc = $invoice->feeStructure?->name ?? $invoice->description ?? 'Tagihan Sekolah';

        $message = "*Tagihan Sekolah*\n\n";
        $message .= "Yth. Orang Tua/Wali dari:\n";
        $message .= "*{$nama}* (NIS: {$student->admission_no})\n\n";
        $message .= "Jenis: *{$desc}*\n";
        $message .= "Jumlah: *{$amount}*\n";
        $message .= "Jatuh Tempo: {$due}\n\n";
        $message .= "Mohon segera lakukan pembayaran. Terima kasih.\n\n";
        $message .= "— {$sekolah}";

        SendWhatsAppNotification::dispatch($phone, $message, $invoice->school_id);
    }

    public function notifyExam(int $examId): void
    {
        $exam = \App\Models\Academic\Exam::with('classSection.students.user', 'subject')->find($examId);
        if (!$exam) return;

        $className = $exam->classSection?->classRoom?->name . ' ' . $exam->classSection?->section?->name;
        $subjectName = $exam->subject?->name ?? 'Ujian';
        $start = $exam->start_at?->format('d F Y, H:i') ?? '-';
        $sekolah = config('app.name', 'Sekolah');

        $students = $exam->classSection?->students ?? collect();

        foreach ($students as $student) {
            $phone = $student->whatsapp_phone ?? $student->guardian_phone;
            if (!$phone) continue;

            $nama = $student->user?->name ?? 'Siswa';

            $message = "*Jadwal Ujian*\n\n";
            $message .= "Yth. Orang Tua/Wali dari:\n";
            $message .= "*{$nama}* (NIS: {$student->admission_no})\n\n";
            $message .= "Mata Pelajaran: *{$subjectName}*\n";
            $message .= "Kelas: {$className}\n";
            $message .= "Tanggal: {$start}\n\n";
            $message .= "Mohon bimbingan belajar untuk persiapan ujian.\n\n";
            $message .= "— {$sekolah}";

            SendWhatsAppNotification::dispatch($phone, $message, $exam->school_id);
        }
    }

    public function notifyDiscipline(int $recordId): void
    {
        $record = \App\Models\Discipline\DisciplineRecord::with('student.user', 'category')->find($recordId);
        if (!$record) return;

        $student = $record->student;
        if (!$student) return;

        $phone = $student->whatsapp_phone ?? $student->guardian_phone;
        if (!$phone) return;

        $nama = $student->user?->name ?? 'Siswa';
        $sekolah = config('app.name', 'Sekolah');
        $category = $record->category?->name ?? 'Pelanggaran';
        $desc = $record->description ?? '';
        $type = $record->type === 'achievement' ? 'Prestasi' : 'Pelanggaran';

        $message = "*Catatan {$type}*\n\n";
        $message .= "Yth. Orang Tua/Wali dari:\n";
        $message .= "*{$nama}* (NIS: {$student->admission_no})\n\n";
        $message .= "Kategori: *{$category}*\n";
        if ($desc) {
            $message .= "Deskripsi: {$desc}\n";
        }
        $message .= "\nMohon bimbingan orang tua di rumah.\n\n";
        $message .= "— {$sekolah}";

        SendWhatsAppNotification::dispatch($phone, $message, $record->school_id);
    }
}

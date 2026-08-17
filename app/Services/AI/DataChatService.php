<?php

namespace App\Services\AI;

use App\Models\Academic\Attendance;
use App\Models\Academic\Mark;
use App\Models\Academic\Student;
use App\Models\AI\AiDataChatLog;
use App\Models\Facilities\Book;
use App\Models\Facilities\BookIssue;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeePayment;
use App\Models\Staff;

class DataChatService
{
    public function __construct(protected AiService $ai) {}

    /** Registry of safe, pre-built metrics (no raw user SQL — zero injection risk). */
    public function metrics(): array
    {
        return [
            'students_by_class'       => 'Siswa per Rombel',
            'attendance_rate'         => 'Tingkat Kehadiran',
            'revenue_by_month'        => 'Pendapatan per Bulan',
            'unpaid_invoices'         => 'Tunggakan SPP',
            'students_by_gender'      => 'Siswa per Jenis Kelamin',
            'average_marks_by_subject'=> 'Rata-rata Nilai per Mapel',
            'library_books'           => 'Perpustakaan',
            'staff_count'             => 'Staf per Departemen',
        ];
    }

    public function ask(int $schoolId, int $userId, string $question): array
    {
        $metricKey = null;
        $aiAnswer  = null;
        $usedAi    = false;

        try {
            $interpreted = $this->interpretWithAi($schoolId, $userId, $question);
            $metricKey   = $interpreted['metric_key'] ?? null;
            $aiAnswer    = $interpreted['answer'] ?? null;
            $usedAi      = true;
        } catch (\Throwable $e) {
            $metricKey = $this->fallbackMetricKey($question);
        }

        $metricKey = $this->metrics()[$metricKey] ? $metricKey : 'students_by_class';

        $result = $this->runMetric($schoolId, $metricKey);

        $answer = $aiAnswer ?: $this->buildAnswer($metricKey, $result);

        AiDataChatLog::create([
            'school_id'  => $schoolId,
            'user_id'    => $userId,
            'question'   => $question,
            'metric_key' => $metricKey,
            'result'     => $result,
            'answer'     => $answer,
            'used_ai'    => $usedAi,
        ]);

        return [
            'metric_key' => $metricKey,
            'metric_label' => $this->metrics()[$metricKey],
            'answer'     => $answer,
            'columns'    => $result['columns'],
            'rows'       => $result['rows'],
            'summary'    => $result['summary'],
            'used_ai'    => $usedAi,
        ];
    }

    protected function interpretWithAi(int $schoolId, int $userId, string $question): array
    {
        $metricList = collect($this->metrics())->map(fn ($label, $key) => "{$key}: {$label}")->implode("\n");

        $system = <<<PROMPT
Anda adalah asisten analisis data sekolah. Petakan pertanyaan pengguna (Bahasa Indonesia) ke SATU metric_key dari daftar berikut:
{$metricList}

Balas HANYA JSON valid tanpa teks lain:
{"metric_key": "<key>", "answer": "<jawaban singkat 1-2 kalimat dalam Bahasa Indonesia>"}
Pilih metric_key yang paling relevan dengan pertanyaan.
PROMPT;

        $messages = [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $question],
        ];

        $result = $this->ai->chatForFeature($schoolId, $userId, 'data_chat', $messages, [
            'temperature' => 0,
            'max_tokens'  => 256,
        ]);

        $text = trim($result['text'] ?? '');
        $json = json_decode($this->extractJson($text), true);

        return is_array($json) ? $json : ['metric_key' => null, 'answer' => null];
    }

    protected function extractJson(string $raw): string
    {
        if (preg_match('/\{[\s\S]*\}/', trim($raw), $m)) {
            return $m[0];
        }
        return $raw;
    }

    /** Keyword → metric key fallback when no AI is configured. */
    public function fallbackMetricKey(string $question): string
    {
        $q = strtolower($question);

        return match (true) {
            str_contains($q, 'hadir') || str_contains($q, 'kehadiran') || str_contains($q, 'absen') || str_contains($q, 'attendance') => 'attendance_rate',
            str_contains($q, 'tunggak') || str_contains($q, 'belum bayar') || str_contains($q, 'spp') && str_contains($q, 'belum') || str_contains($q, 'unpaid') || str_contains($q, 'invoice') => 'unpaid_invoices',
            str_contains($q, 'pendapatan') || str_contains($q, 'pemasukan') || str_contains($q, 'revenue') || str_contains($q, 'bayar') || str_contains($q, 'bulan') => 'revenue_by_month',
            str_contains($q, 'laki') || str_contains($q, 'perempuan') || str_contains($q, 'gender') || str_contains($q, 'jenis kelamin') => 'students_by_gender',
            str_contains($q, 'nilai') || str_contains($q, 'mapel') || str_contains($q, 'marks') || str_contains($q, 'rapor') => 'average_marks_by_subject',
            str_contains($q, 'buku') || str_contains($q, 'perpustakaan') || str_contains($q, 'library') || str_contains($q, 'pinjam') => 'library_books',
            str_contains($q, 'guru') || str_contains($q, 'staff') || str_contains($q, 'pegawai') || str_contains($q, 'karyawan') || str_contains($q, 'staf') || str_contains($q, 'departemen') => 'staff_count',
            str_contains($q, 'rombel') || str_contains($q, 'kelas') || str_contains($q, 'siswa') || str_contains($q, 'student') || str_contains($q, 'murid') => 'students_by_class',
            default => 'students_by_class',
        };
    }

    public function runMetric(int $schoolId, string $key): array
    {
        return match ($key) {
            'students_by_class'        => $this->studentsByClass($schoolId),
            'attendance_rate'          => $this->attendanceRate($schoolId),
            'revenue_by_month'         => $this->revenueByMonth($schoolId),
            'unpaid_invoices'          => $this->unpaidInvoices($schoolId),
            'students_by_gender'       => $this->studentsByGender($schoolId),
            'average_marks_by_subject' => $this->averageMarksBySubject($schoolId),
            'library_books'            => $this->libraryBooks($schoolId),
            'staff_count'              => $this->staffCount($schoolId),
            default                    => $this->studentsByClass($schoolId),
        };
    }

    protected function studentsByClass(int $schoolId): array
    {
        $rows = Student::where('school_id', $schoolId)
            ->with(['classSection.classRoom', 'classSection.section'])
            ->get()
            ->groupBy(fn ($s) => trim(($s->classSection?->classRoom?->name ?? 'Tanpa Rombel') . ' ' . ($s->classSection?->section?->name ?? '')))
            ->map(fn ($g) => ['label' => $g->first()->classSection ? ($g->first()->classSection->classRoom?->name . ' ' . $g->first()->classSection->section?->name) : 'Tanpa Rombel', 'value' => $g->count()])
            ->sortByDesc('value')
            ->values()
            ->all();

        return [
            'columns' => ['Rombel', 'Jumlah Siswa'],
            'rows'    => $rows,
            'summary' => 'Total ' . array_sum(array_column($rows, 'value')) . ' siswa',
        ];
    }

    protected function attendanceRate(int $schoolId, ?string $from = null, ?string $to = null): array
    {
        $base = fn () => Attendance::where('school_id', $schoolId)
            ->when($from, fn ($q) => $q->whereDate('date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('date', '<=', $to));

        $total   = $base()->count();
        $present = (clone $base())->where('status', 'present')->count();
        $absent  = (clone $base())->where('status', 'absent')->count();
        $late    = (clone $base())->where('status', 'late')->count();

        $rate = $total > 0 ? round($present / $total * 100, 1) : 0;

        return [
            'columns' => ['Status', 'Jumlah'],
            'rows'    => [
                ['label' => 'Hadir', 'value' => $present],
                ['label' => 'Terlambat', 'value' => $late],
                ['label' => 'Tanpa Keterangan', 'value' => $absent],
            ],
            'summary' => "Tingkat kehadiran {$rate}% dari {$total} catatan",
        ];
    }

    protected function revenueByMonth(int $schoolId): array
    {
        $rows = FeePayment::where('school_id', $schoolId)
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($r) => ['label' => $r->month, 'value' => (int) $r->total, 'currency' => true])
            ->all();

        return [
            'columns' => ['Bulan', 'Total (Rp)'],
            'rows'    => $rows,
            'summary' => 'Total ' . $this->rupiah(array_sum(array_column($rows, 'value'))),
        ];
    }

    protected function unpaidInvoices(int $schoolId): array
    {
        $invoices = FeeInvoice::where('school_id', $schoolId)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->get();

        $rows = $invoices->map(fn ($i) => [
            'label'    => '#' . $i->id,
            'value'    => $i->amount - $i->paid_amount,
            'currency' => true,
        ])->all();

        return [
            'columns' => ['Invoice', 'Sisa (Rp)'],
            'rows'    => $rows,
            'summary' => $invoices->count() . ' invoice menunggak, total ' . $this->rupiah($invoices->sum(fn ($i) => $i->amount - $i->paid_amount)),
        ];
    }

    protected function studentsByGender(int $schoolId): array
    {
        $rows = Student::where('school_id', $schoolId)
            ->selectRaw('gender, COUNT(*) as total')
            ->groupBy('gender')
            ->get()
            ->map(fn ($r) => ['label' => $r->gender ?: 'Tidak diisi', 'value' => (int) $r->total])
            ->all();

        return [
            'columns' => ['Jenis Kelamin', 'Jumlah'],
            'rows'    => $rows,
            'summary' => array_sum(array_column($rows, 'value')) . ' siswa',
        ];
    }

    protected function averageMarksBySubject(int $schoolId): array
    {
        $rows = Mark::where('school_id', $schoolId)
            ->with('subject')
            ->get()
            ->groupBy(fn ($m) => $m->subject?->name ?? 'Tanpa Mapel')
            ->map(fn ($g, $name) => ['label' => $name, 'value' => round($g->avg('obtained_marks') ?? 0, 1)])
            ->sortByDesc('value')
            ->values()
            ->all();

        return [
            'columns' => ['Mapel', 'Rata-rata Nilai'],
            'rows'    => $rows,
            'summary' => count($rows) . ' mapel',
        ];
    }

    protected function libraryBooks(int $schoolId): array
    {
        $totalBooks = Book::where('school_id', $schoolId)->count();
        $issued     = BookIssue::where('school_id', $schoolId)->whereIn('status', ['issued', 'overdue'])->count();

        return [
            'columns' => ['Kategori', 'Jumlah'],
            'rows'    => [
                ['label' => 'Total Buku', 'value' => $totalBooks],
                ['label' => 'Sedang Dipinjam', 'value' => $issued],
                ['label' => 'Tersedia', 'value' => max(0, $totalBooks - $issued)],
            ],
            'summary' => "{$totalBooks} buku, {$issued} sedang dipinjam",
        ];
    }

    protected function staffCount(int $schoolId): array
    {
        $rows = Staff::where('school_id', $schoolId)
            ->selectRaw('COALESCE(department, "Umum") as dept, COUNT(*) as total')
            ->groupBy('dept')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => ['label' => $r->dept, 'value' => (int) $r->total])
            ->all();

        return [
            'columns' => ['Departemen', 'Jumlah'],
            'rows'    => $rows,
            'summary' => array_sum(array_column($rows, 'value')) . ' staf',
        ];
    }

    protected function buildAnswer(string $key, array $result): string
    {
        return ($this->metrics()[$key] ?? 'Data') . ': ' . ($result['summary'] ?? '');
    }

    protected function rupiah(int $cents): string
    {
        return 'Rp ' . number_format($cents / 100, 0, ',', '.');
    }
}

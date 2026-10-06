<?php

namespace App\Services\Export;

use App\Models\Academic\Mark;
use App\Models\Finance\FeeInvoice;
use Illuminate\Http\Response;

class MarksExportService
{
    /**
     * Escape a CSV cell: neutralizes spreadsheet formula injection
     * (=, +, -, @, tab/CR as first char) per OWASP guidance.
     */
    public static function cell(mixed $value): string
    {
        $cell = (string) ($value ?? '');
        if ($cell !== '' && str_contains('=+-@'."\t\r", $cell[0])) {
            return "'".$cell;
        }

        return $cell;
    }

    /** @param array<int,array<int,mixed>> $rows */
    private function toCsv(array $header, array $rows): string
    {
        $fh = fopen('php://temp', 'r+');
        fputcsv($fh, $header);
        foreach ($rows as $row) {
            fputcsv($fh, array_map(self::cell(...), $row));
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $csv;
    }

    public function exportByClass(int $classSectionId, int $semesterId): Response
    {
        $marks = Mark::where('school_id', auth()->user()->school_id)
            ->whereHas('student', fn ($q) => $q->where('class_section_id', $classSectionId))
            ->where('semester_id', $semesterId)
            ->with(['student.user', 'subject'])
            ->orderBy('student_id')
            ->get();

        $rows = [];
        foreach ($marks as $mark) {
            $rows[] = [
                $mark->student->user->name ?? '',
                $mark->student->admission_no ?? '',
                $mark->subject->name ?? '',
                $mark->obtained_marks,
                $mark->total_marks,
                $mark->percentage,
                $mark->grade ?? '',
                $semesterId,
            ];
        }

        $csv = $this->toCsv(['student_name', 'admission_no', 'subject', 'obtained_marks', 'total_marks', 'percentage', 'grade', 'semester_id'], $rows);
        $filename = "marks_class_{$classSectionId}_semester_{$semesterId}.csv";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportFeeCollection(string $period): Response
    {
        $invoices = FeeInvoice::where('school_id', auth()->user()->school_id)
            ->where('period', $period)
            ->with(['student.user', 'feeStructure'])
            ->orderBy('status')
            ->get();

        $rows = [];
        foreach ($invoices as $inv) {
            $rows[] = [
                $inv->student->user->name ?? '',
                $inv->invoice_no,
                $inv->feeStructure->name ?? '',
                $inv->amount,
                $inv->status,
                $inv->period,
                $inv->due_date,
            ];
        }

        $csv = $this->toCsv(['student_name', 'invoice_no', 'structure', 'amount', 'status', 'period', 'due_date'], $rows);
        $filename = "fee_collection_{$period}.csv";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}

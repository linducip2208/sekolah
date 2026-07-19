<?php

namespace App\Services\Export;

use App\Models\Academic\Mark;
use Illuminate\Http\Response;

class MarksExportService
{
    public function exportByClass(int $classSectionId, int $semesterId): Response
    {
        $marks = Mark::where('school_id', auth()->user()->school_id)
            ->whereHas('student', fn($q) => $q->where('class_section_id', $classSectionId))
            ->where('semester_id', $semesterId)
            ->with(['student.user', 'subject'])
            ->orderBy('student_id')
            ->get();

        $lines   = [];
        $lines[] = implode(',', ['student_name', 'admission_no', 'subject', 'obtained_marks', 'total_marks', 'percentage', 'grade', 'semester_id']);

        foreach ($marks as $mark) {
            $lines[] = implode(',', [
                '"' . ($mark->student->user->name ?? '') . '"',
                '"' . ($mark->student->admission_no ?? '') . '"',
                '"' . ($mark->subject->name ?? '') . '"',
                $mark->obtained_marks,
                $mark->total_marks,
                $mark->percentage,
                '"' . ($mark->grade ?? '') . '"',
                $semesterId,
            ]);
        }

        $csv      = implode("\n", $lines);
        $filename = "marks_class_{$classSectionId}_semester_{$semesterId}.csv";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportFeeCollection(string $period): Response
    {
        $invoices = \App\Models\Finance\FeeInvoice::where('school_id', auth()->user()->school_id)
            ->where('period', $period)
            ->with(['student.user', 'feeStructure'])
            ->orderBy('status')
            ->get();

        $lines   = [];
        $lines[] = implode(',', ['student_name', 'invoice_no', 'structure', 'amount', 'status', 'period', 'due_date']);

        foreach ($invoices as $inv) {
            $lines[] = implode(',', [
                '"' . ($inv->student->user->name ?? '') . '"',
                '"' . $inv->invoice_no . '"',
                '"' . ($inv->feeStructure->name ?? '') . '"',
                $inv->amount,
                $inv->status,
                $inv->period,
                $inv->due_date,
            ]);
        }

        $csv      = implode("\n", $lines);
        $filename = "fee_collection_{$period}.csv";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}

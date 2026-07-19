<?php

namespace App\Services\Academic;

use App\Models\Academic\ReportCard;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ReportCardPdfService
{
    public function generate(ReportCard $reportCard): Response
    {
        $reportCard->load([
            'student.user',
            'student.classSection.classRoom',
            'student.classSection.section',
            'semester',
        ]);

        $pdf = Pdf::loadView('pdf.report-card', [
            'reportCard' => $reportCard,
            'student'    => $reportCard->student,
            'school'     => \App\Models\School::find($reportCard->school_id),
        ])->setPaper('a4');

        $filename = 'rapor_' . str_replace(' ', '_', $reportCard->student->user->name) . '_' . $reportCard->semester_id . '.pdf';

        return $pdf->download($filename);
    }

    public function generateForClass(int $classSectionId, int $semesterId): \Illuminate\Http\Response
    {
        $cards = ReportCard::where('school_id', auth()->user()->school_id)
            ->whereHas('student', fn($q) => $q->where('class_section_id', $classSectionId))
            ->where('semester_id', $semesterId)
            ->with(['student.user', 'student.classSection'])
            ->get();

        $pdf = Pdf::loadView('pdf.report-cards-batch', [
            'cards'  => $cards,
            'school' => app('current_school'),
        ])->setPaper('a4');

        return $pdf->download("rapor_kelas_{$classSectionId}_semester_{$semesterId}.pdf");
    }
}

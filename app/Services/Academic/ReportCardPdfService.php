<?php

namespace App\Services\Academic;

use App\Models\Academic\ClassSection;
use App\Models\Academic\ReportCard;
use App\Models\Academic\Semester;
use App\Models\School;
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
            'student' => $reportCard->student,
            'school' => School::find($reportCard->school_id),
        ])->setPaper('a4');

        $filename = 'rapor_'.str_replace(' ', '_', $reportCard->student->user->name).'_'.$reportCard->semester_id.'.pdf';

        return $pdf->download($filename);
    }

    public function generateForClass(int $classSectionId, int $semesterId): Response
    {
        $schoolId = (int) auth()->user()->school_id;
        $classSection = ClassSection::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->findOrFail($classSectionId);
        Semester::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->findOrFail($semesterId);

        $cards = ReportCard::where('school_id', $schoolId)
            ->whereHas('student', fn ($q) => $q->where('class_section_id', $classSectionId))
            ->where('semester_id', $semesterId)
            ->with(['student.user', 'student.classSection'])
            ->get();

        $pdf = Pdf::loadView('pdf.report-cards-batch', [
            'cards' => $cards,
            'school' => app('current_school'),
        ])->setPaper('a4');

        return $pdf->download("rapor_kelas_{$classSectionId}_semester_{$semesterId}.pdf");
    }
}

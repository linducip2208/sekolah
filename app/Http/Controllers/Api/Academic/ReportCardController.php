<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ReportCard;
use App\Services\Academic\ReportCardPdfService;
use Illuminate\Http\Response;

class ReportCardController extends Controller
{
    public function __construct(private ReportCardPdfService $pdfService) {}

    public function downloadPdf(int $reportCardId): Response
    {
        $reportCard = ReportCard::where('school_id', auth()->user()->school_id)
            ->findOrFail($reportCardId);

        return $this->pdfService->generate($reportCard);
    }

    public function downloadClassPdf(int $classSectionId, int $semesterId): Response
    {
        return $this->pdfService->generateForClass($classSectionId, $semesterId);
    }
}

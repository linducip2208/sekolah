<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Services\Export\MarksExportService;
use App\Services\Import\StudentImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ImportExportController extends Controller
{
    public function __construct(
        private StudentImportService $importer,
        private MarksExportService   $exporter,
    ) {}

    public function importStudents(Request $request): JsonResponse
    {
        $request->validate([
            'file'              => 'required|file|mimes:csv,txt|max:2048',
            'class_section_id'  => 'required|integer|exists:class_sections,id',
        ]);

        $result = $this->importer->import($request->file('file'), $request->class_section_id);

        return response()->json($result, $result['errors'] ? 207 : 200);
    }

    public function studentImportTemplate(): Response
    {
        $csv = $this->importer->template();
        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_import_template.csv"',
        ]);
    }

    public function exportMarks(Request $request): Response
    {
        $request->validate([
            'class_section_id' => 'required|integer',
            'semester_id'      => 'required|integer',
        ]);
        return $this->exporter->exportByClass($request->class_section_id, $request->semester_id);
    }

    public function exportFeeCollection(Request $request): Response
    {
        $request->validate(['period' => 'required|date_format:Y-m']);
        return $this->exporter->exportFeeCollection($request->period);
    }
}

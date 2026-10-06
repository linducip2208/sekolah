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
        $this->requireAny($request, ['student.manage'], ['admin', 'teacher']);
        $request->validate([
            'file'              => 'required|file|mimes:csv,txt|max:2048',
            'class_section_id'  => 'required|integer|exists:class_sections,id',
        ]);

        $result = $this->importer->import($request->file('file'), $request->class_section_id);

        return response()->json($result, $result['errors'] ? 207 : 200);
    }

    /**
     * Staff-only gate: super_admin bypasses; otherwise require one of the
     * permissions or one of the fallback roles.
     */
    private function requireAny(Request $request, array $permissions, array $roles): void
    {
        $user = $request->user();
        if ($user->hasRole('super_admin')) {
            return;
        }
        foreach ($permissions as $perm) {
            if ($user->can($perm)) {
                return;
            }
        }
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return;
            }
        }
        abort(403, 'Tidak memiliki izin untuk operasi ini.');
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
        $this->requireAny($request, ['marks.view', 'marks.manage'], ['admin', 'teacher']);
        $request->validate([
            'class_section_id' => 'required|integer',
            'semester_id'      => 'required|integer',
        ]);
        return $this->exporter->exportByClass($request->class_section_id, $request->semester_id);
    }

    public function exportFeeCollection(Request $request): Response
    {
        $this->requireAny($request, ['fee.view'], ['admin', 'accountant']);
        $request->validate(['period' => 'required|date_format:Y-m']);
        return $this->exporter->exportFeeCollection($request->period);
    }
}

<?php

namespace App\Http\Controllers\Web\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Exam;
use App\Models\Academic\Semester;
use App\Models\Academic\Subject;
use App\Models\Analytics\ReportTemplate;
use App\Models\Analytics\SavedReport;
use App\Services\ReportBuilderService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportBuilderController extends Controller
{
    private function schoolId(): int { return auth()->user()->school_id; }

    public function index(Request $request): View
    {
        $templates = ReportTemplate::where('school_id', $this->schoolId())
            ->orWhere('is_shared', true)
            ->where('school_id', $this->schoolId())
            ->latest()
            ->get();

        $savedReports = SavedReport::where('school_id', $this->schoolId())
            ->latest()
            ->take(10)
            ->get();

        $classSections = ClassSection::with('classRoom', 'section')
            ->where('school_id', $this->schoolId())->get();

        $exams = Exam::where('school_id', $this->schoolId())->get();
        $subjects = Subject::where('school_id', $this->schoolId())->get();
        $semesters = Semester::whereHas('academicYear', fn($q) => $q->where('school_id', $this->schoolId()))->get();

        return view('school-admin.reports.builder', compact(
            'templates', 'savedReports', 'classSections', 'exams', 'subjects', 'semesters'
        ));
    }

    public function preview(Request $request): \Illuminate\Http\JsonResponse
    {
        $config = $request->json('config', []);
        $service = app(ReportBuilderService::class);

        try {
            $data = $service->getPreviewData($config);
            return response()->json($data);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $config = $request->json('config', []);
        $service = app(ReportBuilderService::class);

        return $service->exportCsv($config);
    }

    public function exportPdf(Request $request): \Illuminate\Http\JsonResponse
    {
        $config = $request->json('config', []);
        $service = app(ReportBuilderService::class);

        try {
            $path = $service->exportPdf($config);
            $downloadUrl = route('admin.reports.builder.download', ['path' => base64_encode($path)]);

            return response()->json(['success' => true, 'download_url' => $downloadUrl]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function download(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $path = base64_decode($request->query('path', ''));
        $fullPath = storage_path('app/' . $path);

        if (!file_exists($fullPath)) {
            abort(404);
        }

        return response()->download($fullPath)->deleteFileAfterSend();
    }

    public function saveTemplate(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'report_type' => 'required|in:student,academic,finance,attendance,combined',
            'config'      => 'required|array',
            'is_shared'   => 'boolean',
        ]);

        $template = ReportTemplate::create([
            'school_id'   => $this->schoolId(),
            'user_id'     => auth()->id(),
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'report_type' => $validated['report_type'],
            'config'      => $validated['config'],
            'is_shared'   => $validated['is_shared'] ?? false,
        ]);

        return response()->json([
            'success'  => true,
            'template' => $template,
            'message'  => 'Template berhasil disimpan.',
        ]);
    }

    public function updateTemplate(Request $request, ReportTemplate $template): \Illuminate\Http\JsonResponse
    {
        if ($template->school_id !== $this->schoolId() && !$template->is_shared) {
            abort(403);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'report_type' => 'required|in:student,academic,finance,attendance,combined',
            'config'      => 'required|array',
            'is_shared'   => 'boolean',
        ]);

        $template->update($validated);

        return response()->json([
            'success'  => true,
            'template' => $template,
            'message'  => 'Template berhasil diperbarui.',
        ]);
    }

    public function deleteTemplate(ReportTemplate $template): \Illuminate\Http\JsonResponse
    {
        if ($template->school_id !== $this->schoolId()) {
            abort(403);
        }

        $template->delete();

        return response()->json(['success' => true, 'message' => 'Template berhasil dihapus.']);
    }

    public function loadTemplate(ReportTemplate $template): \Illuminate\Http\JsonResponse
    {
        if ($template->school_id !== $this->schoolId() && !$template->is_shared) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(['config' => $template->config]);
    }

    public function templates(): \Illuminate\Http\JsonResponse
    {
        $templates = ReportTemplate::where('school_id', $this->schoolId())->latest()->get();

        return response()->json(['templates' => $templates]);
    }
}

<?php

namespace App\Http\Controllers\Web\Admin\Alumni;

use App\Http\Controllers\Controller;
use App\Models\Alumni\TracerQuestion;
use App\Models\Alumni\TracerResponse;
use App\Models\Alumni\AlumniProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TracerController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }

    public function questions(): View
    {
        $questions = TracerQuestion::where('school_id', $this->schoolId())
            ->orderBy('sort_order')->get();

        return view('school-admin.alumni.tracer.questions', compact('questions'));
    }

    public function storeQuestion(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'question_text' => 'required|string|max:500',
            'question_type' => 'required|in:text,radio,select,textarea',
            'options'       => 'nullable|string',
            'sort_order'    => 'nullable|integer|min:0',
        ]);

        $options = null;
        if (!empty($data['options']) && in_array($data['question_type'], ['radio', 'select'])) {
            $lines = explode("\n", str_replace("\r", '', $data['options']));
            $options = array_values(array_filter(array_map('trim', $lines)));
        }

        TracerQuestion::create([
            'school_id'     => $this->schoolId(),
            'question_text' => $data['question_text'],
            'question_type' => $data['question_type'],
            'options'       => $options,
            'sort_order'    => $data['sort_order'] ?? 0,
            'is_active'     => true,
        ]);

        return back()->with('success', 'Pertanyaan tracer ditambahkan.');
    }

    public function updateQuestion(Request $request, TracerQuestion $question): RedirectResponse
    {
        $this->authorizeOwn($question);

        $data = $request->validate([
            'question_text' => 'required|string|max:500',
            'question_type' => 'required|in:text,radio,select,textarea',
            'options'       => 'nullable|string',
            'sort_order'    => 'nullable|integer|min:0',
            'is_active'     => 'boolean',
        ]);

        $options = null;
        if (!empty($data['options']) && in_array($data['question_type'], ['radio', 'select'])) {
            $lines = explode("\n", str_replace("\r", '', $data['options']));
            $options = array_values(array_filter(array_map('trim', $lines)));
        }

        $question->update([
            'question_text' => $data['question_text'],
            'question_type' => $data['question_type'],
            'options'       => $options,
            'sort_order'    => $data['sort_order'] ?? 0,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Pertanyaan diperbarui.');
    }

    public function deleteQuestion(TracerQuestion $question): RedirectResponse
    {
        $this->authorizeOwn($question);
        $question->delete();
        return back()->with('success', 'Pertanyaan dihapus.');
    }

    public function responses(Request $request): View
    {
        $schoolId = $this->schoolId();
        $query = TracerResponse::where('school_id', $schoolId)
            ->with('alumniProfile.user:id,name')
            ->orderByDesc('submitted_at');

        if ($request->has('year') && $request->year) {
            $query->where('graduation_year', $request->year);
        }
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $responses = $query->paginate(20)->appends($request->query());
        $years = AlumniProfile::where('school_id', $schoolId)
            ->distinct()->orderByDesc('graduation_year')->pluck('graduation_year');
        $statuses = ['kerja' => 'Kerja', 'kuliah' => 'Kuliah', 'wirausaha' => 'Wirausaha', 'menganggur' => 'Menganggur', 'lainnya' => 'Lainnya'];

        return view('school-admin.alumni.tracer.responses', compact('responses', 'years', 'statuses'));
    }

    public function dashboard(): View
    {
        $schoolId = $this->schoolId();
        $responses = TracerResponse::where('school_id', $schoolId)->get();

        $statusCounts = $responses->groupBy('status')->map->count();
        $totalResponses = $responses->count();
        $totalAlumni = AlumniProfile::where('school_id', $schoolId)->count();
        $responseRate = $totalAlumni > 0 ? round(($totalResponses / $totalAlumni) * 100, 1) : 0;

        $relevant = $responses->where('is_relevant', true)->count();
        $notRelevant = $responses->where('is_relevant', false)->count();
        $unknown = $responses->whereNull('is_relevant')->count();

        $salaryData = $responses->whereNotNull('salary_range')->groupBy('salary_range')->map->count();

        $byYear = $responses->groupBy('graduation_year')->map->count()->sortKeys();

        $recentResponses = TracerResponse::where('school_id', $schoolId)
            ->with('alumniProfile.user:id,name')
            ->orderByDesc('submitted_at')->limit(10)->get();

        return view('school-admin.alumni.tracer.dashboard', compact(
            'statusCounts', 'totalResponses', 'totalAlumni', 'responseRate',
            'relevant', 'notRelevant', 'unknown', 'salaryData', 'byYear', 'recentResponses'
        ));
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $schoolId = $this->schoolId();
        $responses = TracerResponse::where('school_id', $schoolId)
            ->with('alumniProfile.user:id,name')->orderByDesc('submitted_at')->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="tracer-study-' . date('Ymd') . '.csv"',
        ];

        return response()->streamDownload(function () use ($responses) {
            $fp = fopen('php://output', 'w');
            fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($fp, ['Nama Alumni', 'Tahun Lulus', 'Status', 'Perusahaan', 'Jabatan', 'Gaji', 'Relevan', 'Feedback', 'Tanggal Isi']);
            foreach ($responses as $r) {
                fputcsv($fp, [
                    $r->alumniProfile?->user?->name ?? '-',
                    $r->graduation_year,
                    $r->status,
                    $r->company_name,
                    $r->position,
                    $r->salary_range,
                    $r->is_relevant ? 'Ya' : ($r->is_relevant === false ? 'Tidak' : '-'),
                    $r->feedback,
                    $r->submitted_at?->format('d M Y H:i'),
                ]);
            }
            fclose($fp);
        }, 'tracer-study-' . date('Ymd') . '.csv', $headers);
    }
}

<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\AccreditationDocument;
use App\Models\AccreditationInstrument;
use App\Models\AccreditationScore;
use App\Models\AccreditationStandard;
use App\Models\Communication\Document;
use App\Models\Communication\DocumentCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AccreditationController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    /* ==================== DASHBOARD ==================== */

    public function dashboard(): View
    {
        $schoolId = $this->schoolId();

        $standards = AccreditationStandard::with('instruments')->orderBy('code')->get();

        $standardProgress = [];
        $totalPredicted   = 0;
        $totalMax         = 0;

        foreach ($standards as $std) {
            $instrumentIds     = $std->instruments->pluck('id');
            $totalInstruments  = $instrumentIds->count();
            $scoredCount       = AccreditationScore::where('school_id', $schoolId)
                ->whereIn('accreditation_instrument_id', $instrumentIds)
                ->whereNotNull('self_score')
                ->count();
            $docCount          = AccreditationDocument::where('school_id', $schoolId)
                ->whereIn('accreditation_instrument_id', $instrumentIds)
                ->count();
            $approvedDocCount  = AccreditationDocument::where('school_id', $schoolId)
                ->whereIn('accreditation_instrument_id', $instrumentIds)
                ->where('status', 'approved')
                ->count();

            $avgScore = AccreditationScore::where('school_id', $schoolId)
                ->whereIn('accreditation_instrument_id', $instrumentIds)
                ->whereNotNull('self_score')
                ->avg('self_score') ?? 0;

            $maxScore   = $std->max_score;
            $weighted   = ($avgScore / max($maxScore, 1)) * $std->weight_percent;
            $totalPredicted += $weighted;
            $totalMax       += $std->weight_percent;

            $standardProgress[] = [
                'standard'      => $std,
                'total'         => $totalInstruments,
                'scored'        => $scoredCount,
                'documents'     => $docCount,
                'approved_docs' => $approvedDocCount,
                'avg_score'     => round($avgScore, 1),
                'percent'       => $totalInstruments > 0 ? round(($scoredCount / $totalInstruments) * 100) : 0,
            ];
        }

        $predictedScore = $totalMax > 0 ? round($totalPredicted, 1) : 0;
        $gradePrediction = $this->predictGrade($predictedScore);

        $recentDocs = AccreditationDocument::where('school_id', $schoolId)
            ->with(['instrument.standard', 'uploader'])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        return view('school-admin.academic.accreditation.dashboard', compact(
            'standardProgress', 'predictedScore', 'gradePrediction', 'recentDocs', 'standards'
        ));
    }

    /* ==================== INSTRUMENTS ==================== */

    public function instruments(Request $request): View
    {
        $schoolId   = $this->schoolId();
        $standardId = $request->standard_id;

        $standards = AccreditationStandard::withCount('instruments')->orderBy('code')->get();

        $instrumentsQuery = AccreditationInstrument::with('standard');

        if ($standardId) {
            $instrumentsQuery->where('accreditation_standard_id', $standardId);
        }

        $instruments = $instrumentsQuery->orderBy('number')->get();

        $scores = AccreditationScore::where('school_id', $schoolId)
            ->whereIn('accreditation_instrument_id', $instruments->pluck('id'))
            ->get()
            ->keyBy('accreditation_instrument_id');

        $documents = AccreditationDocument::where('school_id', $schoolId)
            ->whereIn('accreditation_instrument_id', $instruments->pluck('id'))
            ->get()
            ->groupBy('accreditation_instrument_id');

        return view('school-admin.academic.accreditation.instruments', compact(
            'standards', 'standardId', 'instruments', 'scores', 'documents'
        ));
    }

    public function saveScore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'accreditation_instrument_id' => 'required|exists:accreditation_instruments,id',
            'self_score'                  => 'required|integer|min:0|max:4',
            'notes'                       => 'nullable|string',
        ]);

        AccreditationScore::updateOrCreate(
            [
                'school_id'                  => $this->schoolId(),
                'accreditation_instrument_id' => $data['accreditation_instrument_id'],
            ],
            [
                'self_score' => $data['self_score'],
                'notes'      => $data['notes'] ?? null,
                'scored_by'  => auth()->id(),
                'scored_at'  => now(),
            ]
        );

        return back()->with('success', 'Nilai mandiri disimpan.');
    }

    /* ==================== DOCUMENTS ==================== */

    public function documents(Request $request): View
    {
        $schoolId    = $this->schoolId();
        $standardId  = $request->standard_id;
        $status      = $request->status;

        $standards = AccreditationStandard::orderBy('code')->get();

        $instrumentsQuery = AccreditationInstrument::with('standard');
        if ($standardId) {
            $instrumentsQuery->where('accreditation_standard_id', $standardId);
        }

        $allInstruments = $instrumentsQuery->orderBy('number')->get();

        $docsQuery = AccreditationDocument::where('school_id', $schoolId)
            ->with(['instrument.standard', 'uploader', 'reviewer']);

        if ($standardId) {
            $instIds = $allInstruments->pluck('id');
            $docsQuery->whereIn('accreditation_instrument_id', $instIds);
        }
        if ($status) {
            $docsQuery->where('status', $status);
        }

        $documents = $docsQuery->orderByDesc('created_at')->paginate(30);

        $unscoredInstruments = AccreditationInstrument::with('standard')
            ->whereDoesntHave('documents', fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('number')
            ->get()
            ->merge(
                $allInstruments->whereNotIn('id', AccreditationDocument::where('school_id', $schoolId)
                    ->pluck('accreditation_instrument_id'))
            )
            ->unique('id');

        return view('school-admin.academic.accreditation.documents', compact(
            'standards', 'standardId', 'status', 'documents', 'allInstruments', 'unscoredInstruments'
        ));
    }

    public function uploadDocument(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'accreditation_instrument_id' => 'required|exists:accreditation_instruments,id',
            'file'                        => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
            'description'                 => 'nullable|string',
        ]);

        $path = $request->file('file')->store('accreditation-docs/' . $this->schoolId(), 'public');
        $file = $request->file('file');

        $schoolId = $this->schoolId();

        $instrument = AccreditationInstrument::with('standard')->find($data['accreditation_instrument_id']);
        $instrumentLabel = $instrument ? "{$instrument->standard->code} - {$instrument->number} {$instrument->title}" : 'Akreditasi';

        $category = DocumentCategory::firstOrCreate(
            ['school_id' => $schoolId, 'name' => 'Akreditasi'],
            ['description' => 'Dokumen bukti akreditasi sekolah', 'access_level' => 'restricted']
        );

        $document = Document::create([
            'school_id'           => $schoolId,
            'document_category_id' => $category->id,
            'title'               => "Bukti Akreditasi: {$instrumentLabel}",
            'description'         => $data['description'] ?? "Dokumen pendukung instrumen {$instrumentLabel}",
            'file_path'           => $path,
            'file_type'           => $file->getClientOriginalExtension(),
            'file_size'           => $file->getSize(),
            'version'             => 1,
            'user_id'             => auth()->id(),
            'is_published'        => true,
            'published_at'        => now(),
        ]);

        AccreditationDocument::create([
            'school_id'                   => $schoolId,
            'accreditation_instrument_id' => $data['accreditation_instrument_id'],
            'file_path'                   => $path,
            'document_id'                 => $document->id,
            'description'                 => $data['description'] ?? null,
            'uploaded_by'                 => auth()->id(),
            'status'                      => 'pending',
        ]);

        return back()->with('success', 'Dokumen bukti diunggah.');
    }

    public function reviewDocument(Request $request, AccreditationDocument $document): RedirectResponse
    {
        $data = $request->validate([
            'status'         => 'required|in:approved,rejected',
            'reviewer_notes' => 'nullable|string',
        ]);

        $document->update([
            'status'         => $data['status'],
            'reviewer_notes' => $data['reviewer_notes'] ?? null,
            'reviewed_by'    => auth()->id(),
            'reviewed_at'    => now(),
        ]);

        return back()->with('success', 'Dokumen ' . ($data['status'] === 'approved' ? 'disetujui' : 'ditolak') . '.');
    }

    public function deleteDocument(AccreditationDocument $document): RedirectResponse
    {
        if ($document->document_id) {
            $linkedDoc = Document::find($document->document_id);
            if ($linkedDoc) {
                $linkedDoc->delete();
            }
        }

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();
        return back()->with('success', 'Dokumen dihapus.');
    }

    /* ==================== PRINT SUMMARY ==================== */

    public function printSummary(): View
    {
        $schoolId = $this->schoolId();
        $school   = \App\Models\School::find($schoolId);

        $standards = AccreditationStandard::with('instruments')->orderBy('code')->get();

        $rows = [];
        foreach ($standards as $std) {
            foreach ($std->instruments as $inst) {
                $score = AccreditationScore::where('school_id', $schoolId)
                    ->where('accreditation_instrument_id', $inst->id)
                    ->first();

                $rows[] = [
                    'standard'    => $std,
                    'instrument'  => $inst,
                    'self_score'  => $score?->self_score,
                    'actual_score'=> $score?->actual_score,
                    'notes'       => $score?->notes,
                ];
            }
        }

        $totalPredicted = 0;
        foreach ($standards as $std) {
            $instIds = $std->instruments->pluck('id');
            $avgScore = AccreditationScore::where('school_id', $schoolId)
                ->whereIn('accreditation_instrument_id', $instIds)
                ->whereNotNull('self_score')
                ->avg('self_score') ?? 0;
            $totalPredicted += ($avgScore / max($std->max_score, 1)) * $std->weight_percent;
        }

        $grade = $this->predictGrade(round($totalPredicted, 1));

        return view('school-admin.academic.accreditation.print-summary', compact(
            'school', 'standards', 'rows', 'totalPredicted', 'grade'
        ));
    }

    /* ==================== PRIVATE ==================== */

    private function predictGrade(float $score): array
    {
        if ($score >= 91) {
            return ['grade' => 'A', 'label' => 'Unggul', 'color' => '#16A34A'];
        }
        if ($score >= 81) {
            return ['grade' => 'A', 'label' => 'Baik Sekali', 'color' => '#22C55E'];
        }
        if ($score >= 71) {
            return ['grade' => 'B', 'label' => 'Baik', 'color' => '#2563EB'];
        }
        if ($score >= 61) {
            return ['grade' => 'C', 'label' => 'Cukup', 'color' => '#EAB308'];
        }
        return ['grade' => 'TT', 'label' => 'Tidak Terakreditasi', 'color' => '#DC2626'];
    }
}

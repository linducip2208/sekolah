<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassSection;
use App\Models\Academic\PkgAssessment;
use App\Models\Academic\PkgCompetency;
use App\Models\Academic\PkgObservation;
use App\Models\Academic\PkgScore;
use App\Models\Academic\Subject;
use App\Models\Academic\Staff;
use App\Models\User;
use App\Services\PkgService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PkgController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();
        $query = PkgAssessment::where('school_id', $schoolId)
            ->with(['teacher:id,name', 'assessor:id,name'])
            ->orderByDesc('assessment_date');

        if ($request->has('teacher') && $request->teacher) {
            $query->where('teacher_id', $request->teacher);
        }
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $assessments = $query->paginate(20)->appends($request->query());
        $teachers = Staff::where('school_id', $schoolId)->with('user:id,name')->get();

        // Summary per teacher
        $summaries = PkgAssessment::where('school_id', $schoolId)
            ->selectRaw('teacher_id, COUNT(*) as total, AVG(final_score) as avg_score')
            ->whereNotNull('final_score')
            ->groupBy('teacher_id')
            ->with('teacher:id,name')
            ->get();

        return view('school-admin.academic.pkg.index', compact('assessments', 'teachers', 'summaries'));
    }

    public function create(): View
    {
        $schoolId = $this->schoolId();
        $teachers = Staff::where('school_id', $schoolId)->with('user:id,name')->get();
        $academicYears = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $competencies = PkgCompetency::where('is_active', true)->orderBy('code')->get();

        return view('school-admin.academic.pkg.create', compact('teachers', 'academicYears', 'competencies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'teacher_id'       => 'required|exists:users,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'semester'         => 'required|in:1,2',
            'assessment_date'  => 'required|date',
            'type'             => 'required|in:self,peer,supervisor',
            'notes'            => 'nullable|string',
            'scores'           => 'required|array',
            'scores.*'         => 'nullable|numeric|min:0|max:100',
            'evidence_notes'   => 'nullable|array',
            'evidence_notes.*' => 'nullable|string',
            'observation_date'      => 'nullable|date',
            'observation_notes'     => 'nullable|string',
            'class_atmosphere'      => 'nullable|string',
            'student_engagement'    => 'nullable|string',
            'class_section_id'      => 'nullable|exists:class_sections,id',
            'subject_id'            => 'nullable|exists:subjects,id',
        ]);

        $assessment = PkgAssessment::create([
            'school_id'        => $this->schoolId(),
            'teacher_id'       => $data['teacher_id'],
            'assessor_id'      => auth()->id(),
            'academic_year_id' => $data['academic_year_id'] ?? null,
            'semester'         => $data['semester'],
            'assessment_date'  => $data['assessment_date'],
            'type'             => $data['type'],
            'status'           => 'draft',
            'notes'            => $data['notes'] ?? null,
        ]);

        $competencies = PkgCompetency::where('is_active', true)->orderBy('code')->get();
        $totalScore = 0;
        $totalWeight = 0;

        foreach ($competencies as $comp) {
            $score = $data['scores'][$comp->id] ?? null;
            if ($score !== null) {
                PkgScore::create([
                    'pkg_assessment_id' => $assessment->id,
                    'pkg_competency_id' => $comp->id,
                    'score'             => max(0, min(100, (float) $score)),
                    'evidence_notes'    => $data['evidence_notes'][$comp->id] ?? null,
                ]);
                $totalScore += (float) $score * $comp->weight;
                $totalWeight += $comp->weight;
            }
        }

        $finalScore = $totalWeight > 0 ? $totalScore / $totalWeight : 0;
        $assessment->update([
            'final_score'    => round($finalScore, 2),
            'recommendation' => app(PkgService::class)->getRecommendation($finalScore),
            'status'         => 'submitted',
        ]);

        if (!empty($data['observation_date'])) {
            PkgObservation::create([
                'pkg_assessment_id'  => $assessment->id,
                'class_section_id'   => $data['class_section_id'] ?? null,
                'subject_id'         => $data['subject_id'] ?? null,
                'observation_date'   => $data['observation_date'],
                'observation_notes'  => $data['observation_notes'] ?? null,
                'class_atmosphere'   => $data['class_atmosphere'] ?? null,
                'student_engagement' => $data['student_engagement'] ?? null,
            ]);
        }

        return redirect()->route('admin.pkg.index')->with('success', 'PKG berhasil disimpan.');
    }

    public function detail(PkgAssessment $assessment): View
    {
        $this->authorizeOwn($assessment);

        $assessment->load([
            'teacher:id,name', 'assessor:id,name', 'academicYear',
            'scores.competency', 'observations.classSection.classRoom',
            'observations.subject',
        ]);

        $competencies = PkgCompetency::where('is_active', true)->orderBy('code')->get();
        $scoreMap = $assessment->scores->pluck('score', 'pkg_competency_id');

        return view('school-admin.academic.pkg.detail', compact('assessment', 'competencies', 'scoreMap'));
    }

    public function verify(PkgAssessment $assessment): RedirectResponse
    {
        $this->authorizeOwn($assessment);
        $assessment->update(['status' => 'verified']);
        return back()->with('success', 'PKG diverifikasi.');
    }

    public function destroy(PkgAssessment $assessment): RedirectResponse
    {
        $this->authorizeOwn($assessment);
        $assessment->delete();
        return redirect()->route('admin.pkg.index')->with('success', 'PKG dihapus.');
    }

    public function exportPdf(PkgAssessment $assessment): \Illuminate\Http\Response
    {
        $this->authorizeOwn($assessment);
        $assessment->load([
            'teacher:id,name', 'assessor:id,name', 'academicYear',
            'scores.competency', 'observations.classSection.classRoom',
            'observations.subject',
        ]);

        $competencies = PkgCompetency::where('is_active', true)->orderBy('code')->get();
        $scoreMap = $assessment->scores->pluck('score', 'pkg_competency_id');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.pkg-report', compact('assessment', 'competencies', 'scoreMap'));
        return $pdf->download('PKG-' . $assessment->teacher?->name . '-' . $assessment->assessment_date->format('Ymd') . '.pdf');
    }
}

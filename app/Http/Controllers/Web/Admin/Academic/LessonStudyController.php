<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassSection;
use App\Models\Academic\LessonStudy;
use App\Models\Academic\LessonStudyMember;
use App\Models\Academic\LessonStudyObservation;
use App\Models\Academic\LessonStudyReflection;
use App\Models\Academic\Subject;
use App\Models\School;
use App\Models\User;
use App\Services\LessonStudyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonStudyController extends Controller
{
    private LessonStudyService $lessonStudyService;

    public function __construct(LessonStudyService $lessonStudyService)
    {
        $this->lessonStudyService = $lessonStudyService;
    }

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): View
    {
        $schoolId = $this->schoolId();

        $studies = LessonStudy::where('school_id', $schoolId)
            ->with(['leadTeacher', 'subject', 'classSection.classRoom', 'classSection.section'])
            ->withCount(['members', 'observations', 'reflections'])
            ->orderByDesc('created_at')
            ->paginate(12);

        $stats = $this->lessonStudyService->getTeacherParticipationStats($schoolId);

        return view('school-admin.academic.lesson-study.index', compact('studies', 'stats'));
    }

    public function create(): View
    {
        $schoolId = $this->schoolId();

        $subjects = Subject::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $classSections = ClassSection::where('school_id', $schoolId)
            ->with(['classRoom', 'section'])
            ->get();

        $teachers = User::where('school_id', $schoolId)
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('school-admin.academic.lesson-study.create', compact('subjects', 'classSections', 'teachers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'subject_id'       => 'nullable|exists:subjects,id',
            'class_section_id' => 'nullable|exists:class_sections,id',
            'topic'            => 'nullable|string|max:255',
            'lead_teacher_id'  => 'required|exists:users,id',
            'member_ids'       => 'nullable|array',
            'member_ids.*'     => 'exists:users,id',
            'plan_date'        => 'nullable|date',
            'teach_date'       => 'nullable|date',
            'description'      => 'nullable|string',
            'plan_notes'       => 'nullable|string',
        ]);

        $schoolId = $this->schoolId();

        $study = LessonStudy::create([
            'school_id'       => $schoolId,
            'title'           => $data['title'],
            'subject_id'      => $data['subject_id'] ?? null,
            'class_section_id'=> $data['class_section_id'] ?? null,
            'topic'           => $data['topic'] ?? null,
            'lead_teacher_id' => $data['lead_teacher_id'],
            'created_by'      => auth()->id(),
            'phase'           => 'plan',
            'status'          => 'draft',
            'plan_date'       => $data['plan_date'] ?? now()->toDateString(),
            'teach_date'      => $data['teach_date'] ?? null,
            'description'     => $data['description'] ?? null,
            'plan_notes'      => $data['plan_notes'] ?? null,
        ]);

        LessonStudyMember::create([
            'lesson_study_id' => $study->id,
            'staff_id'        => $data['lead_teacher_id'],
            'role'            => 'lead',
        ]);

        if (!empty($data['member_ids'])) {
            foreach ($data['member_ids'] as $memberId) {
                if ($memberId != $data['lead_teacher_id']) {
                    LessonStudyMember::firstOrCreate(
                        ['lesson_study_id' => $study->id, 'staff_id' => $memberId],
                        ['role' => 'observer']
                    );
                }
            }
        }

        return redirect()->route('admin.lesson-study.index')
            ->with('success', 'Lesson Study "' . $study->title . '" berhasil dibuat.');
    }

    public function show(LessonStudy $lessonStudy): View
    {
        $this->authorizeOwn($lessonStudy);
        $lessonStudy->load(['leadTeacher', 'subject', 'classSection.classRoom', 'classSection.section', 'members.staff', 'observations.observer', 'reflections.staff']);

        $observationSummary = $this->lessonStudyService->compileObservations($lessonStudy);
        $reflectionSummary = $this->lessonStudyService->compileReflections($lessonStudy);
        $recommendations = $this->lessonStudyService->generateRecommendations($lessonStudy);

        return view('school-admin.academic.lesson-study.index', [
            'studies'             => collect([$lessonStudy]),
            'stats'               => $this->lessonStudyService->getTeacherParticipationStats($this->schoolId()),
            'detailStudy'         => $lessonStudy,
            'observationSummary'  => $observationSummary,
            'reflectionSummary'   => $reflectionSummary,
            'recommendations'     => $recommendations,
            'showDetail'          => true,
        ]);
    }

    public function edit(LessonStudy $lessonStudy): View
    {
        $this->authorizeOwn($lessonStudy);
        $schoolId = $this->schoolId();

        $subjects = Subject::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $classSections = ClassSection::where('school_id', $schoolId)
            ->with(['classRoom', 'section'])
            ->get();
        $teachers = User::where('school_id', $schoolId)
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))
            ->orderBy('name')
            ->get(['id', 'name']);

        $lessonStudy->load('members');

        return view('school-admin.academic.lesson-study.create', compact(
            'lessonStudy', 'subjects', 'classSections', 'teachers'
        ));
    }

    public function update(Request $request, LessonStudy $lessonStudy): RedirectResponse
    {
        $this->authorizeOwn($lessonStudy);

        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'subject_id'       => 'nullable|exists:subjects,id',
            'class_section_id' => 'nullable|exists:class_sections,id',
            'topic'            => 'nullable|string|max:255',
            'lead_teacher_id'  => 'required|exists:users,id',
            'plan_date'        => 'nullable|date',
            'teach_date'       => 'nullable|date',
            'description'      => 'nullable|string',
            'plan_notes'       => 'nullable|string',
        ]);

        $lessonStudy->update($data);

        // Update lead teacher role
        LessonStudyMember::updateOrCreate(
            ['lesson_study_id' => $lessonStudy->id, 'staff_id' => $data['lead_teacher_id']],
            ['role' => 'lead']
        );

        return redirect()->route('admin.lesson-study.index')
            ->with('success', 'Lesson Study diperbarui.');
    }

    public function destroy(LessonStudy $lessonStudy): RedirectResponse
    {
        $this->authorizeOwn($lessonStudy);
        $lessonStudy->delete();
        return back()->with('success', 'Lesson Study dihapus.');
    }

    public function advancePhase(LessonStudy $lessonStudy): RedirectResponse
    {
        $this->authorizeOwn($lessonStudy);
        $this->lessonStudyService->advancePhase($lessonStudy);

        $phaseLabels = ['plan' => 'Plan (Perencanaan)', 'do' => 'Do (Pelaksanaan)', 'see' => 'See (Refleksi)'];
        $label = $phaseLabels[$lessonStudy->fresh()->phase] ?? $lessonStudy->phase;

        return back()->with('success', "Lesson Study lanjut ke fase: {$label}.");
    }

    public function observe(LessonStudy $lessonStudy): View
    {
        $this->authorizeOwn($lessonStudy);
        $lessonStudy->load(['leadTeacher', 'members.staff']);

        $observationTypes = [
            'student_engagement' => 'Keterlibatan Siswa',
            'teaching_method'    => 'Metode Pengajaran',
            'class_management'   => 'Manajemen Kelas',
            'material_clarity'   => 'Kejelasan Materi',
        ];

        $existingObservations = LessonStudyObservation::where('lesson_study_id', $lessonStudy->id)
            ->where('observer_id', auth()->id())
            ->get()
            ->keyBy('observation_type');

        return view('school-admin.academic.lesson-study.observe', compact(
            'lessonStudy', 'observationTypes', 'existingObservations'
        ));
    }

    public function storeObservation(Request $request, LessonStudy $lessonStudy): RedirectResponse
    {
        $this->authorizeOwn($lessonStudy);

        $data = $request->validate([
            'observation_type' => 'required|in:student_engagement,teaching_method,class_management,material_clarity',
            'notes'            => 'required|string',
            'rating'           => 'nullable|integer|min:1|max:5',
        ]);

        LessonStudyObservation::updateOrCreate(
            [
                'lesson_study_id'  => $lessonStudy->id,
                'observer_id'      => auth()->id(),
                'observation_type' => $data['observation_type'],
            ],
            [
                'notes'       => $data['notes'],
                'rating'      => $data['rating'] ?? null,
                'observed_at' => now(),
            ]
        );

        return back()->with('success', 'Observasi disimpan.');
    }

    public function reflect(LessonStudy $lessonStudy): View
    {
        $this->authorizeOwn($lessonStudy);
        $lessonStudy->load(['members.staff']);

        $existingReflection = LessonStudyReflection::where('lesson_study_id', $lessonStudy->id)
            ->where('staff_id', auth()->id())
            ->first();

        return view('school-admin.academic.lesson-study.reflect', compact('lessonStudy', 'existingReflection'));
    }

    public function storeReflection(Request $request, LessonStudy $lessonStudy): RedirectResponse
    {
        $this->authorizeOwn($lessonStudy);

        $data = $request->validate([
            'reflection_text'    => 'required|string',
            'strength_points'    => 'nullable|string',
            'improvement_points' => 'nullable|string',
            'action_plan'        => 'nullable|string',
        ]);

        LessonStudyReflection::updateOrCreate(
            ['lesson_study_id' => $lessonStudy->id, 'staff_id' => auth()->id()],
            $data
        );

        if ($lessonStudy->phase === 'do') {
            $lessonStudy->update(['phase' => 'see', 'status' => 'reflected', 'reflect_date' => now()->toDateString()]);
        }

        return redirect()->route('admin.lesson-study.index')
            ->with('success', 'Refleksi berhasil disimpan.');
    }

    public function reportPdf(LessonStudy $lessonStudy): \Illuminate\Http\Response
    {
        $this->authorizeOwn($lessonStudy);
        $lessonStudy->load(['leadTeacher', 'subject', 'classSection.classRoom', 'classSection.section', 'members.staff', 'observations.observer', 'reflections.staff']);

        $school = School::find($this->schoolId());
        $observationSummary = $this->lessonStudyService->compileObservations($lessonStudy);
        $reflectionSummary = $this->lessonStudyService->compileReflections($lessonStudy);
        $recommendations = $this->lessonStudyService->generateRecommendations($lessonStudy);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.lesson-study-report', compact(
            'lessonStudy', 'school', 'observationSummary', 'reflectionSummary', 'recommendations'
        ));

        return $pdf->download("lesson-study-{$lessonStudy->id}.pdf");
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }
}

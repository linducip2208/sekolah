<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Subject;
use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Models\LessonPlan\LessonPlan;
use App\Models\User;
use App\Services\AI\LessonPlanGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonPlanController extends Controller
{
    public function __construct(protected LessonPlanGeneratorService $generator) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): View
    {
        $schoolId = $this->schoolId();

        return view('school-admin.lesson-plan.index', [
            'plans' => LessonPlan::where('school_id', $schoolId)
                ->with(['subject', 'classSection.classRoom', 'teacher:id,name'])
                ->orderByDesc('created_at')->paginate(20),
            'subjects'      => Subject::where('school_id', $schoolId)->get(),
            'classSections' => ClassSection::where('school_id', $schoolId)->with(['classRoom', 'section'])->get(),
            'teachers'      => User::where('school_id', $schoolId)
                ->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))->get(['id', 'name']),
            'providers'     => AiProvider::where('school_id', $schoolId)->where('is_active', true)->orderBy('priority')->orderBy('name')->get(),
            'aiModels'      => AiModel::where('school_id', $schoolId)->where('is_active', true)->with('provider')->orderBy('priority')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolId = $this->schoolId();

        $data = $request->validate([
            'title'                => 'required|string|max:255',
            'subject_id'           => 'required|exists:subjects,id',
            'class_section_id'     => 'required|exists:class_sections,id',
            'teacher_id'           => 'required|exists:users,id',
            'lesson_date'          => 'nullable|date',
            'duration_minutes'     => 'nullable|integer|min:15|max:300',
            'learning_objectives'  => 'nullable|string',
            'material_summary'     => 'nullable|string',
        ]);
        $data['school_id'] = $schoolId;
        $data['status']    = 'draft';
        LessonPlan::create($data);
        return back()->with('success', 'Lesson plan ditambahkan.');
    }

    public function destroy(LessonPlan $plan): RedirectResponse
    {
        abort_unless($plan->school_id === $this->schoolId(), 403);
        $plan->delete();
        return back()->with('success', 'Lesson plan dihapus.');
    }

    public function generate(Request $request): JsonResponse
    {
        $schoolId = $this->schoolId();
        $userId   = auth()->id();

        $data = $request->validate([
            'subject_name'     => 'required|string|max:255',
            'class_level'      => 'required|string|max:50',
            'title'            => 'required|string|max:255',
            'meeting_number'   => 'nullable|integer|min:1|max:100',
            'curriculum_type'  => 'required|in:Merdeka,K13,Cambridge,IB',
            'ai_provider_id'   => 'nullable|exists:ai_providers,id',
            'ai_model_id'      => 'nullable|exists:ai_models,id',
        ]);

        try {
            $result = $this->generator->generate(
                $schoolId, $userId,
                $data['subject_name'],
                $data['class_level'],
                $data['title'],
                (int) ($data['meeting_number'] ?? 1),
                $data['curriculum_type'],
                $data['ai_provider_id'] ? (int) $data['ai_provider_id'] : null,
                $data['ai_model_id'] ? (int) $data['ai_model_id'] : null,
            );

            return response()->json([
                'success' => true,
                'parsed'  => $result['parsed'],
                'raw_text'=> $result['raw_text'],
                'tokens_used'       => $result['tokens_used'],
                'processing_time_ms'=> $result['processing_time_ms'],
                'ai_provider_id'    => $result['ai_provider_id'],
                'ai_model_id'       => $result['ai_model_id'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function generateAndSave(Request $request): RedirectResponse
    {
        $schoolId = $this->schoolId();
        $userId   = auth()->id();

        $data = $request->validate([
            'subject_id'        => 'nullable|exists:subjects,id',
            'class_section_id'  => 'nullable|exists:class_sections,id',
            'teacher_id'        => 'nullable|exists:users,id',
            'title'             => 'required|string|max:255',
            'subject_name'      => 'required|string|max:255',
            'class_level'       => 'required|string|max:50',
            'meeting_number'    => 'nullable|integer|min:1|max:100',
            'curriculum_type'   => 'required|in:Merdeka,K13,Cambridge,IB',
            'lesson_date'       => 'nullable|date',
            'duration_minutes'  => 'nullable|integer|min:15|max:300',
            'ai_provider_id'    => 'nullable|exists:ai_providers,id',
            'ai_model_id'       => 'nullable|exists:ai_models,id',
        ]);

        try {
            $lessonPlan = $this->generator->generateAndSave(
                $schoolId, $userId,
                [
                    'subject_id'       => $data['subject_id'] ?? null,
                    'class_section_id'  => $data['class_section_id'] ?? null,
                    'teacher_id'        => $data['teacher_id'] ?? null,
                    'title'             => $data['title'],
                    'subject_name'      => $data['subject_name'],
                    'class_level'       => $data['class_level'],
                    'meeting_number'    => (int) ($data['meeting_number'] ?? 1),
                    'curriculum_type'   => $data['curriculum_type'],
                    'lesson_date'       => $data['lesson_date'] ?? null,
                    'duration_minutes'  => $data['duration_minutes'] ?? 90,
                ],
                $data['ai_provider_id'] ? (int) $data['ai_provider_id'] : null,
                $data['ai_model_id'] ? (int) $data['ai_model_id'] : null,
            );

            return back()->with('success', 'RPP berhasil digenerate oleh AI dan disimpan.');
        } catch (\Throwable $e) {
            return back()->withErrors('Gagal generate RPP: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers\Api\LessonPlan;

use App\Http\Controllers\Controller;
use App\Models\LessonPlan\LessonPlan;
use App\Services\LessonPlan\LessonPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonPlanController extends Controller
{
    public function __construct(private LessonPlanService $service) {}

    public function index(Request $request): JsonResponse
    {
        $plans = LessonPlan::where('school_id', $request->user()->school_id)
            ->when($request->input('teacher_id'), fn ($q, $tid) => $q->where('teacher_id', $tid))
            ->when($request->input('class_section_id'), fn ($q, $cid) => $q->where('class_section_id', $cid))
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('lesson_date')
            ->paginate(50);

        return response()->json($plans);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $plan = LessonPlan::where('school_id', $request->user()->school_id)
            ->with('attachments')
            ->findOrFail($id);

        return response()->json($plan);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'class_section_id'    => 'required|integer',
            'subject_id'          => 'required|integer',
            'semester_id'         => 'nullable|integer',
            'title'               => 'required|string|max:200',
            'lesson_date'         => 'required|date',
            'duration_minutes'    => 'required|integer|min:15|max:480',
            'learning_objectives' => 'required|array',
            'material_summary'    => 'required|string',
            'activities'          => 'required|array',
            'assessment_methods'  => 'nullable|array',
            'resources'           => 'nullable|array',
            'curriculum_type'     => 'nullable|in:merdeka,k13,cambridge,ib',
        ]);

        return response()->json(
            $this->service->create($request->user()->school_id, $request->user()->id, $data),
            201,
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $plan = LessonPlan::where('school_id', $request->user()->school_id)
            ->where('teacher_id', $request->user()->id)
            ->findOrFail($id);

        $plan->update($request->only([
            'title', 'lesson_date', 'duration_minutes', 'learning_objectives',
            'material_summary', 'activities', 'assessment_methods', 'resources',
            'curriculum_type',
        ]));

        return response()->json($plan->fresh());
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        $plan = LessonPlan::where('school_id', $request->user()->school_id)
            ->where('teacher_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($this->service->submit($plan));
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $plan = LessonPlan::where('school_id', $request->user()->school_id)->findOrFail($id);
        return response()->json($this->service->approve(
            $plan, $request->user()->id, $request->input('feedback'),
        ));
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate(['feedback' => 'required|string|max:2000']);
        $plan = LessonPlan::where('school_id', $request->user()->school_id)->findOrFail($id);
        return response()->json($this->service->reject(
            $plan, $request->user()->id, $request->input('feedback'),
        ));
    }

    public function markExecuted(Request $request, int $id): JsonResponse
    {
        $plan = LessonPlan::where('school_id', $request->user()->school_id)
            ->where('teacher_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($this->service->markExecuted($plan, $request->input('note')));
    }

    public function coverage(Request $request, int $semesterId): JsonResponse
    {
        return response()->json($this->service->coverageReport(
            $request->user()->school_id, $semesterId,
        ));
    }
}

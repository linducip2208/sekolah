<?php

namespace App\Http\Controllers\Api\Curriculum;

use App\Http\Controllers\Controller;
use App\Models\Curriculum\CompetencyAssessment;
use App\Models\Curriculum\CurriculumCompetency;
use App\Models\Curriculum\CurriculumFramework;
use App\Services\Curriculum\CurriculumService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurriculumController extends Controller
{
    public function __construct(private CurriculumService $service) {}

    public function frameworks(Request $request): JsonResponse
    {
        return response()->json([
            'data' => CurriculumFramework::where('school_id', $request->user()->school_id)->get(),
        ]);
    }

    public function storeFramework(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:200',
            'type'      => 'required|in:merdeka,k13,cambridge,ib,custom',
            'config'    => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);
        $data['school_id'] = $request->user()->school_id;
        return response()->json(CurriculumFramework::create($data), 201);
    }

    public function competencies(Request $request): JsonResponse
    {
        $items = CurriculumCompetency::where('school_id', $request->user()->school_id)
            ->when($request->input('framework_id'), fn ($q, $fid) => $q->where('curriculum_framework_id', $fid))
            ->when($request->input('subject_id'), fn ($q, $sid) => $q->where('subject_id', $sid))
            ->when($request->input('class_room_id'), fn ($q, $cid) => $q->where('class_room_id', $cid))
            ->orderBy('code')->get();

        return response()->json(['data' => $items]);
    }

    public function storeCompetency(Request $request): JsonResponse
    {
        $data = $request->validate([
            'curriculum_framework_id' => 'required|integer',
            'subject_id'              => 'required|integer',
            'class_room_id'           => 'required|integer',
            'code'                    => 'required|string|max:30',
            'description'             => 'required|string',
            'level_type'              => 'required|in:cp,tp,ki,kd,outcome',
            'parent_id'               => 'nullable|integer',
            'indicators'              => 'nullable|array',
        ]);
        $data['school_id'] = $request->user()->school_id;
        return response()->json(CurriculumCompetency::create($data), 201);
    }

    public function recordAssessment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id'                => 'required|integer',
            'curriculum_competency_id'  => 'required|integer',
            'mastery_level'             => 'required|in:emerging,developing,meets,exceeds',
            'assessed_at'               => 'nullable|date',
            'evidence'                  => 'nullable|string',
        ]);
        $data['school_id']   = $request->user()->school_id;
        $data['assessed_by'] = $request->user()->id;
        $data['assessed_at'] = $data['assessed_at'] ?? today();
        return response()->json(CompetencyAssessment::create($data), 201);
    }

    public function coverage(Request $request): JsonResponse
    {
        $data = $request->validate([
            'semester_id'   => 'required|integer',
            'subject_id'    => 'required|integer',
            'class_room_id' => 'required|integer',
        ]);

        return response()->json($this->service->coverageBySemester(
            $request->user()->school_id,
            $data['semester_id'],
            $data['subject_id'],
            $data['class_room_id'],
        ));
    }
}

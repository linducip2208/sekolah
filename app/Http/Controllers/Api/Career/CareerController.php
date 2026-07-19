<?php

namespace App\Http\Controllers\Api\Career;

use App\Http\Controllers\Controller;
use App\Models\Career\CareerAssessment;
use App\Models\Career\InternshipPlacement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CareerController extends Controller
{
    public function recordAssessment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => 'required|integer',
            'test_type'  => 'required|in:holland_riasec,mbti,cliftonstrengths,custom',
            'responses'  => 'required|array',
            'result'     => 'required|array',
        ]);
        $data['school_id'] = $request->user()->school_id;
        $data['taken_at']  = today();
        return response()->json(CareerAssessment::create($data), 201);
    }

    public function studentAssessments(Request $request, int $studentId): JsonResponse
    {
        return response()->json([
            'data' => CareerAssessment::where('school_id', $request->user()->school_id)
                ->where('student_id', $studentId)
                ->orderByDesc('taken_at')->get(),
        ]);
    }

    public function internships(Request $request): JsonResponse
    {
        $items = InternshipPlacement::where('school_id', $request->user()->school_id)
            ->when($request->input('student_id'), fn ($q, $sid) => $q->where('student_id', $sid))
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('start_date')->paginate(50);

        return response()->json($items);
    }

    public function storeInternship(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id'   => 'required|integer',
            'company_name' => 'required|string|max:200',
            'position'     => 'required|string|max:200',
            'mentor_name'  => 'nullable|string|max:200',
            'mentor_phone' => 'nullable|string|max:30',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'status'       => 'nullable|in:planned,active,completed,dropped',
        ]);
        $data['school_id'] = $request->user()->school_id;
        $data['status']    = $data['status'] ?? 'planned';
        return response()->json(InternshipPlacement::create($data), 201);
    }

    public function logDailyActivity(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'date'     => 'required|date',
            'activity' => 'required|string',
        ]);

        $placement = InternshipPlacement::where('school_id', $request->user()->school_id)
            ->findOrFail($id);

        $logs = $placement->daily_logs ?? [];
        $logs[] = [
            'date'     => $request->input('date'),
            'activity' => $request->input('activity'),
        ];
        $placement->update(['daily_logs' => $logs]);

        return response()->json($placement);
    }
}

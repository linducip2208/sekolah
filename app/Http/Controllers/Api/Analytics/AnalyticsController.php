<?php

namespace App\Http\Controllers\Api\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Analytics\StudentRiskScore;
use App\Services\Analytics\RiskScoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private RiskScoreService $service) {}

    public function compute(Request $request): JsonResponse
    {
        $count = $this->service->computeForSchool($request->user()->school_id);
        return response()->json(['count' => $count]);
    }

    public function studentRiskScore(Request $request, int $studentId): JsonResponse
    {
        $score = StudentRiskScore::where('school_id', $request->user()->school_id)
            ->where('student_id', $studentId)
            ->orderByDesc('snapshot_date')
            ->first();

        if (!$score) {
            $score = $this->service->computeForStudent($request->user()->school_id, $studentId);
        }

        return response()->json($score);
    }

    public function topAtRisk(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->service->topAtRisk(
                $request->user()->school_id,
                (int) $request->input('limit', 20),
            ),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\Achievement;

use App\Http\Controllers\Controller;
use App\Models\Achievement\AchievementCategory;
use App\Models\Achievement\DigitalBadge;
use App\Models\Achievement\StudentAchievement;
use App\Models\Achievement\StudentBadge;
use App\Services\Achievement\AchievementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    public function __construct(private AchievementService $service) {}

    public function categories(Request $request): JsonResponse
    {
        return response()->json([
            'data' => AchievementCategory::where('school_id', $request->user()->school_id)->get(),
        ]);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'   => 'required|string|max:200',
            'scope'  => 'required|in:internal,district,province,national,international',
            'points' => 'nullable|integer|min:0',
        ]);
        $data['school_id'] = $request->user()->school_id;
        return response()->json(AchievementCategory::create($data), 201);
    }

    public function studentAchievements(Request $request, int $studentId): JsonResponse
    {
        return response()->json([
            'data' => StudentAchievement::where('school_id', $request->user()->school_id)
                ->where('student_id', $studentId)
                ->orderByDesc('achieved_at')->get(),
        ]);
    }

    public function recordAchievement(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id'              => 'required|integer',
            'achievement_category_id' => 'required|integer',
            'title'                   => 'required|string|max:200',
            'achieved_at'             => 'required|date',
            'issuer'                  => 'nullable|string|max:200',
            'certificate_path'        => 'nullable|string|max:500',
            'description'             => 'nullable|string',
        ]);

        return response()->json($this->service->recordAchievement(
            $request->user()->school_id, $data['student_id'], $data,
        ), 201);
    }

    public function verifyAchievement(Request $request, int $id): JsonResponse
    {
        $a = StudentAchievement::where('school_id', $request->user()->school_id)->findOrFail($id);
        return response()->json($this->service->verify($a, $request->user()->id));
    }

    public function badges(Request $request): JsonResponse
    {
        return response()->json([
            'data' => DigitalBadge::where('school_id', $request->user()->school_id)->get(),
        ]);
    }

    public function studentBadges(Request $request, int $studentId): JsonResponse
    {
        return response()->json([
            'data' => StudentBadge::where('school_id', $request->user()->school_id)
                ->where('student_id', $studentId)
                ->with('digitalBadge')
                ->get(),
        ]);
    }

    public function leaderboard(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->service->studentLeaderboard(
                $request->user()->school_id,
                (int) $request->input('limit', 20),
            ),
        ]);
    }
}

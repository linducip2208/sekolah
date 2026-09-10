<?php

namespace App\Http\Controllers\Api\Discipline;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Discipline\DisciplineCategory;
use App\Models\Discipline\DisciplineRecord;
use App\Services\Discipline\DisciplineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisciplineController extends Controller
{
    public function __construct(private DisciplineService $service) {}

    public function categories(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'discipline.view');

        return response()->json([
            'data' => DisciplineCategory::where('school_id', $request->user()->school_id)
                ->orderBy('type')->orderBy('name')->get(),
        ]);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'discipline.manage');
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'type' => 'required|in:violation,achievement',
            'point_value' => 'required|integer',
            'description' => 'nullable|string|max:1000',
            'auto_sanction' => 'nullable|boolean',
            'sanction_thresholds' => 'nullable|array',
        ]);
        $data['school_id'] = $request->user()->school_id;

        return response()->json(DisciplineCategory::create($data), 201);
    }

    public function records(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'discipline.view');
        $records = DisciplineRecord::where('school_id', $request->user()->school_id)
            ->when($request->input('student_id'), fn ($q, $sid) => $q->where('student_id', $sid))
            ->orderByDesc('incident_date')
            ->paginate(50);

        return response()->json($records);
    }

    public function storeRecord(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'discipline.manage');
        $data = $request->validate([
            'student_id' => 'required|integer',
            'discipline_category_id' => 'required|integer',
            'incident_date' => 'nullable|date',
            'description' => 'required|string',
            'evidence_files' => 'nullable|array',
        ]);

        $record = $this->service->record(
            $request->user()->school_id,
            $data['student_id'],
            $data['discipline_category_id'],
            $request->user()->id,
            $data,
        );

        return response()->json($record, 201);
    }

    public function summary(Request $request, int $studentId): JsonResponse
    {
        $this->requirePermission($request, 'discipline.view');
        Student::withoutGlobalScopes()->where('school_id', $request->user()->school_id)->findOrFail($studentId);

        return response()->json([
            'student_id' => $studentId,
            'total_points' => $this->service->totalPointsFor($request->user()->school_id, $studentId),
            'records' => DisciplineRecord::where('school_id', $request->user()->school_id)
                ->where('student_id', $studentId)
                ->with('reporter:id,name')
                ->orderByDesc('incident_date')
                ->get(),
        ]);
    }

    public function leaderboard(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'discipline.view');

        return response()->json([
            'data' => $this->service->leaderboardByPoints(
                $request->user()->school_id,
                (int) $request->input('limit', 20),
            ),
        ]);
    }

    private function requirePermission(Request $request, string $permission): void
    {
        abort_unless($request->user()->hasRole('super_admin') || $request->user()->can($permission), 403, 'Tidak memiliki izin disiplin.');
    }
}

<?php

namespace App\Http\Controllers\Api\Counseling;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Counseling\BullyingReport;
use App\Models\Counseling\CounselingSession;
use App\Models\User;
use App\Services\Counseling\CounselingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CounselingController extends Controller
{
    public function __construct(private CounselingService $service) {}

    public function sessions(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'counseling.view');
        $sessions = CounselingSession::where('school_id', $request->user()->school_id)
            ->when($request->input('student_id'), fn ($q, $sid) => $q->where('student_id', $sid))
            ->when($request->input('counselor_id'), fn ($q, $cid) => $q->where('counselor_id', $cid))
            ->orderByDesc('scheduled_at')
            ->paginate(50);

        return response()->json($sessions);
    }

    public function scheduleSession(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'counseling.manage');
        $data = $request->validate([
            'student_id' => 'required|integer',
            'counselor_id' => 'required|integer',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:15|max:240',
            'type' => 'required|in:academic,behavior,mental_health,career,family,social',
        ]);

        $schoolId = (int) $request->user()->school_id;
        $this->studentForSchool($schoolId, (int) $data['student_id']);
        $this->userForSchool($schoolId, (int) $data['counselor_id']);

        return response()->json(
            $this->service->scheduleSession($schoolId, $data),
            201,
        );
    }

    public function completeSession(Request $request, int $id): JsonResponse
    {
        $this->requirePermission($request, 'counseling.manage');
        $data = $request->validate([
            'notes' => 'nullable|string|max:5000',
            'refer_external' => 'nullable|boolean',
            'referred_to' => 'nullable|string|max:200',
        ]);

        $session = CounselingSession::where('school_id', $request->user()->school_id)
            ->findOrFail($id);

        return response()->json($this->service->completeSession(
            $session,
            $data['notes'] ?? null,
            (bool) ($data['refer_external'] ?? false),
            $data['referred_to'] ?? null,
        ));
    }

    public function bullyingReports(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'counseling.view');
        $reports = BullyingReport::where('school_id', $request->user()->school_id)
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json($reports);
    }

    public function reportBullying(Request $request): JsonResponse
    {
        $data = $request->validate([
            'anonymous' => 'nullable|boolean',
            'victims_described' => 'nullable|array',
            'perpetrators_described' => 'nullable|array',
            'type' => 'required|in:verbal,physical,cyber,social,other',
            'incident_date' => 'nullable|date',
            'location' => 'nullable|string|max:200',
            'description' => 'required|string',
            'evidence_files' => 'nullable|array',
        ]);

        $schoolId = (int) $request->user()->school_id;
        $report = $this->service->reportBullying($schoolId, $request->user()?->id, $data);

        return response()->json($report, 201);
    }

    public function assignBullying(Request $request, int $id): JsonResponse
    {
        $this->requirePermission($request, 'counseling.manage');
        $request->validate(['user_id' => 'required|integer']);
        $schoolId = (int) $request->user()->school_id;
        $this->userForSchool($schoolId, (int) $request->input('user_id'));
        $report = BullyingReport::where('school_id', $schoolId)->findOrFail($id);

        return response()->json($this->service->assignBullyingReport($report, $request->input('user_id')));
    }

    public function closeBullying(Request $request, int $id): JsonResponse
    {
        $this->requirePermission($request, 'counseling.manage');
        $data = $request->validate([
            'status' => 'required|in:action_taken,closed,unfounded',
            'action_summary' => 'nullable|string|max:5000',
        ]);
        $report = BullyingReport::where('school_id', $request->user()->school_id)->findOrFail($id);

        return response()->json($this->service->closeBullyingReport($report, $data['status'], $data['action_summary'] ?? null));
    }

    public function checkin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => 'required|integer',
            'mood_score' => 'required|integer|min:1|max:10',
            'feeling_tags' => 'nullable|array',
            'note' => 'nullable|string|max:1000',
        ]);

        $schoolId = (int) $request->user()->school_id;
        $this->studentForSchool($schoolId, (int) $data['student_id']);
        abort_unless(
            (int) $request->user()->id === (int) Student::withoutGlobalScopes()->whereKey($data['student_id'])->value('user_id')
                || $request->user()->can('counseling.manage'),
            403,
            'Tidak dapat mengisi wellness check-in siswa lain.',
        );

        $checkin = $this->service->recordWellness(
            $schoolId,
            $data['student_id'],
            $data['mood_score'],
            $data['feeling_tags'] ?? null,
            $data['note'] ?? null,
        );

        return response()->json($checkin, 201);
    }

    public function atRiskStudents(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'counseling.view');
        $days = (int) $request->input('days', 14);
        $grouped = $this->service->atRiskStudents($request->user()->school_id, $days);

        return response()->json([
            'data' => $grouped->map(fn ($items, $studentId) => [
                'student_id' => $studentId,
                'last_checkin' => $items->sortByDesc('checkin_date')->first(),
                'low_mood_days' => $items->where('mood_score', '<=', 3)->count(),
            ])->values(),
        ]);
    }

    private function studentForSchool(int $schoolId, int $studentId): Student
    {
        return Student::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->findOrFail($studentId);
    }

    private function userForSchool(int $schoolId, int $userId): User
    {
        return User::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->findOrFail($userId);
    }

    private function requirePermission(Request $request, string $permission): void
    {
        abort_unless($request->user()->hasRole('super_admin') || $request->user()->can($permission), 403, 'Tidak memiliki izin BK.');
    }
}

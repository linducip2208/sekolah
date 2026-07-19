<?php

namespace App\Http\Controllers\Api\Counseling;

use App\Http\Controllers\Controller;
use App\Models\Counseling\BullyingReport;
use App\Models\Counseling\CounselingSession;
use App\Models\Wellness\WellnessCheckin;
use App\Services\Counseling\CounselingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CounselingController extends Controller
{
    public function __construct(private CounselingService $service) {}

    public function sessions(Request $request): JsonResponse
    {
        $sessions = CounselingSession::where('school_id', $request->user()->school_id)
            ->when($request->input('student_id'), fn ($q, $sid) => $q->where('student_id', $sid))
            ->when($request->input('counselor_id'), fn ($q, $cid) => $q->where('counselor_id', $cid))
            ->orderByDesc('scheduled_at')
            ->paginate(50);

        return response()->json($sessions);
    }

    public function scheduleSession(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id'       => 'required|integer',
            'counselor_id'     => 'required|integer',
            'scheduled_at'     => 'required|date',
            'duration_minutes' => 'nullable|integer|min:15|max:240',
            'type'             => 'required|in:academic,behavior,mental_health,career,family,social',
        ]);

        return response()->json(
            $this->service->scheduleSession($request->user()->school_id, $data),
            201,
        );
    }

    public function completeSession(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'notes'           => 'nullable|string|max:5000',
            'refer_external'  => 'nullable|boolean',
            'referred_to'     => 'nullable|string|max:200',
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
        $reports = BullyingReport::where('school_id', $request->user()->school_id)
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json($reports);
    }

    public function reportBullying(Request $request): JsonResponse
    {
        $data = $request->validate([
            'anonymous'              => 'nullable|boolean',
            'victims_described'      => 'nullable|array',
            'perpetrators_described' => 'nullable|array',
            'type'                   => 'required|in:verbal,physical,cyber,social,other',
            'incident_date'          => 'nullable|date',
            'location'               => 'nullable|string|max:200',
            'description'            => 'required|string',
            'evidence_files'         => 'nullable|array',
        ]);

        $schoolId = $request->user()?->school_id ?? (int) $request->input('school_id');
        $report   = $this->service->reportBullying($schoolId, $request->user()?->id, $data);

        return response()->json($report, 201);
    }

    public function assignBullying(Request $request, int $id): JsonResponse
    {
        $request->validate(['user_id' => 'required|integer']);
        $report = BullyingReport::where('school_id', $request->user()->school_id)->findOrFail($id);
        return response()->json($this->service->assignBullyingReport($report, $request->input('user_id')));
    }

    public function closeBullying(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'status'         => 'required|in:action_taken,closed,unfounded',
            'action_summary' => 'nullable|string|max:5000',
        ]);
        $report = BullyingReport::where('school_id', $request->user()->school_id)->findOrFail($id);
        return response()->json($this->service->closeBullyingReport($report, $data['status'], $data['action_summary'] ?? null));
    }

    public function checkin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id'   => 'required|integer',
            'mood_score'   => 'required|integer|min:1|max:10',
            'feeling_tags' => 'nullable|array',
            'note'         => 'nullable|string|max:1000',
        ]);

        $checkin = $this->service->recordWellness(
            $request->user()->school_id,
            $data['student_id'],
            $data['mood_score'],
            $data['feeling_tags'] ?? null,
            $data['note'] ?? null,
        );

        return response()->json($checkin, 201);
    }

    public function atRiskStudents(Request $request): JsonResponse
    {
        $days = (int) $request->input('days', 14);
        $grouped = $this->service->atRiskStudents($request->user()->school_id, $days);

        return response()->json([
            'data' => $grouped->map(fn ($items, $studentId) => [
                'student_id'    => $studentId,
                'last_checkin'  => $items->sortByDesc('checkin_date')->first(),
                'low_mood_days' => $items->where('mood_score', '<=', 3)->count(),
            ])->values(),
        ]);
    }
}

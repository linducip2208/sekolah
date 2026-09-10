<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Attendance;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Student;
use App\Models\Workflow\WorkflowRequest;
use App\Services\Academic\AttendanceService;
use App\Services\Workflow\WorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $service,
        private WorkflowService $workflow,
    ) {}

    public function getByClass(Request $request, int $classSectionId): JsonResponse
    {
        $request->validate(['date' => 'required|date']);
        $this->requirePermission($request, 'attendance.view');
        $schoolId = (int) $request->user()->school_id;
        ClassSection::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($classSectionId);

        $records = Attendance::where('school_id', $schoolId)
            ->where('class_section_id', $classSectionId)
            ->where('date', $request->date)
            ->with('student.user')
            ->get();

        return response()->json($records);
    }

    public function bulkMark(Request $request, int $classSectionId): JsonResponse
    {
        $this->requirePermission($request, 'attendance.manage');
        $data = $request->validate([
            'date' => 'required|date',
            'attendances' => 'required|array|min:1',
            'attendances.*.student_id' => 'required|integer',
            'attendances.*.status' => 'required|in:present,absent,late,half_day,on_leave',
            'attendances.*.note' => 'nullable|string|max:255',
        ]);

        $summary = $this->service->bulkMark(
            $classSectionId,
            $data['date'],
            $data['attendances'],
            $request->user()
        );

        return response()->json([
            'message' => 'Attendance marked for '.count($data['attendances']).' students',
            'date' => $data['date'],
            'class_section_id' => $classSectionId,
            'summary' => $summary,
        ]);
    }

    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:present,absent,late,half_day,on_leave',
            'note' => 'nullable|string|max:255',
        ]);

        return response()->json($this->service->update($attendance, $data, $request->user()));
    }

    public function lock(Request $request, int $classSectionId): JsonResponse
    {
        $this->requirePermission($request, 'attendance.manage');
        $data = $request->validate(['date' => 'required|date']);

        return response()->json([
            'message' => 'Absensi berhasil dikunci.',
            'lock' => $this->service->lockDate($classSectionId, $data['date'], $request->user()),
        ]);
    }

    public function reopen(Request $request, int $classSectionId): JsonResponse
    {
        $this->requirePermission($request, 'attendance.reopen');
        $data = $request->validate([
            'date' => 'required|date',
            'reason' => 'required|string|max:2000',
        ]);

        return response()->json([
            'message' => 'Absensi dibuka kembali.',
            'lock' => $this->service->reopenDate($classSectionId, $data['date'], $data['reason'], $request->user()),
        ]);
    }

    public function requestCorrection(Request $request, Attendance $attendance): JsonResponse
    {
        $this->requirePermission($request, 'attendance.manage');
        $data = $request->validate([
            'status' => 'required|in:present,absent,late,half_day,on_leave',
            'note' => 'nullable|string|max:255',
            'reason' => 'required|string|max:2000',
        ]);

        return response()->json([
            'message' => 'Koreksi absensi diajukan untuk persetujuan.',
            'workflow' => $this->service->requestCorrection($attendance, $data, $request->user()),
        ], 201);
    }

    public function corrections(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'attendance.approve');

        $items = WorkflowRequest::where('school_id', $request->user()->school_id)
            ->where('type', 'attendance_correction')
            ->with(['requester:id,name', 'approver:id,name'])
            ->when($request->status, fn ($query) => $query->where('status', $request->status))
            ->latest('submitted_at')
            ->paginate(min((int) $request->input('per_page', 25), 100));

        return response()->json($items);
    }

    public function approveCorrection(Request $request, WorkflowRequest $workflowRequest): JsonResponse
    {
        $this->requirePermission($request, 'attendance.approve');
        $this->assertCorrectionRequest($request, $workflowRequest);
        $data = $request->validate(['note' => 'nullable|string|max:2000']);

        return response()->json([
            'message' => 'Koreksi absensi disetujui.',
            'workflow' => $this->workflow->approve($workflowRequest, $data['note'] ?? null),
        ]);
    }

    public function rejectCorrection(Request $request, WorkflowRequest $workflowRequest): JsonResponse
    {
        $this->requirePermission($request, 'attendance.approve');
        $this->assertCorrectionRequest($request, $workflowRequest);
        $data = $request->validate(['note' => 'required|string|max:2000']);

        return response()->json([
            'message' => 'Koreksi absensi ditolak.',
            'workflow' => $this->workflow->reject($workflowRequest, $data['note']),
        ]);
    }

    public function getByStudent(Request $request, int $studentId): JsonResponse
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $schoolId = (int) $request->user()->school_id;
        $student = Student::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($studentId);
        $this->assertStudentReadable($request, $student);

        $records = Attendance::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->whereBetween('date', [$request->from_date, $request->to_date])
            ->orderBy('date')
            ->get();

        return response()->json($records);
    }

    public function summary(Request $request, int $studentId): JsonResponse
    {
        $fromDate = request('from_date', now()->startOfMonth()->toDateString());
        $toDate = request('to_date', now()->toDateString());
        $student = Student::withoutGlobalScopes()
            ->where('school_id', $request->user()->school_id)
            ->findOrFail($studentId);
        $this->assertStudentReadable($request, $student);

        return response()->json(
            $this->service->getStudentSummary($studentId, $fromDate, $toDate)
        );
    }

    private function requirePermission(Request $request, string $permission): void
    {
        abort_unless($request->user()->can($permission), 403, 'Tidak memiliki izin absensi.');
    }

    private function assertStudentReadable(Request $request, Student $student): void
    {
        $user = $request->user();

        if ($user->hasRole('student')) {
            abort_unless((int) $student->user_id === (int) $user->id, 403);
        }

        if ($user->hasRole('parent')) {
            abort_unless($student->parents()->whereKey($user->id)->exists(), 403);
        }

        abort_unless($user->can('attendance.view'), 403, 'Tidak memiliki izin melihat absensi.');
    }

    private function assertCorrectionRequest(Request $request, WorkflowRequest $workflowRequest): void
    {
        abort_unless((int) $workflowRequest->school_id === (int) $request->user()->school_id, 404);
        abort_unless($workflowRequest->type === 'attendance_correction', 404);
        abort_if(in_array($workflowRequest->status, ['approved', 'rejected'], true), 409, 'Koreksi absensi sudah diputuskan.');
    }

    public function mine(Request $request): JsonResponse
    {
        $student = Student::where('user_id', $request->user()->id)->first();
        if (! $student) {
            return response()->json([]);
        }

        $from = $request->get('from_date', now()->startOfMonth()->toDateString());
        $to = $request->get('to_date', now()->toDateString());

        $records = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($records);
    }
}

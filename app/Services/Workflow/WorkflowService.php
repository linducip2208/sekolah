<?php

namespace App\Services\Workflow;

use App\Models\User;
use App\Models\Workflow\WorkflowRequest;
use App\Services\Academic\AttendanceService;
use Illuminate\Support\Facades\DB;

/**
 * Generic, reusable approval workflow for any school-scoped request type
 * (leave, purchase, expense, transfer, discount, refund, etc.).
 */
class WorkflowService
{
    public function create(int $schoolId, int $requesterId, array $data): WorkflowRequest
    {
        User::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($requesterId);
        abort_unless(array_key_exists($data['type'], WorkflowRequest::TYPES), 422, 'Jenis workflow tidak valid.');

        return WorkflowRequest::create([
            'school_id' => $schoolId,
            'requester_id' => $requesterId,
            'type' => $data['type'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'payload' => $data['payload'] ?? null,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    public function approve(WorkflowRequest $request, ?string $note = null): WorkflowRequest
    {
        DB::transaction(function () use ($request, $note) {
            $locked = WorkflowRequest::withoutGlobalScopes()
                ->whereKey($request->id)
                ->lockForUpdate()
                ->firstOrFail();
            $approver = auth()->user();
            abort_unless($approver && (int) $approver->school_id === (int) $locked->school_id, 403, 'Approver bukan bagian dari sekolah ini.');
            abort_unless(in_array($locked->status, ['submitted', 'under_review'], true), 409, 'Workflow sudah diputuskan atau tidak dapat disetujui.');

            $locked->update([
                'status' => 'approved',
                'approver_id' => auth()->id(),
                'decided_at' => now(),
                'decision_note' => $note,
            ]);

            if ($locked->type === 'attendance_correction') {
                app(AttendanceService::class)->applyApprovedCorrection($locked->fresh());
            }
        });

        return $request->fresh();
    }

    public function reject(WorkflowRequest $request, string $note): WorkflowRequest
    {
        DB::transaction(function () use ($request, $note) {
            $locked = WorkflowRequest::withoutGlobalScopes()
                ->whereKey($request->id)
                ->lockForUpdate()
                ->firstOrFail();
            $approver = auth()->user();
            abort_unless($approver && (int) $approver->school_id === (int) $locked->school_id, 403, 'Approver bukan bagian dari sekolah ini.');
            abort_unless(in_array($locked->status, ['submitted', 'under_review'], true), 409, 'Workflow sudah diputuskan atau tidak dapat ditolak.');
            abort_if(blank($note), 422, 'Alasan penolakan wajib diisi.');

            $locked->update([
                'status' => 'rejected',
                'approver_id' => auth()->id(),
                'decided_at' => now(),
                'decision_note' => $note,
            ]);
        });

        return $request->fresh();
    }

    public function pendingCount(int $schoolId): int
    {
        return WorkflowRequest::where('school_id', $schoolId)
            ->whereIn('status', ['submitted', 'under_review'])
            ->count();
    }
}

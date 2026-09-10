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

    public function returnForRevision(WorkflowRequest $request, string $note): WorkflowRequest
    {
        abort_if(blank($note), 422, 'Catatan revisi wajib diisi.');

        DB::transaction(function () use ($request, $note) {
            $locked = $this->lock($request);
            $this->assertApprover($locked);
            abort_unless(in_array($locked->status, ['submitted', 'under_review'], true), 409, 'Workflow tidak dapat dikembalikan untuk revisi.');

            $locked->update([
                'status' => 'returned',
                'approver_id' => auth()->id(),
                'decided_at' => now(),
                'decision_note' => $note,
            ]);
        });

        return $request->fresh();
    }

    public function resubmit(WorkflowRequest $request): WorkflowRequest
    {
        DB::transaction(function () use ($request) {
            $locked = $this->lock($request);
            abort_unless(auth()->id() === $locked->requester_id, 403, 'Hanya pengaju yang dapat mengirim ulang revisi.');
            abort_unless($locked->status === 'returned', 409, 'Workflow belum berstatus perlu revisi.');

            $locked->update([
                'status' => 'submitted',
                'approver_id' => null,
                'submitted_at' => now(),
                'decided_at' => null,
                'decision_note' => null,
            ]);
        });

        return $request->fresh();
    }

    public function cancel(WorkflowRequest $request, ?string $note = null): WorkflowRequest
    {
        DB::transaction(function () use ($request, $note) {
            $locked = $this->lock($request);
            $actor = auth()->user();
            abort_unless($actor && ((int) $actor->id === (int) $locked->requester_id || $actor->hasRole('admin') || $actor->can('workflow.manage')), 403, 'Tidak berhak membatalkan workflow ini.');
            abort_unless(in_array($locked->status, ['draft', 'submitted', 'returned'], true), 409, 'Workflow tidak dapat dibatalkan pada status ini.');

            $locked->update([
                'status' => 'cancelled',
                'approver_id' => $actor->id,
                'decided_at' => now(),
                'decision_note' => $note,
            ]);
        });

        return $request->fresh();
    }

    private function lock(WorkflowRequest $request): WorkflowRequest
    {
        return WorkflowRequest::withoutGlobalScopes()
            ->whereKey($request->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function assertApprover(WorkflowRequest $request): void
    {
        $approver = auth()->user();
        abort_unless($approver && (int) $approver->school_id === (int) $request->school_id, 403, 'Approver bukan bagian dari sekolah ini.');
    }

    public function pendingCount(int $schoolId): int
    {
        return WorkflowRequest::where('school_id', $schoolId)
            ->whereIn('status', ['submitted', 'under_review'])
            ->count();
    }
}

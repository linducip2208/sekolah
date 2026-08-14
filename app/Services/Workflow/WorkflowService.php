<?php

namespace App\Services\Workflow;

use App\Models\Workflow\WorkflowRequest;

/**
 * Generic, reusable approval workflow for any school-scoped request type
 * (leave, purchase, expense, transfer, discount, refund, etc.).
 */
class WorkflowService
{
    public function create(int $schoolId, int $requesterId, array $data): WorkflowRequest
    {
        return WorkflowRequest::create([
            'school_id'    => $schoolId,
            'requester_id' => $requesterId,
            'type'         => $data['type'],
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'payload'      => $data['payload'] ?? null,
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    public function approve(WorkflowRequest $request, ?string $note = null): WorkflowRequest
    {
        $request->update([
            'status'        => 'approved',
            'approver_id'   => auth()->id(),
            'decided_at'    => now(),
            'decision_note' => $note,
        ]);

        return $request;
    }

    public function reject(WorkflowRequest $request, string $note): WorkflowRequest
    {
        $request->update([
            'status'        => 'rejected',
            'approver_id'   => auth()->id(),
            'decided_at'    => now(),
            'decision_note' => $note,
        ]);

        return $request;
    }

    public function pendingCount(int $schoolId): int
    {
        return WorkflowRequest::where('school_id', $schoolId)
            ->whereIn('status', ['submitted', 'under_review'])
            ->count();
    }
}

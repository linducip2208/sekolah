<?php

namespace App\Services;

use App\Models\Finance\BudgetCategory;
use App\Models\Finance\BudgetItem;
use App\Models\Finance\ProcurementApproval;
use App\Models\Finance\ProcurementItem;
use App\Models\Finance\ProcurementRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProcurementService
{
    private int $schoolId;

    public function __construct(int $schoolId)
    {
        $this->schoolId = $schoolId;
    }

    public function generateRequestNumber(): string
    {
        $year = date('Y');
        $last = ProcurementRequest::where('school_id', $this->schoolId)
            ->where('request_number', 'like', "PR-{$year}-%")
            ->withTrashed()
            ->orderByDesc('id')
            ->first();

        if ($last) {
            $parts = explode('-', $last->request_number);
            $seq = (int) end($parts) + 1;
        } else {
            $seq = 1;
        }

        return sprintf('PR-%s-%04d', $year, $seq);
    }

    public function create(array $data): ProcurementRequest
    {
        return DB::transaction(function () use ($data) {
            $data['school_id'] = $this->schoolId;
            $data['request_number'] = $this->generateRequestNumber();

            if (empty($data['status'])) {
                $data['status'] = 'draft';
            }

            $items = $data['items'] ?? [];
            unset($data['items']);

            $request = ProcurementRequest::create($data);

            foreach ($items as $itemData) {
                $itemData['procurement_request_id'] = $request->id;
                ProcurementItem::create($itemData);
            }

            return $request;
        });
    }

    public function update(ProcurementRequest $request, array $data): ProcurementRequest
    {
        return DB::transaction(function () use ($request, $data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $request->update($data);

            if (! empty($items)) {
                $request->items()->delete();
                foreach ($items as $itemData) {
                    $itemData['procurement_request_id'] = $request->id;
                    ProcurementItem::create($itemData);
                }
            }

            return $request->fresh(['items']);
        });
    }

    public function submitForApproval(ProcurementRequest $request): void
    {
        if ($request->status !== 'draft') {
            throw new \RuntimeException('Hanya permintaan draft yang bisa disubmit.');
        }

        if ($request->items()->count() === 0) {
            throw new \RuntimeException('Permintaan harus memiliki minimal 1 item.');
        }

        $this->checkBudget($request);

        $request->update(['status' => 'submitted']);

        $this->createApprovalChain($request);
    }

    private function checkBudget(ProcurementRequest $request): void
    {
        $totalEstimated = $request->totalEstimated();

        $budgetItem = null;

        if ($request->budget_category_id) {
            $budgetItem = BudgetItem::where('school_id', $this->schoolId)
                ->where('budget_category_id', $request->budget_category_id)
                ->first();
        }

        if (! $budgetItem && $request->department) {
            $category = BudgetCategory::where('school_id', $this->schoolId)
                ->where('name', 'like', "%{$request->department}%")
                ->first();

            if ($category) {
                $budgetItem = BudgetItem::where('school_id', $this->schoolId)
                    ->where('budget_category_id', $category->id)
                    ->first();
            }
        }

        if (! $budgetItem) {
            return;
        }

        $remaining = $budgetItem->planned_amount - $budgetItem->actual_amount;

        if ($totalEstimated > $remaining) {
            $sisaFormatted = 'Rp ' . number_format($remaining / 100, 0, ',', '.');

            throw ValidationException::withMessages([
                'estimated_budget' => "Anggaran tidak mencukupi. Sisa anggaran: {$sisaFormatted}",
            ]);
        }
    }

    public function createApprovalChain(ProcurementRequest $request): void
    {
        $approverIds = $this->findApprovers();

        if (empty($approverIds)) {
            $request->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            return;
        }

        foreach ($approverIds as $step => $approverId) {
            ProcurementApproval::create([
                'procurement_request_id' => $request->id,
                'approver_id'            => $approverId,
                'step_order'             => $step + 1,
                'status'                 => 'pending',
            ]);
        }
    }

    public function findApprovers(): array
    {
        $approvers = [];

        $kepalaSekolah = User::where('school_id', $this->schoolId)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['admin']))
            ->first();

        $bendahara = User::where('school_id', $this->schoolId)
            ->whereHas('roles', fn($q) => $q->where('name', 'accountant'))
            ->first();

        if ($bendahara) {
            $approvers[] = $bendahara->id;
        }

        if ($kepalaSekolah) {
            $approvers[] = $kepalaSekolah->id;
        }

        return $approvers;
    }

    public function approveStep(ProcurementApproval $approval, string $notes = null): void
    {
        if ($approval->status !== 'pending') {
            throw new \RuntimeException('Tahap persetujuan ini sudah diproses.');
        }

        $approval->update([
            'status'     => 'approved',
            'notes'      => $notes,
            'decided_at' => now(),
        ]);

        $this->checkCompletion($approval->request);
    }

    public function rejectStep(ProcurementApproval $approval, string $notes = null): void
    {
        if ($approval->status !== 'pending') {
            throw new \RuntimeException('Tahap persetujuan ini sudah diproses.');
        }

        $approval->update([
            'status'     => 'rejected',
            'notes'      => $notes,
            'decided_at' => now(),
        ]);

        $approval->request->update([
            'status'          => 'rejected',
            'rejected_reason' => $notes,
        ]);
    }

    private function checkCompletion(ProcurementRequest $request): void
    {
        $pending = $request->approvals()->where('status', 'pending')->count();
        $rejected = $request->approvals()->where('status', 'rejected')->count();

        if ($rejected > 0) return;

        if ($pending === 0) {
            $request->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        }
    }

    public function markAsOrdered(ProcurementRequest $request): void
    {
        if (! in_array($request->status, ['approved'])) {
            throw new \RuntimeException('Hanya permintaan yang sudah disetujui yang bisa di-order.');
        }
        $request->update(['status' => 'ordered']);
    }

    public function receiveItems(ProcurementRequest $request, array $receivedQtys): void
    {
        if (! in_array($request->status, ['ordered'])) {
            throw new \RuntimeException('Hanya permintaan dalam status ordered yang bisa diterima.');
        }

        DB::transaction(function () use ($request, $receivedQtys) {
            $allReceived = true;
            foreach ($request->items as $item) {
                $qty = $receivedQtys[$item->id] ?? 0;
                $item->update(['received_qty' => $qty]);
                if ($qty < $item->quantity) {
                    $allReceived = false;
                }
            }

            if ($allReceived) {
                $request->update(['status' => 'received']);
            }
        });
    }

    public function getPendingCount(): int
    {
        return ProcurementRequest::where('school_id', $this->schoolId)
            ->where('status', 'submitted')
            ->count();
    }
}

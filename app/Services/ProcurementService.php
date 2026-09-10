<?php

namespace App\Services;

use App\Models\Finance\BudgetCategory;
use App\Models\Finance\BudgetItem;
use App\Models\Finance\ProcurementApproval;
use App\Models\Finance\ProcurementItem;
use App\Models\Finance\ProcurementRequest;
use App\Models\Finance\Supplier;
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
            $this->validateReferences($data, $items);

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
            abort_unless((int) $request->school_id === $this->schoolId, 404);
            abort_unless($request->status === 'draft', 409, 'Hanya permintaan draft yang bisa diubah.');
            $items = $data['items'] ?? [];
            unset($data['items']);
            $this->validateReferences($data, $items);

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
        abort_unless((int) $request->school_id === $this->schoolId, 404);
        if ($request->status !== 'draft') {
            throw new \RuntimeException('Hanya permintaan draft yang bisa disubmit.');
        }

        if ($request->items()->count() === 0) {
            throw new \RuntimeException('Permintaan harus memiliki minimal 1 item.');
        }

        $this->checkBudget($request);

        DB::transaction(function () use ($request) {
            $locked = ProcurementRequest::where('school_id', $this->schoolId)
                ->whereKey($request->id)
                ->lockForUpdate()
                ->firstOrFail();
            abort_unless($locked->status === 'draft', 409, 'Permintaan sudah diproses.');
            $locked->update(['status' => 'submitted']);
            $this->createApprovalChain($locked);
        });
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
            $sisaFormatted = 'Rp '.number_format($remaining / 100, 0, ',', '.');

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
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            return;
        }

        foreach ($approverIds as $step => $approverId) {
            ProcurementApproval::create([
                'procurement_request_id' => $request->id,
                'approver_id' => $approverId,
                'step_order' => $step + 1,
                'status' => 'pending',
            ]);
        }
    }

    public function findApprovers(): array
    {
        $approvers = [];

        $kepalaSekolah = User::where('school_id', $this->schoolId)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin']))
            ->first();

        $bendahara = User::where('school_id', $this->schoolId)
            ->whereHas('roles', fn ($q) => $q->where('name', 'accountant'))
            ->first();

        if ($bendahara) {
            $approvers[] = $bendahara->id;
        }

        if ($kepalaSekolah) {
            $approvers[] = $kepalaSekolah->id;
        }

        return $approvers;
    }

    public function approveStep(ProcurementApproval $approval, ?string $notes = null): void
    {
        DB::transaction(function () use ($approval, $notes) {
            $locked = ProcurementApproval::whereKey($approval->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'pending') {
                throw new \RuntimeException('Tahap persetujuan ini sudah diproses.');
            }
            $locked->update(['status' => 'approved', 'notes' => $notes, 'decided_at' => now()]);
            $this->checkCompletion($locked->request()->lockForUpdate()->firstOrFail());
        });
    }

    public function rejectStep(ProcurementApproval $approval, ?string $notes = null): void
    {
        DB::transaction(function () use ($approval, $notes) {
            $locked = ProcurementApproval::whereKey($approval->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'pending') {
                throw new \RuntimeException('Tahap persetujuan ini sudah diproses.');
            }
            $locked->update(['status' => 'rejected', 'notes' => $notes, 'decided_at' => now()]);
            $locked->request()->lockForUpdate()->firstOrFail()->update([
                'status' => 'rejected',
                'rejected_reason' => $notes,
            ]);
        });
    }

    private function checkCompletion(ProcurementRequest $request): void
    {
        $pending = $request->approvals()->where('status', 'pending')->count();
        $rejected = $request->approvals()->where('status', 'rejected')->count();

        if ($rejected > 0) {
            return;
        }

        if ($pending === 0) {
            $request->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        }
    }

    public function markAsOrdered(ProcurementRequest $request): void
    {
        DB::transaction(function () use ($request) {
            $locked = ProcurementRequest::where('school_id', $this->schoolId)->whereKey($request->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'approved') {
                throw new \RuntimeException('Hanya permintaan yang sudah disetujui yang bisa di-order.');
            }
            $locked->update(['status' => 'ordered']);
        });
    }

    public function receiveItems(ProcurementRequest $request, array $receivedQtys): void
    {
        DB::transaction(function () use ($request, $receivedQtys) {
            $lockedRequest = ProcurementRequest::where('school_id', $this->schoolId)
                ->whereKey($request->id)
                ->lockForUpdate()
                ->firstOrFail();
            if ($lockedRequest->status !== 'ordered') {
                throw new \RuntimeException('Hanya permintaan dalam status ordered yang bisa diterima.');
            }
            $lockedRequest->load('items');
            $allReceived = true;
            foreach ($lockedRequest->items as $item) {
                $qty = array_key_exists($item->id, $receivedQtys) ? (float) $receivedQtys[$item->id] : (float) $item->received_qty;
                if ($qty < 0 || $qty > (float) $item->quantity) {
                    throw new \RuntimeException("Jumlah diterima untuk {$item->item_name} tidak valid.");
                }
                $item->update(['received_qty' => $qty]);
                if ($qty < $item->quantity) {
                    $allReceived = false;
                }
            }

            if ($allReceived) {
                $lockedRequest->update(['status' => 'received']);
            }
        });
    }

    public function getPendingCount(): int
    {
        return ProcurementRequest::where('school_id', $this->schoolId)
            ->where('status', 'submitted')
            ->count();
    }

    private function validateReferences(array $data, array $items): void
    {
        if (! empty($data['budget_category_id'])) {
            abort_unless(BudgetCategory::where('school_id', $this->schoolId)->whereKey($data['budget_category_id'])->exists(), 422, 'Kategori anggaran bukan milik sekolah aktif.');
        }

        foreach ($items as $item) {
            if (! empty($item['supplier_id'])) {
                abort_unless(Supplier::where('school_id', $this->schoolId)->whereKey($item['supplier_id'])->exists(), 422, 'Supplier bukan milik sekolah aktif.');
            }
            abort_if((float) ($item['quantity'] ?? 0) <= 0, 422, 'Jumlah item harus lebih besar dari nol.');
            abort_if((int) ($item['estimated_unit_price'] ?? 0) < 0, 422, 'Harga estimasi tidak valid.');
        }
    }
}

<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Asset;
use App\Models\Inventory\AssetCategory;
use App\Models\Inventory\AssetLoan;
use App\Models\Inventory\MaintenanceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryService
{
    public function createAsset(int $schoolId, array $data): Asset
    {
        AssetCategory::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->findOrFail($data['asset_category_id']);

        return Asset::create(array_merge($data, [
            'school_id' => $schoolId,
            'asset_code' => $data['asset_code'] ?? 'AST-'.strtoupper(Str::random(8)),
            'condition' => $data['condition'] ?? 'good',
            'status' => $data['status'] ?? 'available',
        ]));
    }

    public function requestLoan(int $schoolId, int $assetId, int $borrowerId, \DateTimeInterface $dueAt): AssetLoan
    {
        return DB::transaction(function () use ($schoolId, $assetId, $borrowerId, $dueAt) {
            $asset = Asset::where('school_id', $schoolId)->where('id', $assetId)->lockForUpdate()->firstOrFail();
            User::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($borrowerId);

            if ($asset->status !== 'available') {
                throw new \RuntimeException('Asset tidak tersedia');
            }

            return AssetLoan::create([
                'school_id' => $schoolId,
                'asset_id' => $assetId,
                'borrower_id' => $borrowerId,
                'borrowed_at' => today(),
                'due_at' => $dueAt,
                'status' => 'pending',
            ]);
        });
    }

    public function approveLoan(AssetLoan $loan, int $approvedBy): AssetLoan
    {
        return DB::transaction(function () use ($loan, $approvedBy) {
            $loan = AssetLoan::withoutGlobalScopes()
                ->where('school_id', $loan->school_id)
                ->lockForUpdate()
                ->findOrFail($loan->id);
            User::withoutGlobalScopes()->where('school_id', $loan->school_id)->findOrFail($approvedBy);
            if ($loan->status !== 'pending') {
                throw new \RuntimeException('Peminjaman hanya dapat disetujui saat berstatus pending.');
            }

            $asset = Asset::withoutGlobalScopes()
                ->where('school_id', $loan->school_id)
                ->lockForUpdate()
                ->findOrFail($loan->asset_id);
            if ($asset->status !== 'available') {
                throw new \RuntimeException('Asset tidak tersedia.');
            }

            $loan->update(['approved_by' => $approvedBy, 'status' => 'active']);
            $asset->update(['status' => 'borrowed']);

            return $loan->fresh();
        });
    }

    public function returnAsset(AssetLoan $loan): AssetLoan
    {
        return DB::transaction(function () use ($loan) {
            $loan = AssetLoan::withoutGlobalScopes()
                ->where('school_id', $loan->school_id)
                ->lockForUpdate()
                ->findOrFail($loan->id);
            if (! in_array($loan->status, ['active', 'overdue'], true)) {
                throw new \RuntimeException('Peminjaman tidak sedang aktif.');
            }

            $loan->update(['returned_at' => today(), 'status' => 'returned']);
            Asset::withoutGlobalScopes()
                ->where('school_id', $loan->school_id)
                ->whereKey($loan->asset_id)
                ->update(['status' => 'available']);

            return $loan->fresh();
        });
    }

    public function reportMaintenance(int $schoolId, int $reporterId, array $data): MaintenanceRequest
    {
        User::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($reporterId);
        if (! empty($data['asset_id'])) {
            Asset::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($data['asset_id']);
        }

        return MaintenanceRequest::create(array_merge($data, [
            'school_id' => $schoolId,
            'reported_by' => $reporterId,
            'priority' => $data['priority'] ?? 'medium',
            'status' => 'reported',
        ]));
    }

    public function assignMaintenance(MaintenanceRequest $req, int $assignedTo): MaintenanceRequest
    {
        $req = MaintenanceRequest::withoutGlobalScopes()
            ->where('school_id', $req->school_id)
            ->findOrFail($req->id);
        User::withoutGlobalScopes()->where('school_id', $req->school_id)->findOrFail($assignedTo);
        if (in_array($req->status, ['resolved', 'rejected'], true)) {
            throw new \RuntimeException('Permintaan maintenance sudah ditutup.');
        }

        $req->update(['assigned_to' => $assignedTo, 'status' => 'assigned']);

        return $req->fresh();
    }

    public function resolveMaintenance(MaintenanceRequest $req, ?string $note, ?int $cost = null): MaintenanceRequest
    {
        $req = MaintenanceRequest::withoutGlobalScopes()
            ->where('school_id', $req->school_id)
            ->findOrFail($req->id);
        if (in_array($req->status, ['resolved', 'rejected'], true)) {
            throw new \RuntimeException('Permintaan maintenance sudah ditutup.');
        }

        $req->update([
            'status' => 'resolved',
            'resolution_note' => $note,
            'cost' => $cost,
            'resolved_at' => now(),
        ]);

        return $req->fresh();
    }
}

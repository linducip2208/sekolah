<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Asset;
use App\Models\Inventory\AssetLoan;
use App\Models\Inventory\MaintenanceRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryService
{
    public function createAsset(int $schoolId, array $data): Asset
    {
        return Asset::create(array_merge($data, [
            'school_id'  => $schoolId,
            'asset_code' => $data['asset_code'] ?? 'AST-' . strtoupper(Str::random(8)),
            'condition'  => $data['condition'] ?? 'good',
            'status'     => $data['status'] ?? 'available',
        ]));
    }

    public function requestLoan(int $schoolId, int $assetId, int $borrowerId, \DateTimeInterface $dueAt): AssetLoan
    {
        return DB::transaction(function () use ($schoolId, $assetId, $borrowerId, $dueAt) {
            $asset = Asset::where('school_id', $schoolId)->where('id', $assetId)->lockForUpdate()->firstOrFail();

            if ($asset->status !== 'available') {
                throw new \RuntimeException('Asset tidak tersedia');
            }

            return AssetLoan::create([
                'school_id'   => $schoolId,
                'asset_id'    => $assetId,
                'borrower_id' => $borrowerId,
                'borrowed_at' => today(),
                'due_at'      => $dueAt,
                'status'      => 'pending',
            ]);
        });
    }

    public function approveLoan(AssetLoan $loan, int $approvedBy): AssetLoan
    {
        return DB::transaction(function () use ($loan, $approvedBy) {
            $loan->update(['approved_by' => $approvedBy, 'status' => 'active']);
            Asset::where('id', $loan->asset_id)->update(['status' => 'borrowed']);
            return $loan->fresh();
        });
    }

    public function returnAsset(AssetLoan $loan): AssetLoan
    {
        return DB::transaction(function () use ($loan) {
            $loan->update(['returned_at' => today(), 'status' => 'returned']);
            Asset::where('id', $loan->asset_id)->update(['status' => 'available']);
            return $loan->fresh();
        });
    }

    public function reportMaintenance(int $schoolId, int $reporterId, array $data): MaintenanceRequest
    {
        return MaintenanceRequest::create(array_merge($data, [
            'school_id'   => $schoolId,
            'reported_by' => $reporterId,
            'priority'    => $data['priority'] ?? 'medium',
            'status'      => 'reported',
        ]));
    }

    public function assignMaintenance(MaintenanceRequest $req, int $assignedTo): MaintenanceRequest
    {
        $req->update(['assigned_to' => $assignedTo, 'status' => 'assigned']);
        return $req->fresh();
    }

    public function resolveMaintenance(MaintenanceRequest $req, ?string $note, ?int $cost = null): MaintenanceRequest
    {
        $req->update([
            'status'           => 'resolved',
            'resolution_note'  => $note,
            'cost'             => $cost,
            'resolved_at'      => now(),
        ]);
        return $req->fresh();
    }
}

<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Asset;
use App\Models\Inventory\AssetLoan;
use App\Models\Inventory\MaintenanceRequest;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $service) {}

    public function assets(Request $request): JsonResponse
    {
        return response()->json([
            'data' => Asset::where('school_id', $request->user()->school_id)
                ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
                ->paginate(50),
        ]);
    }

    public function storeAsset(Request $request): JsonResponse
    {
        $data = $request->validate([
            'asset_category_id' => 'required|integer',
            'asset_code'        => 'nullable|string|max:50',
            'name'              => 'required|string|max:200',
            'description'       => 'nullable|string',
            'serial_number'     => 'nullable|string|max:100',
            'purchased_at'      => 'nullable|date',
            'purchase_price'    => 'nullable|integer|min:0',
            'warranty_until'    => 'nullable|date',
            'location'          => 'nullable|string|max:200',
            'photo_path'        => 'nullable|string|max:500',
            'condition'         => 'nullable|in:excellent,good,fair,damaged,disposed',
            'status'            => 'nullable|in:available,borrowed,maintenance,disposed',
            'specs'             => 'nullable|array',
        ]);

        return response()->json(
            $this->service->createAsset($request->user()->school_id, $data),
            201,
        );
    }

    public function requestLoan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'asset_id' => 'required|integer',
            'due_at'   => 'required|date|after:today',
        ]);

        try {
            $loan = $this->service->requestLoan(
                $request->user()->school_id,
                $data['asset_id'],
                $request->user()->id,
                new \DateTimeImmutable($data['due_at']),
            );
            return response()->json($loan, 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function approveLoan(Request $request, int $id): JsonResponse
    {
        $loan = AssetLoan::where('school_id', $request->user()->school_id)->findOrFail($id);
        return response()->json($this->service->approveLoan($loan, $request->user()->id));
    }

    public function returnLoan(Request $request, int $id): JsonResponse
    {
        $loan = AssetLoan::where('school_id', $request->user()->school_id)->findOrFail($id);
        return response()->json($this->service->returnAsset($loan));
    }

    public function maintenanceRequests(Request $request): JsonResponse
    {
        return response()->json([
            'data' => MaintenanceRequest::where('school_id', $request->user()->school_id)
                ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
                ->paginate(50),
        ]);
    }

    public function reportMaintenance(Request $request): JsonResponse
    {
        $data = $request->validate([
            'asset_id'          => 'nullable|integer',
            'location_text'     => 'nullable|string|max:200',
            'issue_description' => 'required|string',
            'photos'            => 'nullable|array',
            'priority'          => 'nullable|in:low,medium,high,critical',
        ]);

        return response()->json(
            $this->service->reportMaintenance($request->user()->school_id, $request->user()->id, $data),
            201,
        );
    }

    public function resolveMaintenance(Request $request, int $id): JsonResponse
    {
        $req = MaintenanceRequest::where('school_id', $request->user()->school_id)->findOrFail($id);
        return response()->json($this->service->resolveMaintenance(
            $req,
            $request->input('note'),
            $request->integer('cost'),
        ));
    }
}

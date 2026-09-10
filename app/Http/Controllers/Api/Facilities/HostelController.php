<?php

namespace App\Http\Controllers\Api\Facilities;

use App\Http\Controllers\Controller;
use App\Models\Facilities\Hostel;
use App\Services\Facilities\HostelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HostelController extends Controller
{
    public function __construct(private HostelService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'hostel.view');

        return response()->json(Hostel::with('rooms')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'hostel.manage');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:boys,girls,mixed',
            'warden_name' => 'nullable|string|max:255',
        ]);
        $validated['school_id'] = auth()->user()->school_id;

        return response()->json(Hostel::create($validated), 201);
    }

    public function storeRoom(Request $request, Hostel $hostel): JsonResponse
    {
        $this->requirePermission($request, 'hostel.manage');
        abort_unless((int) $hostel->school_id === (int) $request->user()->school_id, 404);
        $validated = $request->validate([
            'room_no' => 'required|string|max:20',
            'capacity' => 'sometimes|integer|min:1',
            'fee_per_month' => 'sometimes|integer|min:0',
        ]);

        return response()->json($hostel->rooms()->create($validated), 201);
    }

    public function allocate(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'hostel.manage');
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'room_id' => 'required|integer|exists:hostel_rooms,id',
            'from_date' => 'required|date',
        ]);

        $allocation = $this->service->allocate($validated['student_id'], $validated['room_id'], $validated['from_date']);

        return response()->json($allocation, 201);
    }

    private function requirePermission(Request $request, string $permission): void
    {
        abort_unless($request->user()->hasRole('super_admin') || $request->user()->can($permission), 403, 'Tidak memiliki izin asrama.');
    }
}

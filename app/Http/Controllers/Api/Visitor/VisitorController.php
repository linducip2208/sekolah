<?php

namespace App\Http\Controllers\Api\Visitor;

use App\Http\Controllers\Controller;
use App\Models\Visitor\VisitorVisit;
use App\Services\Visitor\VisitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    public function __construct(private readonly VisitorService $service) {}

    public function index(Request $request): JsonResponse
    {
        $data = VisitorVisit::withoutGlobalScopes()->where('school_id', $request->user()->school_id)
            ->when($request->filled('date'), fn ($q) => $q->whereDate('visit_date', $request->input('date')))
            ->with(['visitor', 'host', 'badges'])->latest('id')->paginate(50);

        return response()->json(['data' => $data]);
    }

    public function active(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->service->activeVisitors((int) $request->user()->school_id)]);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'identity_type' => 'nullable|string|max:30',
            'identity_number' => 'nullable|string|max:80',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'photo_path' => 'nullable|string|max:500',
            'company' => 'nullable|string|max:150',
            'purpose' => 'required|string|max:255',
            'host_user_id' => 'nullable|integer|exists:users,id',
            'destination' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $visit = $this->service->register((int) $request->user()->school_id, $data, (int) $request->user()->id);

            return response()->json($visit, 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function preRegister(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'identity_type' => 'nullable|string|max:30',
            'identity_number' => 'nullable|string|max:80',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'company' => 'nullable|string|max:150',
            'purpose' => 'required|string|max:255',
            'host_user_id' => 'nullable|integer|exists:users,id',
            'destination' => 'nullable|string|max:150',
            'expected_arrival' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);
        try {
            return response()->json($this->service->register((int) $request->user()->school_id, $data, (int) $request->user()->id, true), 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function checkOut(Request $request, int $id): JsonResponse
    {
        try {
            return response()->json($this->service->checkOut((int) $request->user()->school_id, $id, (int) $request->user()->id));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        return response()->json($this->service->approve((int) $request->user()->school_id, $id, (int) $request->user()->id));
    }
}

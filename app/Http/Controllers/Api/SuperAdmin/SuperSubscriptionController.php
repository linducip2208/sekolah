<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Finance\SubscriptionTransaction;
use App\Services\SuperAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuperSubscriptionController extends Controller
{
    public function __construct(private SuperAdminService $service) {}

    public function index(Request $request): JsonResponse
    {
        $txs = SubscriptionTransaction::withoutGlobalScopes()
            ->with('plan')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);
        return response()->json($txs);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_id'      => 'required|integer|exists:schools,id',
            'plan_id'        => 'required|integer|exists:plans,id',
            'amount'         => 'required|integer|min:0',
            'payment_method' => 'sometimes|string',
            'reference'      => 'sometimes|string',
            'period_from'    => 'required|date',
            'period_to'      => 'required|date|after:period_from',
        ]);

        return response()->json($this->service->recordSubscription($validated), 201);
    }
}

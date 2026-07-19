<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuperPlanController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Plan::withCount(['schools' => fn($q) => $q->withoutGlobalScopes()])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'slug'         => 'required|string|unique:plans,slug',
            'price'        => 'required|integer|min:0',
            'max_students' => 'sometimes|integer|min:0',
            'max_teachers' => 'sometimes|integer|min:0',
            'features'     => 'sometimes|array',
        ]);
        $validated['features'] ??= [];

        return response()->json(Plan::create($validated), 201);
    }

    public function update(Request $request, Plan $plan): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'price'        => 'sometimes|integer|min:0',
            'max_students' => 'sometimes|integer|min:0',
            'max_teachers' => 'sometimes|integer|min:0',
            'features'     => 'sometimes|array',
            'is_active'    => 'sometimes|boolean',
        ]);
        $plan->update($validated);
        return response()->json($plan->fresh());
    }
}

<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Services\SuperAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class SuperSchoolController extends Controller
{
    public function __construct(private SuperAdminService $service) {}

    public function index(Request $request): JsonResponse
    {
        $schools = School::withoutGlobalScopes()
            ->with('plan')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('is_active', $request->status === 'active'))
            ->orderBy('name')
            ->paginate(20);

        return response()->json($schools);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'subdomain'      => 'required|string|max:100|unique:schools,subdomain',
            'email'          => 'nullable|email',
            'phone'          => 'nullable|string|max:30',
            'address'        => 'nullable|string',
            'plan_id'        => 'nullable|integer|exists:plans,id',
            'plan_expires_at' => 'nullable|date',
            'admin_name'     => 'sometimes|string|max:255',
            'admin_email'    => 'sometimes|email|unique:users,email',
            'admin_password' => 'sometimes|string|min:8',
        ]);

        $school = $this->service->createSchool($validated);
        return response()->json($school->load('plan'), 201);
    }

    public function show(int $id): JsonResponse
    {
        $school = School::withoutGlobalScopes()->with('plan')->findOrFail($id);
        return response()->json($school);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $school    = School::withoutGlobalScopes()->findOrFail($id);
        $validated = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'email'     => 'sometimes|email',
            'phone'     => 'sometimes|string|max:30',
            'address'   => 'sometimes|string',
            'plan_id'   => 'sometimes|nullable|integer|exists:plans,id',
            'plan_expires_at' => 'sometimes|nullable|date',
        ]);
        $school->update($validated);
        return response()->json($school->fresh('plan'));
    }

    public function suspend(int $id): JsonResponse
    {
        $school = School::withoutGlobalScopes()->findOrFail($id);
        return response()->json($this->service->suspendSchool($school));
    }

    public function activate(int $id): JsonResponse
    {
        $school = School::withoutGlobalScopes()->findOrFail($id);
        return response()->json($this->service->activateSchool($school));
    }

    public function destroy(int $id): JsonResponse
    {
        School::withoutGlobalScopes()->findOrFail($id)->delete();
        return response()->json(['message' => 'School deleted.']);
    }

    public function stats(int $id): JsonResponse
    {
        $school = School::withoutGlobalScopes()->findOrFail($id);
        return response()->json($this->service->schoolStats($school));
    }

    public function activityLog(int $id): JsonResponse
    {
        $log = Activity::where('properties->school_id', $id)
            ->orWhere(fn($q) => $q->where('subject_type', School::class)->where('subject_id', $id))
            ->latest()
            ->paginate(20);
        return response()->json($log);
    }

    public function extendSubscription(Request $request, int $id): JsonResponse
    {
        $school    = School::withoutGlobalScopes()->findOrFail($id);
        $validated = $request->validate([
            'plan_id'    => 'required|integer|exists:plans,id',
            'expires_at' => 'required|date',
        ]);
        return response()->json($this->service->extendSubscription($school, $validated['plan_id'], $validated['expires_at']));
    }

    public function upgradeSubscription(Request $request, int $id): JsonResponse
    {
        $school    = School::withoutGlobalScopes()->findOrFail($id);
        $validated = $request->validate([
            'plan_id'    => 'required|integer|exists:plans,id',
            'expires_at' => 'required|date',
        ]);
        return response()->json($this->service->extendSubscription($school, $validated['plan_id'], $validated['expires_at']));
    }
}

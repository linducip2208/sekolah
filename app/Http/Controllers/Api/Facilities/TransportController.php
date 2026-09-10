<?php

namespace App\Http\Controllers\Api\Facilities;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Facilities\StudentTransport;
use App\Models\Facilities\TransportRoute;
use App\Models\Facilities\TransportRouteStop;
use App\Models\Facilities\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransportController extends Controller
{
    public function routes(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'transport.view');

        return response()->json(TransportRoute::with('stops')->where('is_active', true)->get());
    }

    public function storeRoute(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'transport.manage');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'fee_per_month' => 'sometimes|integer|min:0',
            'stops' => 'sometimes|array',
            'stops.*.stop_name' => 'required|string',
            'stops.*.pickup_time' => 'nullable|date_format:H:i',
            'stops.*.order' => 'sometimes|integer',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $stops = $validated['stops'] ?? [];
        unset($validated['stops']);

        $route = DB::transaction(function () use ($validated, $stops) {
            $route = TransportRoute::create($validated);
            foreach ($stops as $stop) {
                $route->stops()->create($stop);
            }

            return $route;
        });

        return response()->json($route->load('stops'), 201);
    }

    public function vehicles(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'transport.view');

        return response()->json(Vehicle::all());
    }

    public function storeVehicle(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'transport.manage');
        $validated = $request->validate([
            'registration_no' => 'required|string|max:50',
            'make_model' => 'nullable|string|max:100',
            'capacity' => 'sometimes|integer|min:1',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:30',
        ]);
        $validated['school_id'] = auth()->user()->school_id;

        return response()->json(Vehicle::create($validated), 201);
    }

    public function assignStudent(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'transport.manage');
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'transport_route_id' => 'required|integer|exists:transport_routes,id',
            'transport_route_stop_id' => 'nullable|integer|exists:transport_route_stops,id',
        ]);
        $schoolId = (int) $request->user()->school_id;
        abort_unless(Student::withoutGlobalScopes()->where('school_id', $schoolId)->whereKey($validated['student_id'])->exists(), 404);
        $route = TransportRoute::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->findOrFail($validated['transport_route_id']);
        if (! empty($validated['transport_route_stop_id'])) {
            abort_unless(TransportRouteStop::where('transport_route_id', $route->id)->whereKey($validated['transport_route_stop_id'])->exists(), 422, 'Halte bukan milik rute ini.');
        }
        $validated['school_id'] = $schoolId;
        $validated['is_active'] = true;

        $assignment = DB::transaction(function () use ($validated, $schoolId) {
            StudentTransport::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->where('student_id', $validated['student_id'])
                ->where('is_active', true)
                ->lockForUpdate()
                ->update(['is_active' => false]);

            return StudentTransport::create($validated);
        });

        return response()->json($assignment->load('route', 'stop'), 201);
    }

    private function requirePermission(Request $request, string $permission): void
    {
        abort_unless($request->user()->hasRole('super_admin') || $request->user()->can($permission), 403, 'Tidak memiliki izin transportasi.');
    }
}

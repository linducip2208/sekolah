<?php

namespace App\Http\Controllers\Api\Facilities;

use App\Http\Controllers\Controller;
use App\Models\Facilities\StudentTransport;
use App\Models\Facilities\TransportRoute;
use App\Models\Facilities\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransportController extends Controller
{
    public function routes(): JsonResponse
    {
        return response()->json(TransportRoute::with('stops')->where('is_active', true)->get());
    }

    public function storeRoute(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'fee_per_month' => 'sometimes|integer|min:0',
            'stops'         => 'sometimes|array',
            'stops.*.stop_name'   => 'required|string',
            'stops.*.pickup_time' => 'nullable|date_format:H:i',
            'stops.*.order'       => 'sometimes|integer',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $stops  = $validated['stops'] ?? [];
        unset($validated['stops']);

        $route = TransportRoute::create($validated);
        foreach ($stops as $stop) {
            $route->stops()->create($stop);
        }

        return response()->json($route->load('stops'), 201);
    }

    public function vehicles(): JsonResponse
    {
        return response()->json(Vehicle::all());
    }

    public function storeVehicle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'registration_no' => 'required|string|max:50',
            'make_model'      => 'nullable|string|max:100',
            'capacity'        => 'sometimes|integer|min:1',
            'driver_name'     => 'nullable|string|max:255',
            'driver_phone'    => 'nullable|string|max:30',
        ]);
        $validated['school_id'] = auth()->user()->school_id;
        return response()->json(Vehicle::create($validated), 201);
    }

    public function assignStudent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id'              => 'required|integer|exists:students,id',
            'transport_route_id'      => 'required|integer|exists:transport_routes,id',
            'transport_route_stop_id' => 'nullable|integer|exists:transport_route_stops,id',
        ]);
        $validated['school_id'] = auth()->user()->school_id;
        $validated['is_active'] = true;

        StudentTransport::where('student_id', $validated['student_id'])->update(['is_active' => false]);
        $assignment = StudentTransport::create($validated);

        return response()->json($assignment->load('route', 'stop'), 201);
    }
}

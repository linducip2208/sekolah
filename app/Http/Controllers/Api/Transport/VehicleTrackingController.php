<?php

namespace App\Http\Controllers\Api\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\VehicleTrip;
use App\Services\Transport\VehicleTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleTrackingController extends Controller
{
    public function __construct(private VehicleTrackingService $service) {}

    /**
     * Device GPS push (called by GPS hardware in vehicle).
     * Authenticated via device token in Authorization: Bearer header (validated in middleware).
     */
    public function ping(Request $request): JsonResponse
    {
        $data = $request->validate([
            'school_id'   => 'required|integer',
            'vehicle_id'  => 'required|integer',
            'lat'         => 'required|numeric|between:-90,90',
            'lng'         => 'required|numeric|between:-180,180',
            'speed_kmh'   => 'nullable|numeric|min:0',
            'heading_deg' => 'nullable|numeric|min:0|max:360',
            'recorded_at' => 'nullable|date',
        ]);

        $location = $this->service->recordPing($data['school_id'], $data['vehicle_id'], $data);

        return response()->json(['ok' => true, 'id' => $location->id]);
    }

    public function busLocationForChild(Request $request, int $studentId): JsonResponse
    {
        $student = \App\Models\Academic\Student::where('school_id', $request->user()->school_id)
            ->findOrFail($studentId);

        $transport = \App\Models\Facilities\StudentTransport::where('student_id', $student->id)
            ->where('is_active', true)
            ->first();

        if (!$transport) {
            return response()->json(['vehicle' => null, 'location' => null]);
        }

        $trip = VehicleTrip::where('transport_route_id', $transport->transport_route_id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$trip) {
            return response()->json(['vehicle' => null, 'location' => null]);
        }

        $location = $this->service->getLatestForVehicle($trip->vehicle_id);

        return response()->json([
            'trip'     => $trip,
            'vehicle'  => $trip->vehicle ?? null,
            'location' => $location,
        ]);
    }

    public function activeTripsAdmin(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->service->activeTripsForSchool($request->user()->school_id),
        ]);
    }

    public function trackTrip(Request $request, int $tripId): JsonResponse
    {
        $trip = VehicleTrip::where('school_id', $request->user()->school_id)
            ->with('vehicle')
            ->findOrFail($tripId);

        $location = $this->service->getLatestForVehicle($trip->vehicle_id);

        return response()->json(['trip' => $trip, 'location' => $location]);
    }
}

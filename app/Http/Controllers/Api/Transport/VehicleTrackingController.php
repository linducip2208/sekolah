<?php

namespace App\Http\Controllers\Api\Transport;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Facilities\StudentTransport;
use App\Models\Transport\VehicleTrip;
use App\Services\Gate\IdGateService;
use App\Services\Transport\VehicleTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleTrackingController extends Controller
{
    public function __construct(
        private VehicleTrackingService $service,
        private IdGateService $deviceService,
    ) {}

    /**
     * Device GPS push (called by GPS hardware in vehicle).
     * Authenticated via a registered device token in the Authorization: Bearer header.
     * The legacy device_token body field remains accepted for existing hardware clients.
     */
    public function ping(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_token' => 'nullable|string',
            'school_id' => 'required|integer',
            'vehicle_id' => 'required|integer',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'speed_kmh' => 'nullable|numeric|min:0',
            'heading_deg' => 'nullable|numeric|min:0|max:360',
            'recorded_at' => 'nullable|date',
        ]);

        $deviceToken = $data['device_token'] ?? $request->bearerToken();
        if (blank($deviceToken)) {
            return response()->json(['message' => 'Device token is required'], 422);
        }

        $device = $this->deviceService->authenticateDevice($deviceToken);
        if (! $device) {
            return response()->json(['message' => 'Unknown device'], 401);
        }
        if ((int) $device->school_id !== (int) $data['school_id']) {
            return response()->json(['message' => 'Device school mismatch'], 403);
        }

        $location = $this->service->recordPing($data['school_id'], $data['vehicle_id'], $data);

        return response()->json(['ok' => true, 'id' => $location->id]);
    }

    public function busLocationForChild(Request $request, int $studentId): JsonResponse
    {
        $student = Student::where('school_id', $request->user()->school_id)
            ->findOrFail($studentId);

        $transport = StudentTransport::where('student_id', $student->id)
            ->where('is_active', true)
            ->first();

        if (! $transport) {
            return response()->json(['vehicle' => null, 'location' => null]);
        }

        $trip = VehicleTrip::where('transport_route_id', $transport->transport_route_id)
            ->where('school_id', $student->school_id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (! $trip) {
            return response()->json(['vehicle' => null, 'location' => null]);
        }

        $location = $this->service->getLatestForVehicle($trip->vehicle_id, (int) $student->school_id);

        return response()->json([
            'trip' => $trip,
            'vehicle' => $trip->vehicle ?? null,
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

        $location = $this->service->getLatestForVehicle($trip->vehicle_id, (int) $trip->school_id);

        return response()->json(['trip' => $trip, 'location' => $location]);
    }
}

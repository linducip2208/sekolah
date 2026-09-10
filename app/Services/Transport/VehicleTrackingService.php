<?php

namespace App\Services\Transport;

use App\Models\Facilities\TransportRoute;
use App\Models\Facilities\TransportRouteStop;
use App\Models\Facilities\Vehicle;
use App\Models\Transport\VehicleLocation;
use App\Models\Transport\VehicleTrip;

class VehicleTrackingService
{
    public function recordPing(int $schoolId, int $vehicleId, array $data): VehicleLocation
    {
        Vehicle::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->findOrFail($vehicleId);

        return VehicleLocation::create([
            'school_id' => $schoolId,
            'vehicle_id' => $vehicleId,
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'speed_kmh' => $data['speed_kmh'] ?? null,
            'heading_deg' => $data['heading_deg'] ?? null,
            'recorded_at' => $data['recorded_at'] ?? now(),
        ]);
    }

    public function getLatestForVehicle(int $vehicleId, ?int $schoolId = null): ?VehicleLocation
    {
        return VehicleLocation::where('vehicle_id', $vehicleId)
            ->when($schoolId !== null, fn ($query) => $query->where('school_id', $schoolId))
            ->orderByDesc('recorded_at')
            ->first();
    }

    public function startTrip(int $schoolId, int $vehicleId, int $routeId, string $direction): VehicleTrip
    {
        Vehicle::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($vehicleId);
        TransportRoute::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($routeId);

        return VehicleTrip::create([
            'school_id' => $schoolId,
            'vehicle_id' => $vehicleId,
            'transport_route_id' => $routeId,
            'direction' => $direction,
            'started_at' => now(),
            'status' => 'active',
            'stops_completed' => [],
        ]);
    }

    public function completeStop(VehicleTrip $trip, int $stopId, array $studentsOnboard = []): VehicleTrip
    {
        $trip = VehicleTrip::withoutGlobalScopes()
            ->where('school_id', $trip->school_id)
            ->findOrFail($trip->id);
        TransportRouteStop::where('transport_route_id', $trip->transport_route_id)
            ->findOrFail($stopId);
        abort_if($trip->status !== 'active', 422, 'Perjalanan sudah selesai.');

        $stops = $trip->stops_completed ?? [];
        $stops[] = [
            'stop_id' => $stopId,
            'arrived_at' => now()->toIso8601String(),
            'students_onboard' => $studentsOnboard,
        ];
        $trip->update(['stops_completed' => $stops]);

        return $trip->fresh();
    }

    public function endTrip(VehicleTrip $trip): VehicleTrip
    {
        $trip = VehicleTrip::withoutGlobalScopes()
            ->where('school_id', $trip->school_id)
            ->findOrFail($trip->id);
        abort_if($trip->status !== 'active', 422, 'Perjalanan sudah selesai.');

        $trip->update(['ended_at' => now(), 'status' => 'ended']);

        return $trip->fresh();
    }

    public function pruneOldLocations(int $daysToKeep = 7): int
    {
        return VehicleLocation::where('recorded_at', '<', now()->subDays($daysToKeep))->delete();
    }

    public function activeTripsForSchool(int $schoolId)
    {
        return VehicleTrip::where('school_id', $schoolId)
            ->where('status', 'active')
            ->with('vehicle')
            ->get();
    }
}

<?php

namespace App\Services\Transport;

use App\Models\Transport\VehicleLocation;
use App\Models\Transport\VehicleTrip;
use Illuminate\Support\Facades\DB;

class VehicleTrackingService
{
    public function recordPing(int $schoolId, int $vehicleId, array $data): VehicleLocation
    {
        return VehicleLocation::create([
            'school_id'   => $schoolId,
            'vehicle_id'  => $vehicleId,
            'lat'         => $data['lat'],
            'lng'         => $data['lng'],
            'speed_kmh'   => $data['speed_kmh'] ?? null,
            'heading_deg' => $data['heading_deg'] ?? null,
            'recorded_at' => $data['recorded_at'] ?? now(),
        ]);
    }

    public function getLatestForVehicle(int $vehicleId): ?VehicleLocation
    {
        return VehicleLocation::where('vehicle_id', $vehicleId)
            ->orderByDesc('recorded_at')
            ->first();
    }

    public function startTrip(int $schoolId, int $vehicleId, int $routeId, string $direction): VehicleTrip
    {
        return VehicleTrip::create([
            'school_id'         => $schoolId,
            'vehicle_id'        => $vehicleId,
            'transport_route_id'=> $routeId,
            'direction'         => $direction,
            'started_at'        => now(),
            'status'            => 'active',
            'stops_completed'   => [],
        ]);
    }

    public function completeStop(VehicleTrip $trip, int $stopId, array $studentsOnboard = []): VehicleTrip
    {
        $stops = $trip->stops_completed ?? [];
        $stops[] = [
            'stop_id'           => $stopId,
            'arrived_at'        => now()->toIso8601String(),
            'students_onboard'  => $studentsOnboard,
        ];
        $trip->update(['stops_completed' => $stops]);
        return $trip->fresh();
    }

    public function endTrip(VehicleTrip $trip): VehicleTrip
    {
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

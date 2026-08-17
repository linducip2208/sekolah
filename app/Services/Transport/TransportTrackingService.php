<?php

namespace App\Services\Transport;

use App\Models\Transport\VehicleLocation;
use Illuminate\Support\Collection;

class TransportTrackingService
{
    /** Latest GPS position for every vehicle of a school. */
    public function latestLocations(int $schoolId): Collection
    {
        $latestIds = VehicleLocation::where('school_id', $schoolId)
            ->selectRaw('MAX(id) as id')
            ->groupBy('vehicle_id')
            ->pluck('id');

        return VehicleLocation::whereIn('id', $latestIds)
            ->with('vehicle')
            ->orderByDesc('recorded_at')
            ->get();
    }

    /** Return vehicles with a stale GPS signal (no update in N minutes). */
    public function staleVehicles(int $schoolId, int $minutes = 15): Collection
    {
        $cutoff = now()->subMinutes($minutes);

        $latest = $this->latestLocations($schoolId)->keyBy('vehicle_id');

        $all = \App\Models\Facilities\Vehicle::where('school_id', $schoolId)->get();

        return $all->filter(function ($vehicle) use ($latest, $cutoff) {
            $loc = $latest->get($vehicle->id);
            return !$loc || $loc->recorded_at->lt($cutoff);
        })->values();
    }

    /** Distance in meters using haversine. */
    public function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /** ETA in minutes from a moving vehicle to a destination. */
    public function etaMinutes(float $distanceMeters, float $speedKmh): ?int
    {
        if ($speedKmh <= 0) {
            return null;
        }

        return (int) round(($distanceMeters / 1000) / $speedKmh * 60);
    }
}

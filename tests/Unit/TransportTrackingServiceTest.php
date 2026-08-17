<?php

use App\Models\Facilities\Vehicle;
use App\Models\School;
use App\Models\Transport\VehicleLocation;
use App\Services\Transport\TransportTrackingService;

beforeEach(function () {
    $this->service = app(TransportTrackingService::class);
    $this->school = School::factory()->create();
    $this->vehicle = Vehicle::create([
        'school_id' => $this->school->id, 'registration_no' => 'B 1234 XYZ', 'make_model' => 'Hino', 'capacity' => 30,
    ]);
});

it('returns the latest location per vehicle', function () {
    VehicleLocation::create([
        'school_id' => $this->school->id, 'vehicle_id' => $this->vehicle->id,
        'lat' => -6.2, 'lng' => 106.8, 'speed_kmh' => 10, 'recorded_at' => now()->subMinutes(10),
    ]);
    VehicleLocation::create([
        'school_id' => $this->school->id, 'vehicle_id' => $this->vehicle->id,
        'lat' => -6.3, 'lng' => 106.9, 'speed_kmh' => 40, 'recorded_at' => now(),
    ]);

    $latest = $this->service->latestLocations($this->school->id);

    expect($latest)->toHaveCount(1);
    expect((float) $latest->first()->lat)->toBe(-6.3);
    expect((float) $latest->first()->speed_kmh)->toBe(40.0);
});

it('computes haversine distance in meters', function () {
    $distance = $this->service->distanceMeters(-6.2, 106.816, -6.2, 106.817);

    expect($distance)->toBeGreaterThan(90)->toBeLessThan(130);
});

it('computes ETA in minutes from distance and speed', function () {
    expect($this->service->etaMinutes(10000, 50))->toBe(12);
    expect($this->service->etaMinutes(10000, 0))->toBeNull();
});

it('flags vehicles with stale GPS signal', function () {
    VehicleLocation::create([
        'school_id' => $this->school->id, 'vehicle_id' => $this->vehicle->id,
        'lat' => -6.2, 'lng' => 106.8, 'speed_kmh' => 0, 'recorded_at' => now()->subMinutes(30),
    ]);

    expect($this->service->staleVehicles($this->school->id, 15))->toHaveCount(1);
});

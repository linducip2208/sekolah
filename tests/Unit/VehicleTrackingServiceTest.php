<?php

use App\Models\Facilities\Vehicle;
use App\Models\School;
use App\Models\Transport\VehicleLocation;
use App\Services\Transport\VehicleTrackingService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

beforeEach(function () {
    $this->service = app(VehicleTrackingService::class);
    $this->school = School::factory()->create();
    $this->vehicle = Vehicle::create([
        'school_id' => $this->school->id,
        'registration_no' => 'B 4321 XYZ',
        'make_model' => 'Bus',
        'capacity' => 30,
    ]);
});

it('rejects GPS pings for a vehicle owned by another school', function () {
    $foreignSchool = School::factory()->create();
    $foreignVehicle = Vehicle::create([
        'school_id' => $foreignSchool->id,
        'registration_no' => 'B 9876 ABC',
        'make_model' => 'Bus',
        'capacity' => 20,
    ]);

    expect(fn () => $this->service->recordPing($this->school->id, $foreignVehicle->id, [
        'lat' => -6.2,
        'lng' => 106.8,
    ]))->toThrow(ModelNotFoundException::class);
});

it('scopes latest location to the requested school', function () {
    VehicleLocation::create([
        'school_id' => $this->school->id,
        'vehicle_id' => $this->vehicle->id,
        'lat' => -6.2,
        'lng' => 106.8,
        'recorded_at' => now(),
    ]);

    expect($this->service->getLatestForVehicle($this->vehicle->id, $this->school->id)?->school_id)
        ->toBe($this->school->id);
    expect($this->service->getLatestForVehicle($this->vehicle->id, $this->school->id + 999999))
        ->toBeNull();
});

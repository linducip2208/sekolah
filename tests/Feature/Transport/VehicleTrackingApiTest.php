<?php

use App\Models\Facilities\Vehicle;
use App\Models\Gate\IdGateDevice;
use App\Models\School;

it('requires a registered device token and matching school for GPS pings', function () {
    $school = School::factory()->create();
    $foreignSchool = School::factory()->create();
    $vehicle = Vehicle::create([
        'school_id' => $school->id,
        'registration_no' => 'B 1122 GPS',
        'make_model' => 'Bus',
        'capacity' => 30,
    ]);
    $device = new IdGateDevice([
        'school_id' => $school->id,
        'name' => 'GPS Device',
        'location' => 'Bus 1',
        'type' => 'both',
        'is_active' => true,
    ]);
    $device->device_token = 'gps-device-secret';
    $device->save();

    $this->postJson('/api/v1/devices/gps-ping', [
        'school_id' => $school->id,
        'vehicle_id' => $vehicle->id,
        'lat' => -6.2,
        'lng' => 106.8,
    ])->assertUnprocessable();

    $this->postJson('/api/v1/devices/gps-ping', [
        'device_token' => 'unknown-device',
        'school_id' => $school->id,
        'vehicle_id' => $vehicle->id,
        'lat' => -6.2,
        'lng' => 106.8,
    ])->assertUnauthorized();

    $this->postJson('/api/v1/devices/gps-ping', [
        'device_token' => 'gps-device-secret',
        'school_id' => $foreignSchool->id,
        'vehicle_id' => $vehicle->id,
        'lat' => -6.2,
        'lng' => 106.8,
    ])->assertForbidden();

    $this->withToken('gps-device-secret')->postJson('/api/v1/devices/gps-ping', [
        'school_id' => $school->id,
        'vehicle_id' => $vehicle->id,
        'lat' => -6.2,
        'lng' => 106.8,
    ])->assertOk();
});

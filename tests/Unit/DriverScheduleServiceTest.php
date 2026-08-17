<?php

use App\Models\Academic\Student;
use App\Models\Facilities\TransportRoute;
use App\Models\Facilities\Vehicle;
use App\Models\School;
use App\Models\Transport\DriverSchedule;
use App\Models\User;
use App\Services\Transport\DriverScheduleService;

beforeEach(function () {
    $this->service = app(DriverScheduleService::class);
    $this->school = School::factory()->create();
    $this->route = TransportRoute::create([
        'school_id' => $this->school->id, 'name' => 'Rute A', 'is_active' => true,
    ]);
    $this->vehicle = Vehicle::create([
        'school_id' => $this->school->id, 'registration_no' => 'B 1234 XYZ', 'make_model' => 'Hino', 'capacity' => 30,
    ]);
});

it('schedules a driver for a route on a date and shift', function () {
    $schedule = $this->service->schedule(
        $this->school->id, $this->route->id, '2026-08-17', 'morning', $this->vehicle->id, 'Budi'
    );

    expect($schedule->driver_name)->toBe('Budi');
    expect($schedule->vehicle_id)->toBe($this->vehicle->id);

    // Updating same route/date/shift should not duplicate
    $this->service->schedule($this->school->id, $this->route->id, '2026-08-17', 'morning', $this->vehicle->id, 'Andi');
    expect(DriverSchedule::where('transport_route_id', $this->route->id)->count())->toBe(1);
    expect(DriverSchedule::first()->driver_name)->toBe('Andi');
});

it('lists schedules for a date', function () {
    $this->service->schedule($this->school->id, $this->route->id, '2026-08-17', 'morning', $this->vehicle->id, 'Budi');
    $this->service->schedule($this->school->id, $this->route->id, '2026-08-17', 'afternoon', $this->vehicle->id, 'Budi');

    $schedules = $this->service->forDate($this->school->id, '2026-08-17');

    expect($schedules)->toHaveCount(2);
});

it('lists upcoming schedules for a driver', function () {
    $this->service->schedule($this->school->id, $this->route->id, now()->addDay()->toDateString(), 'morning', $this->vehicle->id, 'Budi');
    $this->service->schedule($this->school->id, $this->route->id, now()->subDay()->toDateString(), 'morning', $this->vehicle->id, 'Budi');

    $schedules = $this->service->forDriver($this->school->id, 'Budi');

    expect($schedules)->toHaveCount(1);
});

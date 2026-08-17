<?php

use App\Models\Academic\Student;
use App\Models\Facilities\StudentTransport;
use App\Models\Facilities\TransportRoute;
use App\Models\School;
use App\Models\Transport\TransportAttendance;
use App\Models\User;
use App\Services\Transport\TransportAttendanceService;

beforeEach(function () {
    $this->service = app(TransportAttendanceService::class);
    $this->school = School::factory()->create();
    $this->route = TransportRoute::create([
        'school_id' => $this->school->id, 'name' => 'Rute A', 'is_active' => true,
    ]);
    $user1 = User::factory()->create(['school_id' => $this->school->id]);
    $user2 = User::factory()->create(['school_id' => $this->school->id]);
    $this->student1 = Student::create(['user_id' => $user1->id, 'school_id' => $this->school->id, 'admission_no' => 'T-1']);
    $this->student2 = Student::create(['user_id' => $user2->id, 'school_id' => $this->school->id, 'admission_no' => 'T-2']);

    StudentTransport::create([
        'school_id' => $this->school->id, 'student_id' => $this->student1->id,
        'transport_route_id' => $this->route->id, 'is_active' => true,
    ]);
    StudentTransport::create([
        'school_id' => $this->school->id, 'student_id' => $this->student2->id,
        'transport_route_id' => $this->route->id, 'is_active' => true,
    ]);
});

it('lists active students for a route', function () {
    $students = $this->service->studentsForRoute($this->school->id, $this->route->id);

    expect($students)->toHaveCount(2);
});

it('marks attendance for a route and date', function () {
    $count = $this->service->mark($this->school->id, $this->route->id, '2026-08-17', 'to_school', [
        $this->student1->id => 'present',
        $this->student2->id => 'absent',
    ]);

    expect($count)->toBe(2);
    $this->assertDatabaseHas('transport_attendances', [
        'student_id' => $this->student2->id, 'status' => 'absent', 'direction' => 'to_school',
    ]);
});

it('summarizes attendance for a date', function () {
    $this->service->mark($this->school->id, $this->route->id, '2026-08-17', 'to_school', [
        $this->student1->id => 'present',
        $this->student2->id => 'present',
    ]);

    $summary = $this->service->summary($this->school->id, '2026-08-17');

    expect($summary['present'])->toBe(2);
    expect($summary['absent'])->toBe(0);
});

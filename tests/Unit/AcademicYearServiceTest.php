<?php

use App\Models\Academic\AcademicYear;
use App\Models\Academic\Semester;
use App\Models\School;
use App\Models\User;
use App\Services\Academic\AcademicYearService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

beforeEach(function () {
    $this->service = app(AcademicYearService::class);
    $this->school = School::factory()->create();
    $this->user = User::factory()->create(['school_id' => $this->school->id]);
    $this->actingAs($this->user);
    $this->year = AcademicYear::create([
        'school_id' => $this->school->id,
        'name' => '2026/2027',
        'start_date' => '2026-07-01',
        'end_date' => '2027-06-30',
        'is_active' => false,
    ]);
    $this->semester = Semester::create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->year->id,
        'name' => 'Ganjil',
        'start_date' => '2026-07-01',
        'end_date' => '2026-12-31',
        'is_active' => false,
    ]);
});

it('does not activate an academic year from another school', function () {
    $foreignSchool = School::factory()->create();
    $foreignYear = AcademicYear::create([
        'school_id' => $foreignSchool->id,
        'name' => 'Foreign year',
        'start_date' => '2026-07-01',
        'end_date' => '2027-06-30',
        'is_active' => false,
    ]);

    $this->expectException(ModelNotFoundException::class);
    $this->service->activate($foreignYear->id);
});

it('activates a semester only inside the authenticated school', function () {
    $active = Semester::create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->year->id,
        'name' => 'Genap',
        'start_date' => '2027-01-01',
        'end_date' => '2027-06-30',
        'is_active' => true,
    ]);

    $this->service->activateSemester($this->semester->id);

    expect($this->semester->fresh()->is_active)->toBeTrue()
        ->and($active->fresh()->is_active)->toBeFalse();
});

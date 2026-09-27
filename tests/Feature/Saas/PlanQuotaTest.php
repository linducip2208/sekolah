<?php

use App\Models\Plan;
use App\Models\School;
use App\Models\User;
use App\Services\PlanQuotaService;
use Symfony\Component\HttpKernel\Exception\HttpException;

function quotaSchool(int $maxStudents = 1, int $maxTeachers = 1): School
{
    $plan = Plan::create([
        'name' => 'Mini', 'slug' => 'mini-' . uniqid(), 'price' => 1000000,
        'max_students' => $maxStudents, 'max_teachers' => $maxTeachers,
        'features' => [], 'is_active' => true,
    ]);

    return School::factory()->create(['settings' => [], 'plan_id' => $plan->id, 'is_active' => true]);
}

test('student quota blocks over-enrollment', function () {
    $school = quotaSchool(1, 10);

    $existing = User::factory()->create(['school_id' => $school->id, 'is_active' => true]);
    $existing->assignRole('student');

    try {
        app(PlanQuotaService::class)->assertCanAddStudents($school->id);
        $this->fail('Kuota penuh harus ditolak.');
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(422);
    }
});

test('student quota allows within limit', function () {
    $school = quotaSchool(2, 10);

    $existing = User::factory()->create(['school_id' => $school->id, 'is_active' => true]);
    $existing->assignRole('student');

    app(PlanQuotaService::class)->assertCanAddStudents($school->id);
    expect(true)->toBeTrue();
});

test('teacher quota blocks over-hiring', function () {
    $school = quotaSchool(10, 1);

    $existing = User::factory()->create(['school_id' => $school->id, 'is_active' => true]);
    $existing->assignRole('teacher');

    try {
        app(PlanQuotaService::class)->assertCanAddTeachers($school->id);
        $this->fail('Kuota guru penuh harus ditolak.');
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(422);
    }
});

test('null quota means unlimited', function () {
    $plan = Plan::create([
        'name' => 'Unlimited', 'slug' => 'unlimited-' . uniqid(), 'price' => 0,
        'max_students' => 0, 'max_teachers' => 0,
        'features' => [], 'is_active' => true,
    ]);
    $school = School::factory()->create(['settings' => [], 'plan_id' => $plan->id, 'is_active' => true]);

    app(PlanQuotaService::class)->assertCanAddStudents($school->id);
    app(PlanQuotaService::class)->assertCanAddTeachers($school->id);
    expect(true)->toBeTrue();
});

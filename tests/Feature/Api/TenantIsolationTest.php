<?php

// Tenant isolation: school A actors must never see school B rows.
// Local test DB only (sikadpro_test + RefreshDatabase rollback).

use App\Models\Academic\Student;
use App\Models\School;
use App\Models\User;

function makeAdmin(string $email, School $school): array
{
    $user = User::factory()->create([
        'email' => $email,
        'school_id' => $school->id,
    ]);
    $user->assignRole('admin');
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

test('directory students never leaks other schools', function () {
    $a = School::factory()->create();
    $b = School::factory()->create();
    [$adminA, $tokenA] = makeAdmin('a-iso@test.test', $a);
    $other = User::factory()->create(['email' => 'b-iso@test.test', 'school_id' => $b->id]);
    $other->assignRole('admin');
    Student::factory()->create(['school_id' => $a->id]);
    $outsider = Student::factory()->create(['school_id' => $b->id]);

    $res = $this->getJson('/api/v1/directory/students', ['Authorization' => "Bearer $tokenA"]);
    $res->assertOk();
    $ids = collect($res->json('data'))->pluck('id')->all();

    expect($ids)->not()->toContain($outsider->id)
        ->and(count($ids))->toBeGreaterThan(0);
});

test('staff fee filter cannot reach other school invoice student', function () {
    $a = School::factory()->create();
    $b = School::factory()->create();
    [$adminA, $tokenA] = makeAdmin('a-fee@test.test', $a);
    $stuB = Student::factory()->create(['school_id' => $b->id]);

    $res = $this->getJson(
        '/api/v1/fee/invoices?student_id=' . $stuB->id,
        ['Authorization' => "Bearer $tokenA"]
    );
    $res->assertOk();
    expect($res->json('data'))->toBeEmpty();
});

test('parent cannot read other school child attendance', function () {
    $a = School::factory()->create();
    $b = School::factory()->create();
    $parent = User::factory()->create(['email' => 'p-iso@test.test', 'school_id' => $a->id]);
    $parent->assignRole('parent');
    $stuB = Student::factory()->create(['school_id' => $b->id]);
    $token = $parent->createToken('test')->plainTextToken;

    $res = $this->getJson(
        '/api/v1/parent/children/' . $stuB->id . '/attendance',
        ['Authorization' => "Bearer $token"]
    );
    // Denied either way (403 policy or 404 scope): no data leaks.
    expect($res->status())->toBeIn([403, 404]);
});

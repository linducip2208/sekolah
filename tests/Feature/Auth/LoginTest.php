<?php

use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('user can login with valid credentials', function () {
    $school = School::factory()->create();
    $user   = User::factory()->create([
        'school_id' => $school->id,
        'email'     => 'test@test.com',
        'password'  => Hash::make('password'),
        'is_active' => true,
    ]);
    $user->assignRole('teacher');

    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'test@test.com',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role', 'school_id']]);
});

test('login fails with wrong password', function () {
    User::factory()->create([
        'email'     => 'wrong@test.com',
        'password'  => Hash::make('correct'),
        'is_active' => true,
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email'    => 'wrong@test.com',
        'password' => 'incorrect',
    ])->assertStatus(401);
});

test('inactive user cannot login', function () {
    User::factory()->create([
        'email'     => 'inactive@test.com',
        'password'  => Hash::make('password'),
        'is_active' => false,
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email'    => 'inactive@test.com',
        'password' => 'password',
    ])->assertStatus(401);
});

test('authenticated user can get their profile', function () {
    $school = School::factory()->create();
    $user   = User::factory()->create([
        'school_id' => $school->id,
        'is_active' => true,
    ]);
    $user->assignRole('admin');

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('id', $user->id);
});

test('user can logout', function () {
    $user = User::factory()->create(['is_active' => true]);

    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/auth/logout')
        ->assertOk();

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

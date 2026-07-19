<?php

use App\Models\Plan;
use App\Models\School;

test('school can self-register with free trial', function () {
    Plan::create([
        'name'         => 'Free',
        'slug'         => 'free',
        'price'        => 0,
        'max_students' => 50,
        'max_teachers' => 5,
        'features'     => ['attendance'],
    ]);

    $response = $this->postJson('/api/v1/schools/register', [
        'school_name'            => 'SMA Baru',
        'subdomain'              => 'smabaru',
        'admin_name'             => 'Admin SMA',
        'admin_email'            => 'admin@smabaru.sch.id',
        'admin_password'         => 'Admin1234!',
        'admin_password_confirmation' => 'Admin1234!',
    ]);

    $response->assertStatus(201);
    expect(School::where('subdomain', 'smabaru')->exists())->toBeTrue();
    expect($response->json('expires_at'))->not->toBeNull();
});

test('duplicate subdomain is rejected', function () {
    School::factory()->create(['subdomain' => 'existing', 'settings' => []]);

    $response = $this->postJson('/api/v1/schools/register', [
        'school_name'            => 'Another School',
        'subdomain'              => 'existing',
        'admin_name'             => 'Admin',
        'admin_email'            => 'admin2@school.com',
        'admin_password'         => 'Admin1234!',
        'admin_password_confirmation' => 'Admin1234!',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['subdomain']);
});

<?php

use App\Models\Finance\SubscriptionTransaction;
use App\Models\Plan;
use App\Models\School;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['school_id' => null, 'is_active' => true]);
    $this->superAdmin->assignRole('super_admin');

    $this->plan = Plan::create([
        'name'         => 'Basic',
        'slug'         => 'basic',
        'price'        => 20000000,
        'max_students' => 500,
        'max_teachers' => 50,
        'features'     => ['attendance', 'library'],
    ]);

    $this->school = School::factory()->create([
        'settings'  => [],
        'is_active' => true,
        'plan_id'   => $this->plan->id,
    ]);
});

test('super admin can view dashboard stats', function () {
    Sanctum::actingAs($this->superAdmin);

    $response = $this->getJson('/api/v1/super/dashboard');
    $response->assertOk();

    expect($response->json('overview'))->toHaveKeys([
        'total_schools', 'active_schools', 'suspended_schools',
        'total_students', 'total_teachers', 'total_revenue_this_month_cents',
    ]);
});

test('super admin can list schools', function () {
    Sanctum::actingAs($this->superAdmin);

    $response = $this->getJson('/api/v1/super/schools');
    $response->assertOk();
    expect($response->json('total'))->toBeGreaterThanOrEqual(1);
});

test('super admin can create a school', function () {
    Sanctum::actingAs($this->superAdmin);

    $response = $this->postJson('/api/v1/super/schools', [
        'name'      => 'SMA Negeri 99',
        'subdomain' => 'sman99',
        'email'     => 'info@sman99.sch.id',
    ]);

    $response->assertStatus(201)->assertJsonPath('name', 'SMA Negeri 99');
    expect(School::where('subdomain', 'sman99')->exists())->toBeTrue();
});

test('super admin can suspend a school', function () {
    Sanctum::actingAs($this->superAdmin);

    $response = $this->postJson("/api/v1/super/schools/{$this->school->id}/suspend");
    $response->assertOk();
    $this->school->refresh();
    expect($this->school->is_active)->toBeFalse();
});

test('super admin can activate a suspended school', function () {
    Sanctum::actingAs($this->superAdmin);

    $this->school->update(['is_active' => false]);

    $response = $this->postJson("/api/v1/super/schools/{$this->school->id}/activate");
    $response->assertOk();
    $this->school->refresh();
    expect($this->school->is_active)->toBeTrue();
});

test('non-super-admin cannot access super admin routes', function () {
    $admin = User::factory()->create(['school_id' => $this->school->id, 'is_active' => true]);
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $this->getJson('/api/v1/super/dashboard')->assertStatus(403);
    $this->getJson('/api/v1/super/schools')->assertStatus(403);
});

test('super admin can create a plan', function () {
    Sanctum::actingAs($this->superAdmin);

    $response = $this->postJson('/api/v1/super/plans', [
        'name'         => 'Pro',
        'slug'         => 'pro',
        'price'        => 50000000,
        'max_students' => 2000,
        'features'     => ['attendance', 'library', 'hostel', 'transport'],
    ]);

    $response->assertStatus(201)->assertJsonPath('name', 'Pro');
    expect(Plan::where('slug', 'pro')->exists())->toBeTrue();
});

test('super admin can extend school subscription', function () {
    Sanctum::actingAs($this->superAdmin);

    $expiresAt = now()->addYear()->toDateString();

    $response = $this->postJson("/api/v1/super/schools/{$this->school->id}/subscription/extend", [
        'plan_id'    => $this->plan->id,
        'expires_at' => $expiresAt,
    ]);

    $response->assertOk();
    $this->school->refresh();
    expect($this->school->plan_expires_at->toDateString())->toBe($expiresAt);
});

test('super admin can record subscription payment', function () {
    Sanctum::actingAs($this->superAdmin);

    $response = $this->postJson('/api/v1/super/subscriptions', [
        'school_id'   => $this->school->id,
        'plan_id'     => $this->plan->id,
        'amount'      => 20000000,
        'period_from' => '2025-01-01',
        'period_to'   => '2026-01-01',
    ]);

    $response->assertStatus(201);
    expect(SubscriptionTransaction::withoutGlobalScopes()->where('school_id', $this->school->id)->count())->toBe(1);
});

test('super admin can get revenue analytics', function () {
    Sanctum::actingAs($this->superAdmin);

    $response = $this->getJson('/api/v1/super/analytics/revenue');
    $response->assertOk();
    expect($response->json())->toBeArray();
});

test('super admin can update system config', function () {
    Sanctum::actingAs($this->superAdmin);

    $response = $this->putJson('/api/v1/super/system/config', [
        'app_name'    => 'Sikad Pro Custom',
        'trial_days'  => 30,
    ]);

    $response->assertOk()->assertJsonPath('app_name', 'Sikad Pro Custom')
        ->assertJsonPath('trial_days', 30);
});

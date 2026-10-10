<?php

use App\Models\Plan;
use App\Models\Saas\Coupon;
use App\Models\School;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['school_id' => null, 'is_active' => true]);
    $this->superAdmin->assignRole('super_admin');

    $this->plan = Plan::create([
        'name' => 'Basic', 'slug' => 'basic', 'price' => 20000000,
        'max_students' => 500, 'max_teachers' => 50,
        'features' => ['attendance'], 'is_active' => true,
    ]);

    $this->school = School::factory()->create(['settings' => [], 'is_active' => true]);
});

test('subscription with valid coupon applies discount and records use', function () {
    Sanctum::actingAs($this->superAdmin);

    Coupon::create([
        'code' => 'HEMAT20', 'discount_type' => 'fixed', 'discount_value' => 5000000,
        'max_uses' => 10, 'used_count' => 0, 'is_active' => true,
    ]);

    $response = $this->postJson('/api/v1/super/subscriptions', [
        'school_id' => $this->school->id, 'plan_id' => $this->plan->id,
        'amount' => 200000, 'coupon_code' => 'HEMAT20',
        'period_from' => today()->toDateString(), 'period_to' => today()->addMonth()->toDateString(),
    ]);

    $response->assertCreated()
        ->assertJsonPath('amount', 150000)
        ->assertJsonPath('coupon_code', 'HEMAT20')
        ->assertJsonPath('discount_amount', 50000);

    expect(Coupon::where('code', 'HEMAT20')->first()->used_count)->toBe(1);
});

test('subscription with invalid coupon is rejected', function () {
    Sanctum::actingAs($this->superAdmin);

    $response = $this->postJson('/api/v1/super/subscriptions', [
        'school_id' => $this->school->id, 'plan_id' => $this->plan->id,
        'amount' => 20000000, 'coupon_code' => 'PALSU',
        'period_from' => today()->toDateString(), 'period_to' => today()->addMonth()->toDateString(),
    ]);

    $response->assertStatus(422);
});

test('subscription with exhausted coupon is rejected', function () {
    Sanctum::actingAs($this->superAdmin);

    Coupon::create([
        'code' => 'HABIS', 'discount_type' => 'fixed', 'discount_value' => 1000000,
        'max_uses' => 1, 'used_count' => 1, 'is_active' => true,
    ]);

    $response = $this->postJson('/api/v1/super/subscriptions', [
        'school_id' => $this->school->id, 'plan_id' => $this->plan->id,
        'amount' => 20000000, 'coupon_code' => 'HABIS',
        'period_from' => today()->toDateString(), 'period_to' => today()->addMonth()->toDateString(),
    ]);

    $response->assertStatus(422);
    expect(Coupon::where('code', 'HABIS')->first()->used_count)->toBe(1);
});

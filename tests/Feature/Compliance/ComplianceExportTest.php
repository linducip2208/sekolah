<?php

use App\Models\School;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->school = School::factory()->create();
    $this->admin = User::factory()->create(['school_id' => $this->school->id]);
    Role::firstOrCreate(['name' => 'admin']);
    $this->admin->assignRole('admin');
});

it('downloads compliance exports as CSV', function () {
    $this->actingAs($this->admin)->get(route('admin.exports.leger'))
        ->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $this->actingAs($this->admin)->get(route('admin.exports.dapodik'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.exports.bos'))->assertOk();
});

it('applies coupon on public registration', function () {
    $plan = \App\Models\Plan::create(['name' => 'Pro', 'slug' => 'pro-test-'.uniqid(), 'price' => 10000000, 'max_students' => 500, 'max_teachers' => 50, 'features' => ['all'], 'is_active' => true]);
    $coupon = \App\Models\Saas\Coupon::create(['code' => 'HEMAT20', 'discount_type' => 'percentage', 'discount_value' => 2000, 'is_active' => true]);
    $response = $this->post(route('public.subscription.submit'), [
        'school_name' => 'SDN Kupon', 'subdomain' => 'sdn-kupon-'.uniqid(), 'admin_name' => 'Admin',
        'admin_email' => 'kupon-'.uniqid().'@example.test', 'plan_id' => $plan->id, 'billing_months' => 1,
        'coupon_code' => 'HEMAT20',
    ]);
    $response->assertRedirect();
    expect(\App\Models\Platform\SchoolRegistration::where('coupon_code', 'HEMAT20')->exists())->toBeTrue();
});

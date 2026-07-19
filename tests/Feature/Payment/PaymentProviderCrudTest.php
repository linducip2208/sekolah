<?php

namespace Tests\Feature\Payment;

use App\Models\Payment\PaymentProvider;
use App\Models\Plan;
use App\Models\School;
use App\Models\User;
use Tests\TestCase;

class PaymentProviderCrudTest extends TestCase
{
    public function test_admin_can_create_payment_provider(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/payment-providers', [
                'name'       => 'Test VA Provider',
                'api_format' => 'virtual_account',
                'base_url'   => 'https://api.example.com',
                'api_key'    => 'sk-test-12345',
                'is_sandbox' => true,
                'is_active'  => true,
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'name', 'api_format', 'masked_api_key']);
        $this->assertNotEquals('sk-test-12345', $response->json('masked_api_key'));
    }

    public function test_api_key_is_encrypted_at_rest(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin, 'sanctum')->postJson('/api/v1/admin/payment-providers', [
            'name'       => 'Test',
            'api_format' => 'redirect_checkout',
            'base_url'   => 'https://api.example.com',
            'api_key'    => 'plaintext-secret-key',
        ]);

        $provider = PaymentProvider::where('school_id', $admin->school_id)->first();
        $this->assertNotNull($provider);
        $this->assertEquals('plaintext-secret-key', $provider->api_key);

        $rawValue = \DB::table('payment_providers')->value('api_key_encrypted');
        $this->assertNotEquals('plaintext-secret-key', $rawValue);
    }

    public function test_provider_isolated_per_school(): void
    {
        $adminA = $this->makeAdmin();
        $adminB = $this->makeAdmin();

        $this->actingAs($adminA, 'sanctum')->postJson('/api/v1/admin/payment-providers', [
            'name'       => 'A Provider',
            'api_format' => 'cash',
        ]);

        $response = $this->actingAs($adminB, 'sanctum')->getJson('/api/v1/admin/payment-providers');
        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    protected function makeAdmin(): User
    {
        $plan = Plan::factory()->create();
        $school = School::factory()->create(['plan_id' => $plan->id]);
        $user = User::factory()->create(['school_id' => $school->id]);
        $user->assignRole('admin');
        return $user;
    }
}

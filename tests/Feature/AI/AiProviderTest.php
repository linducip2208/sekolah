<?php

namespace Tests\Feature\AI;

use App\Models\AI\AiProvider;
use App\Models\Plan;
use App\Models\School;
use App\Models\User;
use Tests\TestCase;

class AiProviderTest extends TestCase
{
    public function test_admin_can_create_provider_with_any_format(): void
    {
        $admin = $this->makeAdmin();

        foreach (['openai_compatible', 'anthropic_format', 'gemini_format'] as $format) {
            $response = $this->actingAs($admin, 'sanctum')
                ->postJson('/api/v1/admin/ai/providers', [
                    'name'       => 'AI ' . $format,
                    'api_format' => $format,
                    'base_url'   => 'https://api.example.com',
                    'api_key'    => 'sk-test',
                ]);

            $response->assertStatus(201);
            $this->assertEquals($format, $response->json('api_format'));
        }
    }

    public function test_invalid_api_format_rejected(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/ai/providers', [
                'name'       => 'Bad',
                'api_format' => 'midtrans_format', // not allowed
                'base_url'   => 'https://api.example.com',
            ]);

        $response->assertStatus(422);
    }

    public function test_api_key_encrypted(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin, 'sanctum')->postJson('/api/v1/admin/ai/providers', [
            'name'       => 'Test',
            'api_format' => 'openai_compatible',
            'base_url'   => 'https://api.example.com',
            'api_key'    => 'plaintext',
        ]);

        $raw = \DB::table('ai_providers')->value('api_key_encrypted');
        $this->assertNotEquals('plaintext', $raw);

        $provider = AiProvider::first();
        $this->assertEquals('plaintext', $provider->api_key);
    }

    protected function makeAdmin(): User
    {
        $school = School::factory()->create(['plan_id' => Plan::factory()->create()->id]);
        $user = User::factory()->create(['school_id' => $school->id]);
        $user->assignRole('admin');
        return $user;
    }
}

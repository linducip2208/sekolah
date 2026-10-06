<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_shallow_health_returns_ok(): void
    {
        $response = $this->getJson('/api/v1/health');
        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);
    }

    public function test_deep_health_returns_all_checks(): void
    {
        $response = $this->getJson('/api/v1/health/deep');
        $response->assertStatus(200);
        // app_key presence + raw error strings are intentionally absent
        // (no secret/error disclosure on public endpoints).
        $response->assertJsonStructure([
            'status',
            'checks' => [
                'database',
                'cache',
                'storage',
                'queue',
            ],
            'time',
        ]);
        $response->assertJsonMissing(['app_key' => true]);
    }

    public function test_metrics_returns_counts(): void
    {
        $response = $this->getJson('/api/v1/health/metrics');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'schools_active',
            'users_total',
            'students_total',
            'payments_pending',
            'payments_paid_today',
            'donations_completed_today',
        ]);
    }
}

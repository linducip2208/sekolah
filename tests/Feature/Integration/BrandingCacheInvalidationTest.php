<?php

namespace Tests\Feature\Integration;

use App\Models\Branding\SchoolBranding;
use App\Models\Plan;
use App\Models\School;
use App\Models\User;
use App\Services\Branding\BrandingService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BrandingCacheInvalidationTest extends TestCase
{
    public function test_cache_busted_after_update(): void
    {
        $admin = $this->makeAdmin();
        $svc = app(BrandingService::class);

        // First fetch — populates cache
        $b1 = $svc->getForSchool($admin->school_id);
        $this->assertEquals(1, $b1['cache_version']);

        // Update via API
        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/v1/admin/branding', ['display_name' => 'Updated Name']);

        // Second fetch — must reflect update (cache invalidated)
        $b2 = $svc->getForSchool($admin->school_id);
        $this->assertEquals('Updated Name', $b2['display_name']);
        $this->assertGreaterThan($b1['cache_version'], $b2['cache_version']);
    }

    public function test_cache_isolated_per_school(): void
    {
        $adminA = $this->makeAdmin();
        $adminB = $this->makeAdmin();

        $this->actingAs($adminA, 'sanctum')
            ->putJson('/api/v1/admin/branding', ['display_name' => 'School A']);
        $this->actingAs($adminB, 'sanctum')
            ->putJson('/api/v1/admin/branding', ['display_name' => 'School B']);

        $svc = app(BrandingService::class);
        $bA = $svc->getForSchool($adminA->school_id);
        $bB = $svc->getForSchool($adminB->school_id);

        $this->assertEquals('School A', $bA['display_name']);
        $this->assertEquals('School B', $bB['display_name']);
    }

    protected function makeAdmin(): User
    {
        $school = School::factory()->create(['plan_id' => Plan::factory()->create()->id]);
        $user = User::factory()->create(['school_id' => $school->id]);
        $user->assignRole('admin');
        return $user;
    }
}

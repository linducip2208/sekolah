<?php

namespace Tests\Feature\Branding;

use App\Models\Branding\SchoolBranding;
use App\Models\Plan;
use App\Models\School;
use App\Models\User;
use Tests\TestCase;

class BrandingTest extends TestCase
{
    public function test_admin_can_update_branding_colors(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson('/api/v1/admin/branding', [
                'display_name'   => 'SMK Test',
                'color_primary'  => '#FF0000',
                'color_secondary'=> '#00FF00',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('#FF0000', $response->json('data.colors.primary'));
        $this->assertEquals('SMK Test', $response->json('data.display_name'));
    }

    public function test_branding_cache_version_increments_on_update(): void
    {
        $admin = $this->makeAdmin();

        $branding = SchoolBranding::firstOrCreate(['school_id' => $admin->school_id]);
        $initial  = $branding->cache_version;

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/v1/admin/branding', ['display_name' => 'Updated']);

        $branding->refresh();
        $this->assertGreaterThan($initial, $branding->cache_version);
    }

    public function test_invalid_color_format_rejected(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson('/api/v1/admin/branding', [
                'color_primary' => 'not-a-color',
            ]);

        $response->assertStatus(422);
    }

    public function test_public_branding_lookup_by_subdomain(): void
    {
        $school = School::factory()->create([
            'plan_id'    => Plan::factory()->create()->id,
            'subdomain'  => 'test-school',
        ]);

        $response = $this->getJson('/api/v1/branding/test-school');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['colors', 'logos', 'cache_version']]);
    }

    protected function makeAdmin(): User
    {
        $school = School::factory()->create(['plan_id' => Plan::factory()->create()->id]);
        $user = User::factory()->create(['school_id' => $school->id]);
        $user->assignRole('admin');
        return $user;
    }
}

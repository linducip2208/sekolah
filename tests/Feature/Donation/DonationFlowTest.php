<?php

namespace Tests\Feature\Donation;

use App\Models\Donation\DonationCampaign;
use App\Models\Plan;
use App\Models\School;
use Tests\TestCase;

class DonationFlowTest extends TestCase
{
    public function test_public_can_view_active_public_campaigns(): void
    {
        $school = School::factory()->create(['plan_id' => Plan::factory()->create()->id, 'subdomain' => 'donate-test']);

        DonationCampaign::create([
            'school_id'     => $school->id,
            'title'         => 'Renovasi Mushola',
            'slug'          => 'renovasi-mushola',
            'description'   => 'Mari berdonasi untuk renovasi',
            'target_amount' => 10_000_000,
            'start_date'    => today(),
            'end_date'      => today()->addMonth(),
            'status'        => 'active',
            'is_public'     => true,
        ]);

        DonationCampaign::create([
            'school_id'     => $school->id,
            'title'         => 'Draft',
            'slug'          => 'draft',
            'description'   => 'd',
            'target_amount' => 1_000_000,
            'start_date'    => today(),
            'end_date'      => today()->addMonth(),
            'status'        => 'draft',
            'is_public'     => false,
        ]);

        $response = $this->getJson('/api/v1/public/donations/donate-test/campaigns');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Renovasi Mushola', $response->json('data.0.title'));
    }

    public function test_public_can_donate_anonymously(): void
    {
        $school = School::factory()->create(['plan_id' => Plan::factory()->create()->id, 'subdomain' => 'donate-anon']);

        $campaign = DonationCampaign::create([
            'school_id'     => $school->id,
            'title'         => 'Test',
            'slug'          => 'test-campaign',
            'description'   => 'd',
            'target_amount' => 1_000_000,
            'start_date'    => today(),
            'end_date'      => today()->addMonth(),
            'status'        => 'active',
            'is_public'     => true,
        ]);

        $response = $this->postJson('/api/v1/public/donations/donate-anon/campaigns/test-campaign/donate', [
            'donor_name'   => 'John Doe',
            'donor_email'  => 'john@example.com',
            'is_anonymous' => true,
            'amount'       => 50_000,
            'message'      => 'Semoga bermanfaat',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('donations', [
            'donor_name'   => 'John Doe',
            'is_anonymous' => true,
            'amount'       => 50_000,
        ]);
    }
}

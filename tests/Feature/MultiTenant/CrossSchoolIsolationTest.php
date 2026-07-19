<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Achievement\AchievementCategory;
use App\Models\Donation\DonationCampaign;
use App\Models\Payment\PaymentProvider;
use App\Models\Plan;
use App\Models\School;
use App\Models\User;
use Tests\TestCase;

class CrossSchoolIsolationTest extends TestCase
{
    public function test_payment_provider_isolated(): void
    {
        [$adminA, $adminB] = $this->makeTwoAdmins();

        PaymentProvider::create([
            'school_id'  => $adminA->school_id,
            'name'       => 'A Provider',
            'slug'       => 'a-provider',
            'api_format' => 'cash',
            'is_active'  => true,
        ]);

        $resA = $this->actingAs($adminA, 'sanctum')->getJson('/api/v1/admin/payment-providers');
        $resB = $this->actingAs($adminB, 'sanctum')->getJson('/api/v1/admin/payment-providers');

        $this->assertCount(1, $resA->json('data'));
        $this->assertCount(0, $resB->json('data'));
    }

    public function test_donation_campaign_isolated(): void
    {
        [$adminA, $adminB] = $this->makeTwoAdmins();

        DonationCampaign::create([
            'school_id'     => $adminA->school_id,
            'title'         => 'A Campaign',
            'slug'          => 'a-campaign',
            'description'   => 'desc',
            'target_amount' => 1000000,
            'start_date'    => today(),
            'end_date'      => today()->addMonth(),
            'status'        => 'active',
            'is_public'     => true,
        ]);

        $resA = $this->actingAs($adminA, 'sanctum')->getJson('/api/v1/admin/donations/campaigns');
        $resB = $this->actingAs($adminB, 'sanctum')->getJson('/api/v1/admin/donations/campaigns');

        $this->assertCount(1, $resA->json('data.data'));
        $this->assertCount(0, $resB->json('data.data'));
    }

    public function test_achievement_categories_isolated(): void
    {
        [$adminA, $adminB] = $this->makeTwoAdmins();

        AchievementCategory::create([
            'school_id' => $adminA->school_id,
            'name'      => 'Olimpiade Sains',
            'scope'     => 'national',
            'points'    => 50,
        ]);

        $resA = $this->actingAs($adminA, 'sanctum')->getJson('/api/v1/achievements/categories');
        $resB = $this->actingAs($adminB, 'sanctum')->getJson('/api/v1/achievements/categories');

        $this->assertCount(1, $resA->json('data'));
        $this->assertCount(0, $resB->json('data'));
    }

    /** @return array{0:User,1:User} */
    protected function makeTwoAdmins(): array
    {
        $plan = Plan::factory()->create();
        $schoolA = School::factory()->create(['plan_id' => $plan->id]);
        $schoolB = School::factory()->create(['plan_id' => $plan->id]);

        $userA = User::factory()->create(['school_id' => $schoolA->id]);
        $userA->assignRole('admin');

        $userB = User::factory()->create(['school_id' => $schoolB->id]);
        $userB->assignRole('admin');

        return [$userA, $userB];
    }
}

<?php

namespace Tests\Feature\Integration;

use App\Models\Donation\DonationCampaign;
use App\Models\Plan;
use App\Models\School;
use Tests\TestCase;

class ProgrammaticSeoTest extends TestCase
{
    public function test_sitemap_xml_returns_valid_xml(): void
    {
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $this->assertStringContainsString('<?xml', $response->getContent());
        $this->assertStringContainsString('<urlset', $response->getContent());
    }

    public function test_robots_txt_includes_sitemap(): void
    {
        $response = $this->get('/robots.txt');
        $response->assertStatus(200);
        $this->assertStringContainsString('Sitemap:', $response->getContent());
        $this->assertStringContainsString('Disallow: /admin/', $response->getContent());
        $this->assertStringContainsString('Disallow: /api/', $response->getContent());
    }

    public function test_donation_landing_renders_with_jsonld(): void
    {
        $school = School::factory()->create([
            'plan_id'   => Plan::factory()->create()->id,
            'subdomain' => 'seo-school',
        ]);

        DonationCampaign::create([
            'school_id'     => $school->id,
            'title'         => 'Renovasi Mushola',
            'slug'          => 'renovasi-mushola-seo',
            'description'   => 'Kampanye renovasi mushola sekolah.',
            'target_amount' => 10_000_000_00,
            'raised_amount' => 0,
            'start_date'    => today()->subDay(),
            'end_date'      => today()->addMonth(),
            'category'      => 'building',
            'status'        => 'active',
            'is_public'     => true,
        ]);

        $response = $this->get('/donate/seo-school/renovasi-mushola-seo');
        $response->assertStatus(200);
        $this->assertStringContainsString('application/ld+json', $response->getContent());
        $this->assertStringContainsString('Renovasi Mushola', $response->getContent());
        $this->assertStringContainsString('schema.org', $response->getContent());
    }
}

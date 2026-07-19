<?php

namespace Tests\Feature\PPDB;

use App\Models\Plan;
use App\Models\PPDB\PpdbApplication;
use App\Models\PPDB\PpdbPeriod;
use App\Models\School;
use Tests\TestCase;

class PpdbRegistrationTest extends TestCase
{
    public function test_public_can_view_published_periods(): void
    {
        $school = $this->makeSchool('test-school');

        PpdbPeriod::create([
            'school_id'        => $school->id,
            'academic_year_id' => 1,
            'name'             => 'PPDB 2025/2026',
            'open_date'        => now()->subDay(),
            'close_date'       => now()->addMonth(),
            'is_published'     => true,
        ]);

        PpdbPeriod::create([
            'school_id'        => $school->id,
            'academic_year_id' => 1,
            'name'             => 'Draft',
            'open_date'        => now(),
            'close_date'       => now()->addMonth(),
            'is_published'     => false,
        ]);

        $response = $this->getJson('/api/v1/public/ppdb/test-school/periods');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_public_can_register(): void
    {
        $school = $this->makeSchool('register-test');
        $period = PpdbPeriod::create([
            'school_id'        => $school->id,
            'academic_year_id' => 1,
            'name'             => 'PPDB 2025/2026',
            'open_date'        => now()->subDay(),
            'close_date'       => now()->addMonth(),
            'is_published'     => true,
        ]);

        $response = $this->postJson('/api/v1/public/ppdb/register-test/register', [
            'ppdb_period_id' => $period->id,
            'jalur'          => 'reguler',
            'student_name'   => 'Budi Santoso',
            'date_of_birth'  => '2010-05-15',
            'gender'         => 'male',
            'address'        => 'Jl. Mawar No. 1',
            'district'       => 'Cilandak',
            'city'           => 'Jakarta',
            'parent_name'    => 'Ayah Budi',
            'parent_phone'   => '081234567890',
            'parent_email'   => 'ayah@example.com',
        ]);

        $response->assertStatus(201);
        $this->assertEquals('Budi Santoso', $response->json('student_name'));
        $this->assertStringStartsWith('PPDB-', $response->json('registration_no'));
        $this->assertDatabaseHas('ppdb_applications', [
            'student_name' => 'Budi Santoso',
            'school_id'    => $school->id,
        ]);
    }

    protected function makeSchool(string $subdomain): School
    {
        return School::factory()->create([
            'plan_id'   => Plan::factory()->create()->id,
            'subdomain' => $subdomain,
        ]);
    }
}

<?php

namespace Tests\Feature\DailyReport;

use App\Models\Academic\Student;
use App\Models\DailyReport\DailyReport;
use App\Models\Plan;
use App\Models\School;
use App\Models\User;
use App\Services\DailyReport\DailyReportService;
use Tests\TestCase;

class DailyReportTest extends TestCase
{
    public function test_generates_report_for_student(): void
    {
        $school = School::factory()->create(['plan_id' => Plan::factory()->create()->id]);
        $student = Student::factory()->create(['school_id' => $school->id]);

        $service = app(DailyReportService::class);
        $report = $service->generateForStudent($school->id, $student->id, today()->toDateString());

        $this->assertInstanceOf(DailyReport::class, $report);
        $this->assertEquals(today()->toDateString(), $report->report_date->toDateString());
    }

    public function test_admin_can_trigger_bulk_generate(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/daily-reports/generate', [
                'date' => today()->toDateString(),
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['count']);
    }

    protected function makeAdmin(): User
    {
        $school = School::factory()->create(['plan_id' => Plan::factory()->create()->id]);
        $user = User::factory()->create(['school_id' => $school->id]);
        $user->assignRole('admin');
        return $user;
    }
}

<?php

namespace Tests\Feature\PPDB;

use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Medium;
use App\Models\Academic\Section;
use App\Models\Finance\FeeInvoice;
use App\Models\Plan;
use App\Models\PPDB\PpdbApplication;
use App\Models\PPDB\PpdbPeriod;
use App\Models\School;
use App\Models\User;
use App\Services\PPDB\PpdbService;
use Tests\TestCase;

class PpdbEnrollInvoiceTest extends TestCase
{
    public function test_enroll_creates_reregistration_invoice_when_form_fee_set(): void
    {
        $school = School::factory()->create(['plan_id' => Plan::factory()->create()->id]);
        $admin = User::factory()->create(['school_id' => $school->id]);
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $year = AcademicYear::factory()->create(['school_id' => $school->id]);
        $medium = Medium::create(['school_id' => $school->id, 'name' => 'Umum']);
        $room = ClassRoom::create(['school_id' => $school->id, 'medium_id' => $medium->id, 'name' => '7']);
        $section = Section::create(['school_id' => $school->id, 'name' => 'A']);
        $classSection = ClassSection::create([
            'school_id' => $school->id, 'class_room_id' => $room->id,
            'section_id' => $section->id, 'medium_id' => $medium->id,
            'academic_year_id' => $year->id,
        ]);

        $period = PpdbPeriod::create([
            'school_id' => $school->id, 'academic_year_id' => $year->id,
            'name' => 'PPDB 2026', 'open_date' => now()->subDay(), 'close_date' => now()->addMonth(),
            'is_published' => true, 'form_fee' => 25000000,
        ]);

        $service = app(PpdbService::class);
        $app = $service->register($period, [
            'jalur' => 'reguler', 'student_name' => 'Siti Aminah',
            'date_of_birth' => '2011-03-10', 'gender' => 'female',
            'address' => 'Jl. Melati 2', 'district' => 'Kebayoran', 'city' => 'Jakarta',
            'parent_name' => 'Ibu Siti', 'parent_phone' => '0811111111', 'parent_email' => 'ibu@example.com',
        ]);
        $service->submit($app);
        $service->verify($app, $admin->id);
        $service->accept($app, $admin->id);

        $student = $service->enrollStudent($app, $classSection->id, null, $admin->id);

        $this->assertDatabaseHas('fee_invoices', [
            'school_id' => $school->id,
            'student_id' => $student->id,
            'amount' => 25000000,
            'status' => 'unpaid',
        ]);
        $this->assertDatabaseHas('fee_structures', [
            'school_id' => $school->id,
            'name' => 'Biaya Daftar Ulang PPDB',
            'frequency' => 'one-time',
        ]);
    }

    public function test_enroll_skips_invoice_when_no_form_fee(): void
    {
        $school = School::factory()->create(['plan_id' => Plan::factory()->create()->id]);
        $admin = User::factory()->create(['school_id' => $school->id]);
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $year = AcademicYear::factory()->create(['school_id' => $school->id]);
        $medium = Medium::create(['school_id' => $school->id, 'name' => 'Umum']);
        $room = ClassRoom::create(['school_id' => $school->id, 'medium_id' => $medium->id, 'name' => '7']);
        $section = Section::create(['school_id' => $school->id, 'name' => 'A']);
        $classSection = ClassSection::create([
            'school_id' => $school->id, 'class_room_id' => $room->id,
            'section_id' => $section->id, 'medium_id' => $medium->id,
            'academic_year_id' => $year->id,
        ]);

        $period = PpdbPeriod::create([
            'school_id' => $school->id, 'academic_year_id' => $year->id,
            'name' => 'PPDB Gratis', 'open_date' => now()->subDay(), 'close_date' => now()->addMonth(),
            'is_published' => true, 'form_fee' => 0,
        ]);

        $service = app(PpdbService::class);
        $app = $service->register($period, [
            'jalur' => 'reguler', 'student_name' => 'Andi',
            'date_of_birth' => '2011-01-01', 'gender' => 'male',
            'address' => 'Jl. Kenanga', 'district' => 'Pancoran', 'city' => 'Jakarta',
            'parent_name' => 'Bapak Andi', 'parent_phone' => '0822222222', 'parent_email' => 'bapak@example.com',
        ]);
        $service->submit($app);
        $service->verify($app, $admin->id);
        $service->accept($app, $admin->id);

        $student = $service->enrollStudent($app, $classSection->id, null, $admin->id);

        $this->assertDatabaseMissing('fee_invoices', ['student_id' => $student->id]);
    }
}

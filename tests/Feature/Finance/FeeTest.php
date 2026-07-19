<?php

use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Medium;
use App\Models\Academic\Section;
use App\Models\Academic\Student;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeeStructure;
use App\Models\School;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->school = School::factory()->create(['settings' => []]);
    app()->instance('current_school', $this->school);

    $this->admin = User::factory()->create(['school_id' => $this->school->id, 'is_active' => true]);
    $this->admin->assignRole('admin');

    $academicYear = AcademicYear::create([
        'school_id'  => $this->school->id,
        'name'       => '2024/2025',
        'start_date' => '2024-07-01',
        'end_date'   => '2025-06-30',
        'is_active'  => true,
    ]);

    $medium    = Medium::create(['school_id' => $this->school->id, 'name' => 'Indonesia']);
    $classRoom = ClassRoom::create(['school_id' => $this->school->id, 'medium_id' => $medium->id, 'name' => 'Kelas 7']);
    $section   = Section::create(['school_id' => $this->school->id, 'name' => 'A']);

    $teacher = User::factory()->create(['school_id' => $this->school->id]);
    $classSection = ClassSection::create([
        'school_id'        => $this->school->id,
        'class_room_id'    => $classRoom->id,
        'section_id'       => $section->id,
        'medium_id'        => $medium->id,
        'academic_year_id' => $academicYear->id,
        'class_teacher_id' => $teacher->id,
    ]);

    $studentUser  = User::factory()->create(['school_id' => $this->school->id]);
    $this->student = Student::create([
        'user_id'          => $studentUser->id,
        'school_id'        => $this->school->id,
        'class_section_id' => $classSection->id,
    ]);
});

test('admin can create fee structure', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->postJson('/api/v1/fee/structures', [
        'name'      => 'SPP Bulanan',
        'frequency' => 'monthly',
        'amount'    => 20000000, // 200.000 cents
    ]);

    $response->assertStatus(201)->assertJsonPath('name', 'SPP Bulanan');
});

test('monthly invoices are generated for all students', function () {
    Sanctum::actingAs($this->admin);

    FeeStructure::create([
        'school_id' => $this->school->id,
        'name'      => 'SPP',
        'frequency' => 'monthly',
        'amount'    => 20000000,
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/v1/fee/generate-monthly', ['period' => '2025-01']);
    $response->assertOk()->assertJsonPath('generated', 1);

    expect(FeeInvoice::count())->toBe(1);
});

test('payment is recorded and invoice status updates', function () {
    Sanctum::actingAs($this->admin);

    $structure = FeeStructure::create([
        'school_id' => $this->school->id,
        'name'      => 'SPP',
        'frequency' => 'monthly',
        'amount'    => 20000000,
        'is_active' => true,
    ]);

    $invoice = FeeInvoice::create([
        'school_id'        => $this->school->id,
        'student_id'       => $this->student->id,
        'fee_structure_id' => $structure->id,
        'invoice_no'       => 'INV-001',
        'due_date'         => today()->addDays(30),
        'amount'           => 20000000,
        'status'           => 'unpaid',
        'period'           => '2025-01',
    ]);

    $response = $this->postJson("/api/v1/fee/invoices/{$invoice->id}/pay", [
        'amount'         => 20000000,
        'payment_method' => 'transfer',
    ]);

    $response->assertOk();
    $invoice->refresh();
    expect($invoice->status)->toBe('paid');
});

<?php

use App\Models\Academic\Student;
use App\Models\Communication\Conversation;
use App\Models\Facilities\Hostel;
use App\Models\Facilities\HostelRoom;
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

    $this->teacher = User::factory()->create(['school_id' => $this->school->id, 'is_active' => true]);
    $this->teacher->assignRole('teacher');

    $studentUserA = User::factory()->create(['school_id' => $this->school->id, 'is_active' => true]);
    $studentUserA->assignRole('student');
    $this->studentUserA = $studentUserA;
    $this->studentA = Student::create(['user_id' => $studentUserA->id, 'school_id' => $this->school->id]);

    $studentUserB = User::factory()->create(['school_id' => $this->school->id, 'is_active' => true]);
    $studentUserB->assignRole('student');
    $this->studentB = Student::create(['user_id' => $studentUserB->id, 'school_id' => $this->school->id]);

    $this->structure = FeeStructure::create([
        'school_id' => $this->school->id, 'name' => 'SPP', 'frequency' => 'monthly',
        'amount' => 10000000, 'is_active' => true,
    ]);
});

test('non-participant cannot read conversation messages (403)', function () {
    Sanctum::actingAs($this->admin);

    $conversation = Conversation::create([
        'school_id' => $this->school->id,
        'user_one' => min($this->admin->id, $this->teacher->id),
        'user_two' => max($this->admin->id, $this->teacher->id),
    ]);

    Sanctum::actingAs($this->studentUserA);
    $this->getJson("/api/v1/chat/conversations/{$conversation->id}/messages")->assertForbidden();
});

test('non-participant cannot send to conversation (403)', function () {
    Sanctum::actingAs($this->admin);

    $conversation = Conversation::create([
        'school_id' => $this->school->id,
        'user_one' => min($this->admin->id, $this->teacher->id),
        'user_two' => max($this->admin->id, $this->teacher->id),
    ]);

    Sanctum::actingAs($this->studentUserA);
    $this->postJson("/api/v1/chat/conversations/{$conversation->id}/send", [
        'body' => 'menyusup',
    ])->assertForbidden();
});

test('cannot start conversation with user from another school (404)', function () {
    $otherSchool = School::factory()->create(['settings' => []]);
    $outsider = User::factory()->create(['school_id' => $otherSchool->id, 'is_active' => true]);

    Sanctum::actingAs($this->admin);
    $this->postJson('/api/v1/chat/conversations', [
        'recipient_id' => $outsider->id,
    ])->assertNotFound();
});

test('conversation list only shows own conversations', function () {
    Conversation::create([
        'school_id' => $this->school->id,
        'user_one' => min($this->admin->id, $this->teacher->id),
        'user_two' => max($this->admin->id, $this->teacher->id),
    ]);

    Sanctum::actingAs($this->studentUserA);
    $this->getJson('/api/v1/chat/conversations')->assertOk()->assertJsonCount(0);
});

test('student invoice list is scoped to own student (IDOR guard)', function () {
    FeeInvoice::create([
        'school_id' => $this->school->id, 'student_id' => $this->studentA->id,
        'fee_structure_id' => $this->structure->id, 'invoice_no' => 'INV-IDOR-A',
        'due_date' => today()->addDays(30), 'amount' => 10000000, 'status' => 'unpaid', 'period' => '2026-01',
    ]);
    FeeInvoice::create([
        'school_id' => $this->school->id, 'student_id' => $this->studentB->id,
        'fee_structure_id' => $this->structure->id, 'invoice_no' => 'INV-IDOR-B',
        'due_date' => today()->addDays(30), 'amount' => 10000000, 'status' => 'unpaid', 'period' => '2026-01',
    ]);

    Sanctum::actingAs($this->studentUserA);
    $response = $this->getJson('/api/v1/fee/invoices?student_id='.$this->studentB->id);
    $response->assertOk()->assertJsonCount(1)->assertJsonPath('0.invoice_no', 'INV-IDOR-A');
});

test('student cannot record invoice payment (403)', function () {
    $invoice = FeeInvoice::create([
        'school_id' => $this->school->id, 'student_id' => $this->studentA->id,
        'fee_structure_id' => $this->structure->id, 'invoice_no' => 'INV-IDOR-C',
        'due_date' => today()->addDays(30), 'amount' => 10000000, 'status' => 'unpaid', 'period' => '2026-01',
    ]);

    Sanctum::actingAs($this->studentUserA);
    $this->postJson("/api/v1/fee/invoices/{$invoice->id}/pay", [
        'amount' => 1000000, 'payment_method' => 'cash',
    ])->assertForbidden();
});

test('hostel rooms endpoint returns rooms and blocks other school (404)', function () {
    Sanctum::actingAs($this->admin);

    $hostel = Hostel::create(['school_id' => $this->school->id, 'name' => 'Asrama A', 'type' => 'boys']);
    HostelRoom::create(['hostel_id' => $hostel->id, 'room_no' => 'R1', 'capacity' => 4, 'occupied' => 0]);

    $this->getJson("/api/v1/hostel/{$hostel->id}/rooms")->assertOk()->assertJsonPath('0.room_no', 'R1');

    $otherSchool = School::factory()->create(['settings' => []]);
    $foreign = Hostel::withoutGlobalScopes()->create(
        ['school_id' => $otherSchool->id, 'name' => 'Asrama X', 'type' => 'girls']
    );
    $this->getJson("/api/v1/hostel/{$foreign->id}/rooms")->assertNotFound();
});

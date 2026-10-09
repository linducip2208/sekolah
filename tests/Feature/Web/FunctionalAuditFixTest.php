<?php

// Regression tests for the full-system functional audit fixes:
// dead nav route, procurement/emergency route shadowing, money atomicity,
// scoped exists rules, cooperative statement page, refund payment scoping.

use App\Models\Academic\ClassSection;
use App\Models\Academic\Student;
use App\Models\Finance\BudgetCategory;
use App\Models\Finance\BudgetItem;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\CooperativeMember;
use App\Models\Finance\CooperativeSaving;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeePayment;
use App\Models\Finance\FeeStructure;
use App\Models\Finance\JournalEntry;
use App\Models\School;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->school = School::factory()->create();
    $this->otherSchool = School::factory()->create();
    Role::firstOrCreate(['name' => 'admin']);
    $this->admin = User::factory()->create(['school_id' => $this->school->id]);
    $this->admin->assignRole('admin');
});

test('Tugas nav route resolves and renders assignment list', function () {
    expect(route('admin.classroom.assignments.index'))->toBeString();

    $this->actingAs($this->admin)
        ->get(route('admin.classroom.assignments.index'))
        ->assertOk();
});

test('procurement approvals and suppliers pages are reachable (no shadowing)', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.procurement.approvals'))
        ->assertOk();

    $this->actingAs($this->admin)
        ->get(route('admin.procurement.suppliers'))
        ->assertOk();
});

test('emergency contacts page is reachable (no shadowing)', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.emergency.contacts'))
        ->assertOk();
});

test('recordPayment writes payment, invoice status and journal atomically', function () {
    ChartOfAccount::create(['school_id' => $this->school->id, 'code' => '1000', 'name' => 'Kas', 'type' => 'asset', 'normal_balance' => 'debit', 'is_active' => true]);
    ChartOfAccount::create(['school_id' => $this->school->id, 'code' => '4000', 'name' => 'Pendapatan', 'type' => 'revenue', 'normal_balance' => 'credit', 'is_active' => true]);

    $structure = FeeStructure::create([
        'school_id' => $this->school->id, 'name' => 'SPP', 'frequency' => 'monthly',
        'amount' => 10000000, 'is_active' => true,
    ]);
    $studentUser = User::factory()->create(['school_id' => $this->school->id]);
    $student = Student::create(['user_id' => $studentUser->id, 'school_id' => $this->school->id]);
    $invoice = FeeInvoice::create([
        'school_id' => $this->school->id, 'student_id' => $student->id,
        'fee_structure_id' => $structure->id, 'invoice_no' => 'INV-AUDIT-1',
        'due_date' => now()->toDateString(), 'amount' => 10000000,
        'paid_amount' => 0, 'status' => 'unpaid', 'period' => '2026-01',
    ]);

    $this->actingAs($this->admin)->post(route('admin.fee.invoices.pay', $invoice), [
        'amount_rupiah' => 50000,
        'payment_method' => 'cash',
        'payment_date' => now()->toDateString(),
        'reference' => 'REF-AUDIT-1',
    ])->assertRedirect();

    expect(FeePayment::where('fee_invoice_id', $invoice->id)->count())->toBe(1);
    expect($invoice->fresh()->status)->toBe('partial');
    expect(JournalEntry::where('school_id', $this->school->id)->where('reference_no', 'REF-AUDIT-1')->count())->toBe(1);
});

test('cooperative storeSaving rejects foreign member and records own member', function () {
    $foreignUser = User::factory()->create(['school_id' => $this->otherSchool->id]);
    $foreign = CooperativeMember::create([
        'school_id' => $this->otherSchool->id, 'memberable_type' => User::class,
        'memberable_id' => $foreignUser->id, 'member_number' => 'F-1',
        'join_date' => now()->toDateString(), 'total_savings' => 0, 'status' => 'active',
    ]);

    $this->actingAs($this->admin)->post(route('admin.cooperative.savings.store'), [
        'cooperative_member_id' => $foreign->id,
        'transaction_date' => now()->toDateString(),
        'amount' => 50000,
        'savings_type' => 'wajib',
        'transaction_type' => 'deposit',
    ])->assertInvalid('cooperative_member_id');

    expect(CooperativeSaving::count())->toBe(0);

    $ownUser = User::factory()->create(['school_id' => $this->school->id]);
    $own = CooperativeMember::create([
        'school_id' => $this->school->id, 'memberable_type' => User::class,
        'memberable_id' => $ownUser->id, 'member_number' => 'M-1',
        'join_date' => now()->toDateString(), 'total_savings' => 0, 'status' => 'active',
    ]);

    $this->actingAs($this->admin)->post(route('admin.cooperative.savings.store'), [
        'cooperative_member_id' => $own->id,
        'transaction_date' => now()->toDateString(),
        'amount' => 50000,
        'savings_type' => 'wajib',
        'transaction_type' => 'deposit',
    ])->assertRedirect();

    expect(CooperativeSaving::where('cooperative_member_id', $own->id)->count())->toBe(1);
    expect($own->fresh()->total_savings)->toBe(50000);
});

test('cooperative member statement renders dedicated page', function () {
    $stmtUser = User::factory()->create(['school_id' => $this->school->id]);
    $member = CooperativeMember::create([
        'school_id' => $this->school->id, 'memberable_type' => User::class,
        'memberable_id' => $stmtUser->id, 'member_number' => 'M-2',
        'join_date' => now()->toDateString(), 'total_savings' => 0, 'status' => 'active',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.cooperative.members.statement', $member))
        ->assertOk()
        ->assertSee('M-2');
});

test('budget storeTransaction rejects foreign item; deleteCategory reports error', function () {
    $foreignCat = BudgetCategory::create([
        'school_id' => $this->otherSchool->id, 'name' => 'X', 'code' => 'X', 'type' => 'expense',
    ]);
    $foreignItem = BudgetItem::create([
        'school_id' => $this->otherSchool->id, 'budget_category_id' => $foreignCat->id,
        'name' => 'XI', 'planned_amount' => 1000, 'actual_amount' => 0, 'status' => 'planned',
    ]);

    $this->actingAs($this->admin)->post(route('admin.budget.transactions.store'), [
        'budget_item_id' => $foreignItem->id,
        'transaction_date' => now()->toDateString(),
        'amount_rp' => 10000,
    ])->assertInvalid('budget_item_id');

    $cat = BudgetCategory::create([
        'school_id' => $this->school->id, 'name' => 'OP', 'code' => 'OP', 'type' => 'expense',
    ]);
    $item = BudgetItem::create([
        'school_id' => $this->school->id, 'budget_category_id' => $cat->id,
        'name' => 'OPI', 'planned_amount' => 100000, 'actual_amount' => 0, 'status' => 'planned',
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.budget.categories.destroy', $cat))
        ->assertSessionHasErrors();
    expect(BudgetCategory::whereKey($cat->id)->exists())->toBeTrue();
});

test('QR generate rejects foreign class section', function () {
    $medium = \App\Models\Academic\Medium::create(['school_id' => $this->otherSchool->id, 'name' => 'M']);
    $room = \App\Models\Academic\ClassRoom::create(['school_id' => $this->otherSchool->id, 'medium_id' => $medium->id, 'name' => 'R']);
    $section = \App\Models\Academic\Section::create(['school_id' => $this->otherSchool->id, 'name' => 'S']);
    $year = \App\Models\Academic\AcademicYear::create([
        'school_id' => $this->otherSchool->id, 'name' => 'Y',
        'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true,
    ]);
    $foreignSection = ClassSection::create([
        'school_id' => $this->otherSchool->id, 'class_room_id' => $room->id,
        'section_id' => $section->id, 'medium_id' => $medium->id, 'academic_year_id' => $year->id,
    ]);

    $this->actingAs($this->admin)
        ->postJson(route('admin.qr-attendance.generate'), ['class_section_id' => $foreignSection->id])
        ->assertStatus(422);
});

test('refund rejects foreign payment reference', function () {
    $structure = FeeStructure::create([
        'school_id' => $this->school->id, 'name' => 'SPP', 'frequency' => 'monthly',
        'amount' => 10000000, 'is_active' => true,
    ]);
    $studentUser = User::factory()->create(['school_id' => $this->school->id]);
    $student = Student::create(['user_id' => $studentUser->id, 'school_id' => $this->school->id]);
    $invoice = FeeInvoice::create([
        'school_id' => $this->school->id, 'student_id' => $student->id,
        'fee_structure_id' => $structure->id, 'invoice_no' => 'INV-AUDIT-2',
        'due_date' => now()->toDateString(), 'amount' => 10000000,
        'paid_amount' => 10000000, 'status' => 'paid', 'period' => '2026-01',
    ]);
    $foreignStructure = FeeStructure::create([
        'school_id' => $this->otherSchool->id, 'name' => 'SPP', 'frequency' => 'monthly',
        'amount' => 5000000, 'is_active' => true,
    ]);
    $foreignStudentUser = User::factory()->create(['school_id' => $this->otherSchool->id]);
    $foreignStudent = Student::create(['user_id' => $foreignStudentUser->id, 'school_id' => $this->otherSchool->id]);
    $foreignInvoice = FeeInvoice::create([
        'school_id' => $this->otherSchool->id, 'student_id' => $foreignStudent->id,
        'fee_structure_id' => $foreignStructure->id, 'invoice_no' => 'INV-FOREIGN-1',
        'due_date' => now()->toDateString(), 'amount' => 5000000,
        'paid_amount' => 5000000, 'status' => 'paid', 'period' => '2026-01',
    ]);
    $foreignPayment = FeePayment::create([
        'school_id' => $this->otherSchool->id, 'fee_invoice_id' => $foreignInvoice->id,
        'collected_by' => $this->admin->id, 'amount' => 5000000,
        'payment_method' => 'cash', 'payment_date' => now()->toDateString(),
    ]);

    $this->actingAs($this->admin)->post(route('admin.fee.invoices.refund', $invoice), [
        'amount_rupiah' => 10000,
        'reason' => 'coba',
        'fee_payment_id' => $foreignPayment->id,
    ])->assertStatus(422);

    expect($invoice->fresh()->paid_amount)->toBe(10000000);
});

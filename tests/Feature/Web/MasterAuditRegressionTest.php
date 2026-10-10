<?php

// Regression tests for MASTER-COMMAND audit session 2026-10-10:
// missing OSIS/committee views, generator save route, scoped journal COA,
// fee rounding + structure guards, payroll tax-profile ownership + ledger
// posting on pay, asset scoping + writeoff/loan atomicity, budget guards,
// cooperative delete guards, parent-payment ownership.

use App\Models\Academic\Staff;
use App\Models\Academic\Student;
use App\Models\Committee\CommitteeMeeting;
use App\Models\Finance\BudgetCategory;
use App\Models\Finance\BudgetItem;
use App\Models\Finance\BudgetTransaction;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\CooperativeLoan;
use App\Models\Finance\CooperativeMember;
use App\Models\Finance\CooperativeSaving;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeeStructure;
use App\Models\Finance\JournalEntry;
use App\Models\Finance\SalarySlip;
use App\Models\Inventory\Asset;
use App\Models\Inventory\AssetCategory;
use App\Models\Inventory\AssetLoan;
use App\Models\Osis\OsisProgram;
use App\Models\School;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->school = School::factory()->create();
    $this->otherSchool = School::factory()->create();
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'parent']);
    Role::firstOrCreate(['name' => 'student']);
    $this->admin = User::factory()->create(['school_id' => $this->school->id]);
    $this->admin->assignRole('admin');
});

test('admin osis programs page renders (view was missing)', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.osis.programs'))
        ->assertOk()
        ->assertSee('Program Kerja OSIS');
});

test('admin osis programs store + delete round-trip', function () {
    $this->actingAs($this->admin)->post(route('admin.osis.programs.store'), [
        'title' => 'Bakti Sosial',
    ])->assertRedirect();

    $program = OsisProgram::where('school_id', $this->school->id)->firstOrFail();
    expect($program->title)->toBe('Bakti Sosial');

    $this->actingAs($this->admin)
        ->get(route('admin.osis.programs'))
        ->assertOk()
        ->assertSee('Bakti Sosial');

    $this->actingAs($this->admin)
        ->delete(route('admin.osis.programs.delete', $program))
        ->assertRedirect();
    expect(OsisProgram::whereKey($program->id)->exists())->toBeFalse();
});

test('student osis programs page renders (view was missing)', function () {
    $studentUser = User::factory()->create(['school_id' => $this->school->id]);
    $studentUser->assignRole('student');

    $this->actingAs($studentUser)
        ->get(route('student.osis.programs'))
        ->assertOk()
        ->assertSee('Program Kerja OSIS');
});

test('committee meeting page renders (view was missing)', function () {
    $meeting = CommitteeMeeting::create([
        'school_id' => $this->school->id, 'title' => 'Rapat Q1',
        'meeting_date' => now()->addDay(), 'status' => 'scheduled',
        'created_by' => $this->admin->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('portal.committee.meeting', $meeting->id))
        ->assertOk()
        ->assertSee('Rapat Q1');
});

test('timetable generator save route rejects foreign ids', function () {
    $medium = \App\Models\Academic\Medium::create(['school_id' => $this->otherSchool->id, 'name' => 'M']);
    $room = \App\Models\Academic\ClassRoom::create(['school_id' => $this->otherSchool->id, 'medium_id' => $medium->id, 'name' => 'R']);
    $section = \App\Models\Academic\Section::create(['school_id' => $this->otherSchool->id, 'name' => 'S']);
    $year = \App\Models\Academic\AcademicYear::create([
        'school_id' => $this->otherSchool->id, 'name' => 'Y',
        'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true,
    ]);
    $foreignSection = \App\Models\Academic\ClassSection::create([
        'school_id' => $this->otherSchool->id, 'class_room_id' => $room->id,
        'section_id' => $section->id, 'medium_id' => $medium->id, 'academic_year_id' => $year->id,
    ]);

    $this->actingAs($this->admin)->post(route('admin.timetable.generator.save'), [
        'academic_year_id' => $year->id,
        'class_section_id' => $foreignSection->id,
        'days_per_week' => 5,
        'periods_per_day' => 7,
        'period_duration_minutes' => 45,
        'start_time' => '07:00',
    ])->assertInvalid(['academic_year_id', 'class_section_id']);
});

test('journal store rejects foreign chart of accounts', function () {
    ChartOfAccount::create(['school_id' => $this->otherSchool->id, 'code' => '1000', 'name' => 'Kas', 'type' => 'asset', 'normal_balance' => 'debit', 'is_active' => true]);
    $own = ChartOfAccount::create(['school_id' => $this->school->id, 'code' => '1000', 'name' => 'Kas', 'type' => 'asset', 'normal_balance' => 'debit', 'is_active' => true]);
    $rev = ChartOfAccount::create(['school_id' => $this->school->id, 'code' => '4000', 'name' => 'Pendapatan', 'type' => 'revenue', 'normal_balance' => 'credit', 'is_active' => true]);
    $foreign = ChartOfAccount::where('school_id', $this->otherSchool->id)->firstOrFail();

    $this->actingAs($this->admin)->post(route('admin.accounting.journal.store'), [
        'entry_date' => now()->toDateString(),
        'description' => 'coba',
        'lines' => [
            ['chart_of_account_id' => $own->id, 'debit' => 10000, 'credit' => 0],
            ['chart_of_account_id' => $foreign->id, 'debit' => 0, 'credit' => 10000],
        ],
    ])->assertInvalid('lines.1.chart_of_account_id');

    expect(JournalEntry::count())->toBe(0);
});

test('fee structure rounds correctly and cannot be deleted with invoices', function () {
    $this->actingAs($this->admin)->post(route('admin.fee.structures.store'), [
        'name' => 'SPP', 'frequency' => 'monthly', 'amount_rupiah' => 19999.99,
    ])->assertRedirect();

    $structure = FeeStructure::where('school_id', $this->school->id)->firstOrFail();
    expect($structure->amount)->toBe(1999999);

    $studentUser = User::factory()->create(['school_id' => $this->school->id]);
    $student = Student::create(['user_id' => $studentUser->id, 'school_id' => $this->school->id]);
    FeeInvoice::create([
        'school_id' => $this->school->id, 'student_id' => $student->id,
        'fee_structure_id' => $structure->id, 'invoice_no' => 'INV-DEL-1',
        'due_date' => now()->toDateString(), 'amount' => 1999999,
        'paid_amount' => 0, 'status' => 'unpaid', 'period' => '2026-01',
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.fee.structures.destroy', $structure))
        ->assertSessionHasErrors();
    expect(FeeStructure::whereKey($structure->id)->exists())->toBeTrue();
});

test('tax profile rejects foreign staff', function () {
    $foreignUser = User::factory()->create(['school_id' => $this->otherSchool->id]);
    $foreignStaff = Staff::create([
        'school_id' => $this->otherSchool->id, 'user_id' => $foreignUser->id,
        'basic_salary' => 500000000,
    ]);

    $this->actingAs($this->admin)->post(
        route('admin.payroll.tax-profile.update', $foreignStaff->id),
        ['pTKP_status' => 1, 'number_of_dependents' => 0]
    )->assertNotFound();
});

test('paySlip posts ledger and rejects double pay', function () {
    ChartOfAccount::create(['school_id' => $this->school->id, 'code' => '5000', 'name' => 'Beban Gaji', 'type' => 'expense', 'normal_balance' => 'debit', 'is_active' => true]);
    ChartOfAccount::create(['school_id' => $this->school->id, 'code' => '1000', 'name' => 'Kas', 'type' => 'asset', 'normal_balance' => 'debit', 'is_active' => true]);
    $staffUser = User::factory()->create(['school_id' => $this->school->id]);
    $staff = Staff::create([
        'school_id' => $this->school->id, 'user_id' => $staffUser->id,
        'basic_salary' => 500000000,
    ]);
    $slip = SalarySlip::create([
        'school_id' => $this->school->id, 'staff_id' => $staff->id, 'month' => '2026-01',
        'basic_salary' => 500000000, 'total_allowances' => 0,
        'total_deductions' => 0, 'net_salary' => 500000000, 'status' => 'draft',
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.payroll.slips.pay', $slip))
        ->assertRedirect();
    expect($slip->fresh()->status)->toBe('paid');
    expect(JournalEntry::where('school_id', $this->school->id)->where('reference_no', 'PAYROLL-' . $slip->id)->count())->toBe(1);

    $this->actingAs($this->admin)
        ->post(route('admin.payroll.slips.pay', $slip))
        ->assertStatus(409);
    expect(JournalEntry::where('school_id', $this->school->id)->where('reference_no', 'PAYROLL-' . $slip->id)->count())->toBe(1);
});

test('asset loan rejects foreign asset and returns atomically', function () {
    $cat = AssetCategory::create(['school_id' => $this->school->id, 'name' => 'Elektronik']);

    $this->actingAs($this->admin)->post(route('admin.inventory.loans.store'), [
        'asset_id' => 999999,
        'borrower_id' => $this->admin->id,
        'borrowed_at' => now()->toDateString(),
        'due_at' => now()->addWeek()->toDateString(),
    ])->assertInvalid('asset_id');

    $asset = Asset::create([
        'school_id' => $this->school->id, 'asset_category_id' => $cat->id,
        'asset_code' => 'A-1', 'name' => 'Laptop', 'condition' => 'good', 'status' => 'available',
    ]);

    $this->actingAs($this->admin)->post(route('admin.inventory.loans.store'), [
        'asset_id' => $asset->id,
        'borrower_id' => $this->admin->id,
        'borrowed_at' => now()->toDateString(),
        'due_at' => now()->addWeek()->toDateString(),
    ])->assertRedirect();

    expect($asset->fresh()->status)->toBe('borrowed');
    $loan = AssetLoan::where('asset_id', $asset->id)->firstOrFail();

    $this->actingAs($this->admin)
        ->post(route('admin.inventory.loans.return', $loan))
        ->assertRedirect();
    expect($asset->fresh()->status)->toBe('available');
});

test('budget guards: foreign update 403, item with transactions protected', function () {
    $foreignCat = BudgetCategory::create([
        'school_id' => $this->otherSchool->id, 'name' => 'X', 'code' => 'X', 'type' => 'expense',
    ]);

    $this->actingAs($this->admin)->put(route('admin.budget.categories.update', $foreignCat), [
        'name' => 'Hacked', 'code' => 'X', 'type' => 'expense',
    // Foreign category is blocked by tenant-scoped route binding (404),
    // with authorizeOwn as defense-in-depth inside the controller.
    ])->assertNotFound();

    $cat = BudgetCategory::create([
        'school_id' => $this->school->id, 'name' => 'OP', 'code' => 'OP', 'type' => 'expense',
    ]);
    $item = BudgetItem::create([
        'school_id' => $this->school->id, 'budget_category_id' => $cat->id,
        'name' => 'OPI', 'planned_amount' => 100000, 'actual_amount' => 50000, 'status' => 'planned',
    ]);
    BudgetTransaction::create([
        'school_id' => $this->school->id, 'budget_item_id' => $item->id,
        'transaction_date' => now()->toDateString(), 'amount' => 50000,
        'recorded_by' => $this->admin->id,
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.budget.items.destroy', $item))
        ->assertSessionHasErrors();
    expect(BudgetItem::whereKey($item->id)->exists())->toBeTrue();
});

test('cooperative delete guards: member with savings, active loan', function () {
    $memberUser = User::factory()->create(['school_id' => $this->school->id]);
    $member = CooperativeMember::create([
        'school_id' => $this->school->id, 'memberable_type' => User::class,
        'memberable_id' => $memberUser->id, 'member_number' => 'M-9',
        'join_date' => now()->toDateString(), 'total_savings' => 0, 'status' => 'active',
    ]);
    CooperativeSaving::create([
        'school_id' => $this->school->id, 'cooperative_member_id' => $member->id,
        'transaction_date' => now()->toDateString(), 'amount' => 50000,
        'savings_type' => 'wajib', 'transaction_type' => 'deposit',
        'recorded_by' => $this->admin->id, 'reference_no' => 'SVG-T9',
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.cooperative.members.delete', $member))
        ->assertSessionHasErrors();

    $loan = CooperativeLoan::create([
        'school_id' => $this->school->id, 'cooperative_member_id' => $member->id,
        'loan_amount' => 1000000, 'interest_rate' => 0, 'term_months' => 10,
        'start_date' => now()->toDateString(), 'status' => 'active',
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.cooperative.loans.delete', $loan))
        ->assertStatus(422);
    expect(CooperativeLoan::whereKey($loan->id)->exists())->toBeTrue();
});

test('parent cannot open another child invoice', function () {
    $parentA = User::factory()->create(['school_id' => $this->school->id]);
    $parentA->assignRole('parent');
    $childAUser = User::factory()->create(['school_id' => $this->school->id]);
    $childA = Student::create(['user_id' => $childAUser->id, 'school_id' => $this->school->id]);
    $childA->parents()->attach($parentA->id);

    $childBUser = User::factory()->create(['school_id' => $this->school->id]);
    $childB = Student::create(['user_id' => $childBUser->id, 'school_id' => $this->school->id]);
    $structure = FeeStructure::create([
        'school_id' => $this->school->id, 'name' => 'SPP', 'frequency' => 'monthly',
        'amount' => 10000000, 'is_active' => true,
    ]);
    $invoiceB = FeeInvoice::create([
        'school_id' => $this->school->id, 'student_id' => $childB->id,
        'fee_structure_id' => $structure->id, 'invoice_no' => 'INV-PB-1',
        'due_date' => now()->toDateString(), 'amount' => 10000000,
        'paid_amount' => 0, 'status' => 'unpaid', 'period' => '2026-01',
    ]);

    $this->actingAs($parentA)
        ->get(route('portal.invoices.pay', $invoiceB->id))
        ->assertForbidden();
});

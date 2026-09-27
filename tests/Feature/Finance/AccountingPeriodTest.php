<?php

use App\Models\Finance\AccountingPeriod;
use App\Models\School;
use App\Models\User;
use App\Services\Finance\AccountingService;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpKernel\Exception\HttpException;

function accountingFixture(): array
{
    $school = School::factory()->create(['settings' => []]);
    app()->instance('current_school', $school);
    app(AccountingService::class)->seedDefaultCoa($school->id);

    $admin = User::factory()->create(['school_id' => $school->id, 'is_active' => true]);
    $admin->assignRole('admin');

    return [$school, $admin];
}

function balancedLines(int $schoolId): array
{
    $cash = \App\Models\Finance\ChartOfAccount::withoutGlobalScopes()->where('school_id', $schoolId)->where('code', '1000')->firstOrFail();
    $revenue = \App\Models\Finance\ChartOfAccount::withoutGlobalScopes()->where('school_id', $schoolId)->where('code', '4000')->firstOrFail();

    return [
        ['chart_of_account_id' => $cash->id, 'debit' => 1000000, 'credit' => 0],
        ['chart_of_account_id' => $revenue->id, 'debit' => 0, 'credit' => 1000000],
    ];
}

test('closed period rejects new journal entries', function () {
    [$school, $admin] = accountingFixture();
    Sanctum::actingAs($admin);

    app(AccountingService::class)->closePeriod($school->id, '2026-01');

    $this->expectException(HttpException::class);
    app(AccountingService::class)->createEntry($school->id, [
        'entry_date' => '2026-01-15', 'description' => 'Uji tutup',
    ], balancedLines($school->id));
});

test('closed period rejects posting and reopen restores it', function () {
    [$school, $admin] = accountingFixture();
    Sanctum::actingAs($admin);
    $service = app(AccountingService::class);

    $entry = $service->createEntry($school->id, [
        'entry_date' => '2026-02-10', 'description' => 'Sebelum tutup',
    ], balancedLines($school->id));

    $service->closePeriod($school->id, '2026-02');

    try {
        $service->post($entry);
        $this->fail('Posting ke periode tertutup harus ditolak.');
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(423);
    }

    $service->reopenPeriod($school->id, '2026-02');
    $service->post($entry);

    expect($entry->fresh()->status)->toBe('posted');
});

test('reopen requires accounting reopen permission', function () {
    [$school, $admin] = accountingFixture();

    $accountant = User::factory()->create(['school_id' => $school->id, 'is_active' => true]);
    $accountant->assignRole('accountant');

    Sanctum::actingAs($admin);
    app(AccountingService::class)->closePeriod($school->id, '2026-03');

    Sanctum::actingAs($accountant);
    try {
        app(AccountingService::class)->reopenPeriod($school->id, '2026-03');
        $this->fail('Akuntan tanpa izin reopen harus ditolak.');
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(403);
    }

    expect(AccountingPeriod::where('school_id', $school->id)->where('period', '2026-03')->first()->status)->toBe('closed');
});

test('period close is tenant isolated', function () {
    [$schoolA, $adminA] = accountingFixture();
    $schoolB = School::factory()->create(['settings' => []]);
    app(AccountingService::class)->seedDefaultCoa($schoolB->id);

    Sanctum::actingAs($adminA);
    app(AccountingService::class)->closePeriod($schoolA->id, '2026-04');

    // School B unaffected: entry in same month still allowed.
    $entry = app(AccountingService::class)->createEntry($schoolB->id, [
        'entry_date' => '2026-04-10', 'description' => 'Sekolah B',
    ], balancedLines($schoolB->id));

    expect($entry->status)->toBe('draft');
});

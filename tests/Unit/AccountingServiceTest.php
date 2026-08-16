<?php

use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\JournalEntry;
use App\Models\School;
use App\Services\Finance\AccountingService;

beforeEach(fn () => $this->service = new AccountingService());

function acctSchool(): School
{
    return School::factory()->create();
}

it('seeds a default chart of accounts idempotently', function () {
    $school = acctSchool();

    $created = $this->service->seedDefaultCoa($school->id);
    expect($created)->toBe(14);

    $again = $this->service->seedDefaultCoa($school->id);
    expect($again)->toBe(0);
});

it('posts a balanced journal and produces correct reports', function () {
    $school = acctSchool();
    $this->service->seedDefaultCoa($school->id);

    $kas   = ChartOfAccount::where('school_id', $school->id)->where('code', '1000')->firstOrFail();
    $spp   = ChartOfAccount::where('school_id', $school->id)->where('code', '4000')->firstOrFail();

    $entry = $this->service->createEntry($school->id, [
        'entry_date'   => '2026-08-16',
        'reference_no' => 'JRN-1',
        'description'  => 'Penerimaan SPP',
    ], [
        ['chart_of_account_id' => $kas->id, 'debit' => 100000, 'credit' => 0],
        ['chart_of_account_id' => $spp->id, 'debit' => 0, 'credit' => 100000],
    ]);

    expect($entry->status)->toBe('draft');

    $this->service->post($entry->fresh());
    expect($entry->fresh()->status)->toBe('posted');

    $tb = $this->service->trialBalance($school->id);
    expect($tb)->toHaveCount(2);

    $kasRow = $tb->firstWhere('code', '1000');
    expect($kasRow->debit)->toBe(100000);
    expect($kasRow->balance)->toBe(100000);

    $pl = $this->service->profitLoss($school->id);
    expect($pl['revenue'])->toBe(100000);
    expect($pl['expense'])->toBe(0);
    expect($pl['net_income'])->toBe(100000);

    $bs = $this->service->balanceSheet($school->id);
    expect($bs['assets'])->toBe(100000);
    expect($bs['total_equity'])->toBe(100000);
    expect($bs['assets'])->toBe($bs['liabilities_plus_equity']);
});

it('rejects an unbalanced journal on post', function () {
    $school = acctSchool();
    $this->service->seedDefaultCoa($school->id);

    $kas = ChartOfAccount::where('school_id', $school->id)->where('code', '1000')->firstOrFail();

    $entry = $this->service->createEntry($school->id, ['entry_date' => '2026-08-16'], [
        ['chart_of_account_id' => $kas->id, 'debit' => 100000, 'credit' => 0],
    ]);

    $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

    $this->service->post($entry);
});

<?php

// Mobile money contract: DB stores minor units (cents), /api/v1 speaks
// whole rupiah. Covers ConvertsRupiah mapping used by Fee, Payroll,
// Canteen, Donation, Scholarship, Inventory, Reports, Budget endpoints.

use App\Http\Controllers\Api\Concerns\ConvertsRupiah;

function rupiahProbe(): object
{
    return new class {
        use ConvertsRupiah;

        public function out(mixed $p): mixed
        {
            return $this->inRupiah($p);
        }

        public function toCentsDeepPub(array $d, array $k): array
        {
            return $this->toCentsDeep($d, $k);
        }
    };
}

test('inRupiah divides known money keys by 100', function () {
    $out = rupiahProbe()->out(['amount' => 15000000, 'paid_amount' => 5000000, 'title' => 'SPP']);

    expect($out['amount'])->toBe(150000)
        ->and($out['paid_amount'])->toBe(50000)
        ->and($out['title'])->toBe('SPP');
});

test('inRupiah recurses into nested relations and paginator shapes', function () {
    $out = rupiahProbe()->out([
        'data' => [
            ['amount' => 20000, 'payments' => [['amount' => 5000]]],
        ],
        'total' => 1,
    ]);

    expect($out['data'][0]['amount'])->toBe(200)
        ->and($out['data'][0]['payments'][0]['amount'])->toBe(50)
        ->and($out['total'])->toBe(1);
});

test('inRupiah never touches percentages, points, quantities', function () {
    $out = rupiahProbe()->out([
        'discount_value' => 15,
        'fee_percent_bp' => 250,
        'points' => 300,
        'qty' => 2,
        'value' => 10,
    ]);

    expect($out)->toBe([
        'discount_value' => 15,
        'fee_percent_bp' => 250,
        'points' => 300,
        'qty' => 2,
        'value' => 10,
    ]);
});

test('toCentsDeep converts only listed input keys', function () {
    $out = rupiahProbe()->toCentsDeepPub(
        ['amount' => 150000, 'reason' => 'tunai'], ['amount']);

    expect($out['amount'])->toBe(15000000)
        ->and($out['reason'])->toBe('tunai');
});

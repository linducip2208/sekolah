<?php

use App\Models\School;
use App\Services\Finance\CurrencyService;

test('idr default formatting works with zero decimals', function () {
    $school = School::factory()->create([
        'currency_code'          => 'IDR',
        'currency_symbol'        => 'Rp',
        'currency_decimals'      => 0,
        'currency_thousands_sep' => '.',
        'currency_decimal_sep'   => ',',
    ]);
    expect(app(CurrencyService::class)->format(1_234_567, $school))->toBe('Rp 1.234.567');
});

test('usd formatting with two decimals', function () {
    $school = School::factory()->create([
        'currency_code'          => 'USD',
        'currency_symbol'        => '$',
        'currency_decimals'      => 2,
        'currency_thousands_sep' => ',',
        'currency_decimal_sep'   => '.',
    ]);
    expect(app(CurrencyService::class)->format(123_456, $school))->toBe('$ 1,234.56');
});

test('applying preset updates school config', function () {
    $school = School::factory()->create(['currency_code' => 'IDR']);
    app(CurrencyService::class)->applyPreset($school, 'EUR');
    $school->refresh();
    expect($school->currency_code)->toBe('EUR');
    expect($school->currency_symbol)->toBe('€');
    expect($school->currency_decimals)->toBe(2);
});

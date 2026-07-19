<?php

use App\Models\School;
use App\Services\Finance\CurrencyService;

if (!function_exists('money')) {
    function money(int|float|null $amount, ?School $school = null): string
    {
        return app(CurrencyService::class)->format($amount, $school);
    }
}

if (!function_exists('currency_symbol')) {
    function currency_symbol(?School $school = null): string
    {
        return app(CurrencyService::class)->symbol($school);
    }
}

if (!function_exists('currency_code')) {
    function currency_code(?School $school = null): string
    {
        return app(CurrencyService::class)->code($school);
    }
}

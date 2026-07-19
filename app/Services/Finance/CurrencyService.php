<?php

namespace App\Services\Finance;

use App\Models\School;

class CurrencyService
{
    /** Common currency presets — auto-fill convenience only, never enforced. */
    public const PRESETS = [
        'IDR' => ['symbol' => 'Rp',  'decimals' => 0, 'thousands' => '.', 'decimal' => ','],
        'USD' => ['symbol' => '$',   'decimals' => 2, 'thousands' => ',', 'decimal' => '.'],
        'EUR' => ['symbol' => '€',   'decimals' => 2, 'thousands' => '.', 'decimal' => ','],
        'GBP' => ['symbol' => '£',   'decimals' => 2, 'thousands' => ',', 'decimal' => '.'],
        'SGD' => ['symbol' => 'S$',  'decimals' => 2, 'thousands' => ',', 'decimal' => '.'],
        'MYR' => ['symbol' => 'RM',  'decimals' => 2, 'thousands' => ',', 'decimal' => '.'],
        'PHP' => ['symbol' => '₱',   'decimals' => 2, 'thousands' => ',', 'decimal' => '.'],
        'THB' => ['symbol' => '฿',   'decimals' => 2, 'thousands' => ',', 'decimal' => '.'],
        'VND' => ['symbol' => '₫',   'decimals' => 0, 'thousands' => '.', 'decimal' => ','],
        'JPY' => ['symbol' => '¥',   'decimals' => 0, 'thousands' => ',', 'decimal' => '.'],
        'INR' => ['symbol' => '₹',   'decimals' => 2, 'thousands' => ',', 'decimal' => '.'],
        'AUD' => ['symbol' => 'A$',  'decimals' => 2, 'thousands' => ',', 'decimal' => '.'],
        'SAR' => ['symbol' => 'ر.س', 'decimals' => 2, 'thousands' => ',', 'decimal' => '.'],
    ];

    public function format(int|float|null $amountMinorUnits, ?School $school = null): string
    {
        $school ??= $this->resolveSchool();
        $symbol    = $school?->currency_symbol ?? 'Rp';
        $decimals  = (int) ($school?->currency_decimals ?? 0);
        $thousands = $school?->currency_thousands_sep ?? '.';
        $decimalSep = $school?->currency_decimal_sep ?? ',';

        $minor = (int) round($amountMinorUnits ?? 0);
        $divisor = 10 ** $decimals;
        $value   = $decimals > 0 ? $minor / $divisor : $minor;

        return $symbol . ' ' . number_format($value, $decimals, $decimalSep, $thousands);
    }

    public function symbol(?School $school = null): string
    {
        $school ??= $this->resolveSchool();
        return $school?->currency_symbol ?? 'Rp';
    }

    public function code(?School $school = null): string
    {
        $school ??= $this->resolveSchool();
        return $school?->currency_code ?? 'IDR';
    }

    public function applyPreset(School $school, string $currencyCode): void
    {
        $code = strtoupper($currencyCode);
        $preset = self::PRESETS[$code] ?? null;
        $school->currency_code = $code;
        if ($preset) {
            $school->currency_symbol         = $preset['symbol'];
            $school->currency_decimals       = $preset['decimals'];
            $school->currency_thousands_sep  = $preset['thousands'];
            $school->currency_decimal_sep    = $preset['decimal'];
        }
        $school->save();
    }

    private function resolveSchool(): ?School
    {
        if (!auth()->check()) return null;
        $schoolId = auth()->user()->school_id;
        if (!$schoolId) return null;
        return School::find($schoolId);
    }
}

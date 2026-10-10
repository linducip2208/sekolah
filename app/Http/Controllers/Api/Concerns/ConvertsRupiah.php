<?php

namespace App\Http\Controllers\Api\Concerns;

/**
 * Mobile API money contract: whole rupiah (integer, 0 decimals).
 *
 * The database stores minor units (cents) everywhere — every web
 * write path multiplies by 100 and every web read divides by 100.
 * These helpers convert at the mobile API boundary so Flutter can
 * keep sending/displaying whole rupiah (see eSchool AGENTS.md).
 *
 * Percentage / point / quantity keys are NEVER converted.
 */
trait ConvertsRupiah
{
    /** @var string[] Model keys holding minor-unit money. */
    private const RUPIAH_KEYS = [
        'amount',
        'paid_amount',
        'discount',
        'discount_amount',
        'planned_amount',
        'actual_amount',
        'purchase_price',
        'price',
        'total_price',
        'total_amount',
        'subtotal',
        'balance',
        'daily_limit',
        'monthly_limit',
        'low_balance_threshold',
        'target_amount',
        'raised_amount',
        'basic_salary',
        'total_allowances',
        'total_deductions',
        'net_salary',
        'total_unpaid_amount',
        'fees_collected',
        'fees_pending',
    ];

    /**
     * Convert minor units → whole rupiah, recursively.
     * Accepts Eloquent models, collections, paginators, arrays.
     */
    protected function inRupiah(mixed $payload): mixed
    {
        if ($payload instanceof \Illuminate\Contracts\Support\Arrayable) {
            $payload = $payload->toArray();
        }

        return $this->mapMoney($payload, fn (int $cents): int => intdiv($cents, 100));
    }

    /** Whole rupiah → minor units for writes. */
    protected function toCents(int $rupiah): int
    {
        return $rupiah * 100;
    }

    /** Convert listed input keys from rupiah to minor units. */
    protected function toCentsDeep(array $data, array $keys): array
    {
        foreach ($keys as $k) {
            if (isset($data[$k]) && is_numeric($data[$k])) {
                $data[$k] = (int) $data[$k] * 100;
            }
        }

        return $data;
    }

    private function mapMoney(mixed $value, callable $fn): mixed
    {
        if (! is_array($value)) {
            return $value;
        }
        $out = [];
        foreach ($value as $k => $v) {
            if (is_array($v)) {
                $out[$k] = $this->mapMoney($v, $fn);
            } elseif (in_array($k, self::RUPIAH_KEYS, true) && is_numeric($v)) {
                $out[$k] = $fn((int) $v);
            } else {
                $out[$k] = $v;
            }
        }

        return $out;
    }
}

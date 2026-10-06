<?php

namespace App\Services;

use App\Models\School;

class SchoolSettingsService
{
    /**
     * Keys writable via API. Everything else is ignored server-side to
     * prevent settings poisoning through mass update.
     */
    public const array WRITABLE_KEYS = [
        'currency',
        'currency_symbol',
        'currency_decimals',
        'currency_thousands_sep',
        'currency_decimal_sep',
        'working_days',
        'timezone',
        'locale',
        'attendance_tolerance_minutes',
        'report_header_text',
        'report_footer_text',
        'announcement_banner',
    ];

    public function get(School $school, string $key = null): mixed
    {
        $settings = $school->settings ?? [];
        return $key ? data_get($settings, $key) : $settings;
    }

    public function update(School $school, array $data): School
    {
        $settings = array_merge($school->settings ?? [], $data);
        $school->update(['settings' => $settings]);
        return $school->fresh();
    }

    public function getCurrency(School $school): array
    {
        return [
            'code'   => $this->get($school, 'currency') ?? 'IDR',
            'symbol' => $this->get($school, 'currency_symbol') ?? 'Rp',
        ];
    }

    public function getWorkingDays(School $school): array
    {
        return $this->get($school, 'working_days')
            ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
    }
}

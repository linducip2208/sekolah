<?php

namespace App\Services;

use App\Models\School;

class SchoolSettingsService
{
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

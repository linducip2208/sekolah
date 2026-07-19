<?php

namespace Database\Factories\PPDB;

use App\Models\PPDB\PpdbPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

class PpdbPeriodFactory extends Factory
{
    protected $model = PpdbPeriod::class;

    public function definition(): array
    {
        return [
            'name'                  => 'PPDB ' . fake()->year() . '/' . (fake()->year() + 1),
            'open_date'             => now()->subDay(),
            'close_date'            => now()->addMonth(),
            'announcement_date'     => now()->addMonths(2),
            'form_fee'              => fake()->randomElement([0, 100_000_00, 250_000_00]),
            'jalur_config'          => [
                'zonasi'   => 50,
                'prestasi' => 30,
                'reguler'  => 20,
            ],
            'document_requirements' => ['kk', 'akta', 'rapor'],
            'is_published'          => true,
        ];
    }
}

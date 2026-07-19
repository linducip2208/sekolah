<?php

namespace Database\Factories\Scholarship;

use App\Models\Scholarship\ScholarshipProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScholarshipProgramFactory extends Factory
{
    protected $model = ScholarshipProgram::class;

    public function definition(): array
    {
        return [
            'name'                  => fake()->randomElement([
                'Beasiswa Berprestasi', 'Beasiswa Yatim Piatu', 'Beasiswa Olimpiade',
            ]),
            'source'                => fake()->randomElement(['internal_school', 'external_donor', 'government']),
            'discount_type'         => fake()->randomElement(['percentage', 'fixed', 'full']),
            'discount_value'        => fake()->randomElement([25, 50, 100, 1_000_000_00]),
            'eligibility_criteria'  => ['min_avg_score' => 80],
            'open_date'             => today(),
            'close_date'            => today()->addMonth(),
            'quota'                 => fake()->randomElement([5, 10, 20, null]),
            'is_active'             => true,
        ];
    }
}

<?php

namespace Database\Factories\Academic;

use App\Models\Academic\AcademicYear;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition(): array
    {
        return [
            'school_id'  => School::factory(),
            'name'       => 'Tahun Ajaran ' . fake()->year('+1'),
            'start_date' => '2025-07-01',
            'end_date'   => '2026-06-30',
            'is_active'  => true,
        ];
    }
}

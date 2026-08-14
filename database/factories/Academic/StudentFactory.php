<?php

namespace Database\Factories\Academic;

use App\Models\Academic\Student;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'school_id'    => School::factory(),
            'user_id'      => User::factory(),
            'admission_no' => 'ADM-' . fake()->unique()->numerify('#####'),
            'gender'       => 'male',
        ];
    }
}

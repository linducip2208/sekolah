<?php

namespace Database\Factories\PPDB;

use App\Models\PPDB\PpdbApplication;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PpdbApplicationFactory extends Factory
{
    protected $model = PpdbApplication::class;

    public function definition(): array
    {
        return [
            'registration_no' => 'PPDB-' . fake()->year() . '-' . strtoupper(Str::random(6)),
            'jalur'           => fake()->randomElement(['zonasi', 'prestasi', 'afirmasi', 'undian', 'reguler']),
            'student_name'    => fake()->name(),
            'nisn'            => fake()->numerify('##########'),
            'date_of_birth'   => fake()->dateTimeBetween('-18 years', '-6 years'),
            'gender'          => fake()->randomElement(['male', 'female']),
            'address'         => fake()->address(),
            'district'        => fake()->city(),
            'city'            => fake()->city(),
            'parent_name'     => fake()->name(),
            'parent_phone'    => fake()->phoneNumber(),
            'parent_email'    => fake()->safeEmail(),
            'average_score'   => fake()->randomFloat(2, 60, 95),
            'status'          => 'submitted',
        ];
    }
}

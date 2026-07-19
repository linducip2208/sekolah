<?php

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        return [
            'name'      => fake()->company() . ' School',
            'subdomain' => fake()->unique()->slug(2),
            'email'     => fake()->companyEmail(),
            'phone'     => '021' . fake()->numerify('#######'),
            'address'   => fake()->address(),
            'timezone'  => 'Asia/Jakarta',
            'locale'    => 'id',
            'is_active' => true,
            'settings'  => [
                'library' => [
                    'max_books_per_member' => 3,
                    'default_issue_days'   => 14,
                    'fine_per_day_cents'   => 50000,
                ],
            ],
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        $name = fake()->randomElement(['Free', 'Basic', 'Pro', 'Enterprise']) . ' ' . Str::random(4);
        return [
            'name'         => $name,
            'slug'         => Str::slug($name) . '-' . Str::lower(Str::random(4)),
            'price'        => fake()->randomElement([0, 99000, 199000, 499000]),
            'max_students' => fake()->randomElement([50, 500, 5000]),
            'max_teachers' => fake()->randomElement([10, 50, 200]),
            'features'     => ['attendance', 'fee', 'library'],
            'is_active'    => true,
        ];
    }
}

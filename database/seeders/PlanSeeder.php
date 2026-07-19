<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'         => 'Free',
                'slug'         => 'free',
                'price'        => 0,
                'max_students' => 50,
                'max_teachers' => 5,
                'features'     => ['attendance', 'notice'],
                'is_active'    => true,
            ],
            [
                'name'         => 'Basic',
                'slug'         => 'basic',
                'price'        => 9900000,
                'max_students' => 500,
                'max_teachers' => 50,
                'features'     => ['attendance', 'library', 'fee', 'timetable', 'classroom', 'notice', 'exam'],
                'is_active'    => true,
            ],
            [
                'name'         => 'Pro',
                'slug'         => 'pro',
                'price'        => 19900000,
                'max_students' => 0,
                'max_teachers' => 0,
                'features'     => ['*'],
                'is_active'    => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}

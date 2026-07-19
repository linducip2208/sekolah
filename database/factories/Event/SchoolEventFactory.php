<?php

namespace Database\Factories\Event;

use App\Models\Event\SchoolEvent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SchoolEventFactory extends Factory
{
    protected $model = SchoolEvent::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+2 months');
        $title = fake()->randomElement([
            'Parent-Teacher Meeting',
            'Festival Sekolah',
            'Lomba Olimpiade',
            'Workshop Robotika',
            'Seminar Karir',
        ]);

        return [
            'title'        => $title,
            'slug'         => Str::slug($title) . '-' . Str::lower(Str::random(4)),
            'description'  => fake()->paragraph(),
            'event_type'   => fake()->randomElement(['parent_meeting','field_trip','festival','competition','workshop','seminar']),
            'starts_at'    => $start,
            'ends_at'      => (clone $start)->modify('+3 hours'),
            'venue'        => fake()->company() . ' Hall',
            'city'         => fake()->city(),
            'capacity'     => fake()->randomElement([50, 100, 200, 500]),
            'ticket_price' => fake()->randomElement([0, 25_000_00, 50_000_00]),
            'require_rsvp' => true,
            'is_published' => true,
        ];
    }
}

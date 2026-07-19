<?php

namespace Database\Factories\Donation;

use App\Models\Donation\DonationCampaign;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DonationCampaignFactory extends Factory
{
    protected $model = DonationCampaign::class;

    public function definition(): array
    {
        $title = fake()->randomElement([
            'Renovasi Mushola Sekolah',
            'Beasiswa Anak Yatim',
            'Bantuan Korban Banjir',
            'Pengadaan Lab Komputer',
            'Pembangunan Perpustakaan',
        ]);

        return [
            'title'         => $title,
            'slug'          => Str::slug($title) . '-' . Str::lower(Str::random(4)),
            'description'   => fake()->paragraph(5),
            'target_amount' => fake()->randomElement([10_000_000_00, 50_000_000_00, 100_000_000_00]),
            'raised_amount' => 0,
            'start_date'    => now()->subDays(7),
            'end_date'      => now()->addMonths(3),
            'category'      => fake()->randomElement(['scholarship', 'building', 'equipment', 'emergency', 'general']),
            'status'        => 'active',
            'is_public'     => true,
        ];
    }
}

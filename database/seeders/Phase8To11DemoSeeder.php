<?php

namespace Database\Seeders;

use App\Models\Achievement\AchievementCategory;
use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Models\Canteen\CanteenCategory;
use App\Models\Canteen\CanteenMenuItem;
use App\Models\Discipline\DisciplineCategory;
use App\Models\Donation\DonationCampaign;
use App\Models\Payment\PaymentMethod;
use App\Models\Payment\PaymentProvider;
use App\Models\PPDB\PpdbPeriod;
use App\Models\Religious\ReligiousModeConfig;
use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class Phase8To11DemoSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (!$school) {
            $this->command->warn('No schools found — run DemoSchoolSeeder first.');
            return;
        }

        $this->seedPaymentProviders($school);
        $this->seedDisciplineCategories($school);
        $this->seedAchievementCategories($school);
        $this->seedCanteen($school);
        $this->seedReligiousMode($school);
        $this->seedPpdb($school);
        $this->seedDonations($school);
        $this->seedAi($school);

        $this->command->info('Phase 8-11 demo data seeded.');
    }

    protected function seedPaymentProviders(School $school): void
    {
        $cash = PaymentProvider::firstOrCreate(
            ['school_id' => $school->id, 'slug' => 'cash-' . Str::lower(Str::random(4))],
            [
                'name'       => 'Kasir Sekolah',
                'api_format' => PaymentProvider::FORMAT_CASH,
                'is_sandbox' => false,
                'is_active'  => true,
            ],
        );

        PaymentMethod::firstOrCreate(
            ['school_id' => $school->id, 'code' => 'cash'],
            [
                'payment_provider_id' => $cash->id,
                'display_name'        => 'Bayar di Kasir',
                'fee_borne_by'        => PaymentMethod::FEE_BORNE_PARENT,
                'is_active'           => true,
            ],
        );

        $manual = PaymentProvider::firstOrCreate(
            ['school_id' => $school->id, 'slug' => 'transfer-manual-' . Str::lower(Str::random(4))],
            [
                'name'         => 'Transfer Manual',
                'api_format'   => PaymentProvider::FORMAT_BANK_TRANSFER_MANUAL,
                'extra_config' => [
                    'bank_accounts' => [
                        ['bank_name' => 'BCA', 'account_number' => '1234567890', 'account_holder' => $school->name],
                    ],
                    'instructions' => 'Transfer ke rekening di atas, lalu upload bukti.',
                ],
                'is_sandbox' => false,
                'is_active'  => true,
            ],
        );

        PaymentMethod::firstOrCreate(
            ['school_id' => $school->id, 'code' => 'bank_manual'],
            [
                'payment_provider_id' => $manual->id,
                'display_name'        => 'Transfer Manual + Upload Bukti',
                'instruction_template' => 'Transfer ke rekening sekolah, lalu upload bukti TF di app.',
                'is_active'           => true,
            ],
        );
    }

    protected function seedDisciplineCategories(School $school): void
    {
        $cats = [
            ['Terlambat', 'violation', -2],
            ['Tidak rapi', 'violation', -1],
            ['Berkelahi', 'violation', -10],
            ['Tidak masuk tanpa keterangan', 'violation', -5],
            ['Membantu teman', 'achievement', 2],
            ['Juara kelas', 'achievement', 10],
            ['Aktif organisasi', 'achievement', 5],
        ];
        foreach ($cats as [$name, $type, $points]) {
            DisciplineCategory::firstOrCreate(
                ['school_id' => $school->id, 'name' => $name],
                ['type' => $type, 'point_value' => $points],
            );
        }
    }

    protected function seedAchievementCategories(School $school): void
    {
        foreach ([
            ['Lomba Internal Sekolah', 'internal', 5],
            ['Olimpiade Tingkat Kota', 'district', 15],
            ['Olimpiade Tingkat Provinsi', 'province', 30],
            ['Olimpiade Nasional', 'national', 50],
            ['Olimpiade Internasional', 'international', 100],
        ] as [$name, $scope, $points]) {
            AchievementCategory::firstOrCreate(
                ['school_id' => $school->id, 'name' => $name],
                ['scope' => $scope, 'points' => $points],
            );
        }
    }

    protected function seedCanteen(School $school): void
    {
        $cats = [
            'Makanan Berat' => '🍱',
            'Snack & Roti'  => '🍞',
            'Minuman'       => '🥤',
        ];
        foreach ($cats as $name => $icon) {
            CanteenCategory::firstOrCreate(
                ['school_id' => $school->id, 'name' => $name],
                ['icon' => $icon, 'healthy_tag' => true],
            );
        }

        $foodCat  = CanteenCategory::where('school_id', $school->id)->first();
        if ($foodCat) {
            foreach ([
                ['Nasi Goreng', 1500000],
                ['Mie Ayam',     1200000],
                ['Soto Ayam',    1500000],
                ['Es Teh',        500000],
            ] as [$name, $price]) {
                CanteenMenuItem::firstOrCreate(
                    ['school_id' => $school->id, 'name' => $name],
                    [
                        'canteen_category_id' => $foodCat->id,
                        'price'               => $price,
                        'is_available'        => true,
                    ],
                );
            }
        }
    }

    protected function seedReligiousMode(School $school): void
    {
        ReligiousModeConfig::firstOrCreate(
            ['school_id' => $school->id],
            [
                'enabled'           => false,
                'religion'          => 'islam',
                'institution_type'  => null,
                'use_hijri_calendar'=> false,
            ],
        );
    }

    protected function seedPpdb(School $school): void
    {
        PpdbPeriod::firstOrCreate(
            ['school_id' => $school->id, 'name' => 'PPDB ' . now()->year . '/' . (now()->year + 1)],
            [
                'academic_year_id' => 1,
                'open_date'        => now(),
                'close_date'       => now()->addMonths(3),
                'announcement_date'=> now()->addMonths(4),
                'form_fee'         => 100_000_00,
                'jalur_config'     => [
                    'zonasi'   => 50,
                    'prestasi' => 30,
                    'afirmasi' => 15,
                    'reguler'  => 5,
                ],
                'document_requirements' => ['kk', 'akta', 'rapor', 'foto'],
                'is_published'     => true,
            ],
        );
    }

    protected function seedDonations(School $school): void
    {
        DonationCampaign::firstOrCreate(
            ['school_id' => $school->id, 'slug' => 'demo-renovasi'],
            [
                'title'         => 'Renovasi Lab Komputer',
                'description'   => '<p>Bantu kami merenovasi lab komputer untuk siswa kami.</p>',
                'target_amount' => 50_000_000_00,
                'raised_amount' => 0,
                'start_date'    => today(),
                'end_date'      => today()->addMonths(3),
                'category'      => 'equipment',
                'status'        => 'active',
                'is_public'     => true,
            ],
        );
    }

    protected function seedAi(School $school): void
    {
        AiProvider::firstOrCreate(
            ['school_id' => $school->id, 'slug' => 'demo-ai-provider'],
            [
                'name'        => 'Demo AI (configure your own)',
                'api_format'  => 'openai_compatible',
                'base_url'    => 'https://api.example.com/v1',
                'is_active'   => false,  // Disabled until admin configures real API key
                'priority'    => 0,
            ],
        );
    }
}

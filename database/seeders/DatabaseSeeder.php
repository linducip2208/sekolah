<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            RolePermissionSeeder::class,
            AccreditationInstrumentSeeder::class,
            CommunicationSeeder::class,
        ]);

        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@eschool.app'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('password'),
                'is_active' => true,
                'locale'    => 'id',
            ]
        );
        $superAdmin->assignRole(Role::findByName('super_admin', 'web'));

        $demoSchool = School::updateOrCreate(
            ['subdomain' => 'demo'],
            [
                'name'     => 'Demo School',
                'email'    => 'admin@demo.eschool.app',
                'timezone' => 'Asia/Jakarta',
                'locale'   => 'id',
                'is_active' => true,
                'settings' => [
                    'library' => [
                        'max_books_per_member' => 3,
                        'default_issue_days'   => 14,
                        'fine_per_day_cents'   => 50000,
                        'allow_staff_borrow'   => true,
                    ],
                ],
            ]
        );

        $adminUser = User::updateOrCreate(
            ['email' => 'admin@demo.eschool.app'],
            [
                'school_id' => $demoSchool->id,
                'name'      => 'Admin Demo',
                'password'  => Hash::make('password'),
                'is_active' => true,
                'locale'    => 'id',
            ]
        );
        $adminUser->assignRole(Role::findByName('admin', 'web'));

        // Run full demo school seeder in non-test environments
        if (app()->environment(['local', 'staging'])) {
            $this->call(DemoSchoolSeeder::class);
        }
    }
}

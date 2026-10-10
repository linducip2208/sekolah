<?php

namespace Database\Seeders;

use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Medium;
use App\Models\Academic\Section;
use App\Models\Academic\Student;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\FeeStructure;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Deterministic fixture for browser E2E (Playwright) runs.
 *
 * SAFETY: refuses to run unless APP_ENV=testing AND the database is on the
 * explicit allowlist. Never point this at production or dev databases.
 */
class E2EFixtureSeeder extends Seeder
{
    public const ALLOWLIST = ['sikadpro_e2e_test', 'sikadpro_test'];

    public function run(): void
    {
        abort_unless(
            app()->environment('testing')
            && in_array(config('database.connections.mysql.database'), self::ALLOWLIST, true),
            403,
            'E2EFixtureSeeder only runs in testing on an allowlisted database.'
        );

        $this->call(RolePermissionSeeder::class);

        $school = School::factory()->create([
            'name' => 'Sekolah E2E',
            'subdomain' => 'e2e',
        ]);

        $mkUser = fn (string $email, string $role) => tap(
            User::factory()->create([
                'school_id' => $school->id,
                'email' => $email,
                'password' => Hash::make('Password123!'),
                'is_active' => true,
            ]),
            fn ($u) => $u->assignRole($role)
        );

        $mkUser('e2e-admin@sekolah.test', 'admin');
        $mkUser('e2e-teacher@sekolah.test', 'teacher');
        $parent = $mkUser('e2e-parent@sekolah.test', 'parent');

        $year = AcademicYear::create([
            'school_id' => $school->id, 'name' => '2025/2026',
            'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true,
        ]);
        $medium = Medium::create(['school_id' => $school->id, 'name' => 'Umum']);
        $room = ClassRoom::create(['school_id' => $school->id, 'medium_id' => $medium->id, 'name' => 'Kelas 7']);
        $section = Section::create(['school_id' => $school->id, 'name' => 'A']);
        $teacher = User::where('email', 'e2e-teacher@sekolah.test')->firstOrFail();
        $classSection = ClassSection::create([
            'school_id' => $school->id, 'class_room_id' => $room->id,
            'section_id' => $section->id, 'medium_id' => $medium->id,
            'academic_year_id' => $year->id, 'class_teacher_id' => $teacher->id,
        ]);

        $childUser = User::factory()->create([
            'school_id' => $school->id, 'email' => 'e2e-student@sekolah.test',
            'password' => Hash::make('Password123!'), 'is_active' => true,
        ]);
        $childUser->assignRole('student');
        $child = Student::create([
            'user_id' => $childUser->id, 'school_id' => $school->id,
            'class_section_id' => $classSection->id, 'admission_no' => 'E2E-001',
        ]);
        $child->parents()->attach($parent->id);

        FeeStructure::create([
            'school_id' => $school->id, 'name' => 'SPP', 'frequency' => 'monthly',
            'amount' => 10000000, 'is_active' => true,
        ]);

        foreach ([
            ['1000', 'Kas', 'asset', 'debit'],
            ['4000', 'Pendapatan', 'revenue', 'credit'],
            ['5000', 'Beban Gaji', 'expense', 'debit'],
        ] as [$code, $name, $type, $balance]) {
            ChartOfAccount::create([
                'school_id' => $school->id, 'code' => $code, 'name' => $name,
                'type' => $type, 'normal_balance' => $balance, 'is_active' => true,
            ]);
        }

        \App\Models\Committee\CommitteeMeeting::create([
            'school_id' => $school->id, 'title' => 'Rapat Komite E2E',
            'meeting_date' => now()->addWeek(), 'status' => 'scheduled',
            'created_by' => User::where('email', 'e2e-admin@sekolah.test')->firstOrFail()->id,
        ]);

        $coopUser = User::where('email', 'e2e-teacher@sekolah.test')->firstOrFail();
        \App\Models\Finance\CooperativeMember::create([
            'school_id' => $school->id, 'memberable_type' => User::class,
            'memberable_id' => $coopUser->id, 'member_number' => 'E2E-M1',
            'join_date' => now()->toDateString(), 'total_savings' => 0,
            'total_loans' => 0, 'status' => 'active',
        ]);

        $this->command->info('E2E fixture ready: e2e-admin@sekolah.test / Password123!');
    }
}

<?php

namespace Database\Seeders;

use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Medium;
use App\Models\Academic\Section;
use App\Models\Academic\Staff;
use App\Models\Academic\Student;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeeStructure;
use App\Models\Plan;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSchoolSeeder extends Seeder
{
    public function run(): void
    {
        $plan = Plan::where('slug', 'pro')->first();

        // Create demo school
        $school = School::create([
            'name'            => 'SMA Negeri 1 Demo',
            'subdomain'       => 'sman1demo',
            'email'           => 'info@sman1demo.sch.id',
            'phone'           => '021-5551234',
            'address'         => 'Jl. Pendidikan No. 1, Jakarta',
            'plan_id'         => $plan?->id,
            'plan_expires_at' => now()->addYear(),
            'is_active'       => true,
            'settings'        => [],
        ]);

        // Super admin
        $superAdmin = User::firstOrCreate(['email' => 'super@sikadpro.app'], [
            'name'      => 'Super Admin',
            'password'  => bcrypt('SuperAdmin123!'),
            'school_id' => null,
            'is_active' => true,
        ]);
        $superAdmin->assignRole('super_admin');

        // School admin
        $admin = User::create([
            'name'      => 'Ahmad Fauzi',
            'email'     => 'admin@sman1demo.sch.id',
            'password'  => bcrypt('Admin123!'),
            'school_id' => $school->id,
            'phone'     => '08123456789',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Academic Year
        $academicYear = AcademicYear::create([
            'school_id'  => $school->id,
            'name'       => '2024/2025',
            'start_date' => '2024-07-01',
            'end_date'   => '2025-06-30',
            'is_active'  => true,
        ]);

        // Medium
        $medium = Medium::create(['school_id' => $school->id, 'name' => 'Bahasa Indonesia']);

        // Classes
        $classes    = [];
        $classNames = ['Kelas 10', 'Kelas 11', 'Kelas 12'];
        foreach ($classNames as $name) {
            $classes[] = ClassRoom::create(['school_id' => $school->id, 'medium_id' => $medium->id, 'name' => $name]);
        }

        // Sections
        $sections    = [];
        $sectionNames = ['A', 'B', 'C'];
        foreach ($sectionNames as $name) {
            $sections[] = Section::create(['school_id' => $school->id, 'name' => $name]);
        }

        // Teachers & ClassSections
        $classSections = [];
        foreach ($classes as $idx => $class) {
            $teacher = User::create([
                'name'      => "Guru {$class->name}",
                'email'     => 'guru' . ($idx + 1) . '@sman1demo.sch.id',
                'password'  => bcrypt('Guru123!'),
                'school_id' => $school->id,
                'is_active' => true,
            ]);
            $teacher->assignRole('teacher');

            Staff::create([
                'school_id'    => $school->id,
                'user_id'      => $teacher->id,
                'department'   => 'Akademik',
                'designation'  => 'Guru Kelas',
                'joining_date' => '2020-07-01',
                'basic_salary' => 350000000, // Rp 3.5jt
            ]);

            foreach ($sections as $section) {
                $cs = ClassSection::create([
                    'school_id'        => $school->id,
                    'class_room_id'    => $class->id,
                    'section_id'       => $section->id,
                    'medium_id'        => $medium->id,
                    'academic_year_id' => $academicYear->id,
                    'class_teacher_id' => $teacher->id,
                ]);
                $classSections[] = $cs;
            }
        }

        // Students (10 per class section)
        $firstNames = ['Budi', 'Dewi', 'Rudi', 'Sari', 'Andi', 'Nina', 'Eko', 'Lisa', 'Agus', 'Wati'];
        $lastNames  = ['Santoso', 'Rahayu', 'Pratama', 'Susanti', 'Wibowo', 'Hidayat', 'Lestari', 'Saputra', 'Kurniawan', 'Handayani'];

        foreach ($classSections as $csIdx => $cs) {
            for ($i = 0; $i < 10; $i++) {
                $name = $firstNames[$i] . ' ' . $lastNames[array_rand($lastNames)];
                $studentUser = User::create([
                    'name'      => $name,
                    'email'     => 'siswa' . $csIdx . '_' . $i . '@sman1demo.sch.id',
                    'password'  => bcrypt('Siswa123!'),
                    'school_id' => $school->id,
                    'is_active' => true,
                ]);
                $studentUser->assignRole('student');

                Student::create([
                    'user_id'          => $studentUser->id,
                    'school_id'        => $school->id,
                    'class_section_id' => $cs->id,
                    'admission_no'     => 'ADM-' . str_pad(($csIdx * 10 + $i + 1), 4, '0', STR_PAD_LEFT),
                    'gender'           => $i % 2 === 0 ? 'male' : 'female',
                ]);
            }
        }

        // Fee Structure
        $feeStructure = FeeStructure::create([
            'school_id' => $school->id,
            'name'      => 'SPP Bulanan',
            'frequency' => 'monthly',
            'amount'    => 25000000, // Rp 250.000
            'is_active' => true,
        ]);

        // Generate invoices for last 3 months
        $students = Student::where('school_id', $school->id)->get();
        foreach (['2024-10', '2024-11', '2024-12'] as $period) {
            $dueDate = Carbon::createFromFormat('Y-m', $period)->endOfMonth()->toDateString();
            foreach ($students as $student) {
                FeeInvoice::create([
                    'school_id'        => $school->id,
                    'student_id'       => $student->id,
                    'fee_structure_id' => $feeStructure->id,
                    'invoice_no'       => 'INV-' . strtoupper(Str::random(8)),
                    'due_date'         => $dueDate,
                    'amount'           => 25000000,
                    'status'           => $period === '2024-10' ? 'paid' : 'unpaid',
                    'period'           => $period,
                ]);
            }
        }

        $this->command->info("Demo school created: {$school->name}");
        $this->command->info("Admin: admin@sman1demo.sch.id / Admin123!");
        $this->command->info("Super Admin: super@sikadpro.app / SuperAdmin123!");
        $this->command->info("Students created: " . $students->count());
    }
}

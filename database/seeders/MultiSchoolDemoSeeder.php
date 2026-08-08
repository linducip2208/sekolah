<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Generate 20 demo schools, each with 900 students + 12 months invoices.
 * Total: ~317,000 records. Idempotent — skips existing schools by subdomain.
 *
 *   php artisan db:seed --class=MultiSchoolDemoSeeder
 */
class MultiSchoolDemoSeeder extends Seeder
{
    private int $totalSchools = 20;
    private int $studentsPerSchool = 900;
    private int $monthsOfInvoices = 12;
    private int $paidPercentage = 30;
    private int $studentsPerSection;

    private string $hashedPassword;

    private array $firstNames = [
        'Budi', 'Dewi', 'Rudi', 'Sari', 'Andi', 'Nina', 'Eko', 'Lisa', 'Agus', 'Wati',
        'Tono', 'Yuni', 'Bambang', 'Indah', 'Hadi', 'Citra', 'Wawan', 'Diah', 'Fajar', 'Mega',
        'Galih', 'Putri', 'Hendra', 'Rina', 'Iwan', 'Ayu', 'Joko', 'Ratna', 'Krisna', 'Sinta',
        'Anton', 'Bella', 'Dimas', 'Elsa', 'Faisal', 'Gita', 'Hanif', 'Ika', 'Jaka', 'Kirana',
    ];
    private array $lastNames = [
        'Santoso', 'Rahayu', 'Pratama', 'Susanti', 'Wibowo', 'Hidayat', 'Lestari',
        'Saputra', 'Kurniawan', 'Handayani', 'Setiawan', 'Maulana', 'Wijaya', 'Prasetyo',
        'Aditya', 'Permana', 'Yulianto', 'Mahendra', 'Nugroho', 'Cahyono',
    ];

    public function run(): void
    {
        $this->studentsPerSection = (int) ceil($this->studentsPerSchool / 9);
        $this->hashedPassword = Hash::make('Password123!');

        $studentRole = Role::where('name', 'student')->first();
        $teacherRole = Role::where('name', 'teacher')->first();
        $adminRole = Role::where('name', 'admin')->first();

        if (!$studentRole || !$teacherRole || !$adminRole) {
            $this->command->error('Roles missing. Run RolePermissionSeeder first.');
            return;
        }

        $planId = DB::table('plans')->where('slug', 'pro')->value('id')
            ?? DB::table('plans')->orderBy('id')->value('id');

        DB::disableQueryLog();

        for ($i = 1; $i <= $this->totalSchools; $i++) {
            $subdomain = sprintf('demo%02d', $i);
            $existing = DB::table('schools')->where('subdomain', $subdomain)->first();
            if ($existing) {
                $this->command->info("[{$i}/{$this->totalSchools}] School {$subdomain} already exists, skipping.");
                continue;
            }

            $start = microtime(true);
            $this->command->info("[{$i}/{$this->totalSchools}] Creating school {$subdomain}...");
            $this->createSchool($i, $subdomain, $planId, $studentRole, $teacherRole, $adminRole);
            $elapsed = round(microtime(true) - $start, 1);
            $this->command->info("    ✓ done in {$elapsed}s");
        }

        $this->command->info("=== ALL DONE ===");
        $this->command->info("Schools: " . DB::table('schools')->count());
        $this->command->info("Users: " . DB::table('users')->count());
        $this->command->info("Students: " . DB::table('students')->count());
        $this->command->info("Invoices: " . DB::table('fee_invoices')->count());
        $this->command->info("Payments: " . DB::table('fee_payments')->count());
    }

    private function createSchool(int $idx, string $subdomain, ?int $planId, Role $studentRole, Role $teacherRole, Role $adminRole): void
    {
        $now = now();
        $schoolName = "Demo School {$idx}";

        $schoolId = DB::table('schools')->insertGetId([
            'name'            => $schoolName,
            'subdomain'       => $subdomain,
            'email'           => "info@{$subdomain}.sikadpro",
            'phone'           => '021-555' . str_pad((string) $idx, 4, '0', STR_PAD_LEFT),
            'address'         => "Jl. Pendidikan No. {$idx}",
            'plan_id'         => $planId,
            'plan_expires_at' => $now->copy()->addYear(),
            'is_active'       => true,
            'timezone'        => 'Asia/Jakarta',
            'locale'          => 'id',
            'settings'        => json_encode([]),
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        $adminId = DB::table('users')->insertGetId([
            'name'      => "Admin {$schoolName}",
            'email'     => "admin@{$subdomain}.sikadpro",
            'password'  => $this->hashedPassword,
            'school_id' => $schoolId,
            'is_active' => true,
            'locale'    => 'id',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('model_has_roles')->insert([
            'role_id' => $adminRole->id, 'model_type' => 'App\\Models\\User', 'model_id' => $adminId,
        ]);

        $academicYearId = DB::table('academic_years')->insertGetId([
            'school_id' => $schoolId, 'name' => '2024/2025',
            'start_date' => '2024-07-01', 'end_date' => '2025-06-30',
            'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);

        $mediumId = DB::table('mediums')->insertGetId([
            'school_id' => $schoolId, 'name' => 'Bahasa Indonesia',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $classIds = [];
        foreach (['Kelas 10', 'Kelas 11', 'Kelas 12'] as $name) {
            $classIds[] = DB::table('class_rooms')->insertGetId([
                'school_id' => $schoolId, 'medium_id' => $mediumId, 'name' => $name,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $sectionIds = [];
        foreach (['A', 'B', 'C'] as $name) {
            $sectionIds[] = DB::table('sections')->insertGetId([
                'school_id' => $schoolId, 'name' => $name,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $teacherIds = [];
        foreach ($classIds as $tIdx => $classRoomId) {
            $tNum = $tIdx + 1;
            $teacherUserId = DB::table('users')->insertGetId([
                'name'      => "Guru {$tNum} - {$schoolName}",
                'email'     => "guru{$tNum}@{$subdomain}.sikadpro",
                'password'  => $this->hashedPassword,
                'school_id' => $schoolId,
                'is_active' => true,
                'locale'    => 'id',
                'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('staffs')->insert([
                'user_id'      => $teacherUserId,
                'school_id'    => $schoolId,
                'employee_id'  => "EMP-{$idx}-{$tNum}",
                'department'   => 'Akademik',
                'designation'  => 'Guru Kelas',
                'joining_date' => '2020-07-01',
                'basic_salary' => 350000000,
                'created_at'   => $now, 'updated_at' => $now,
            ]);
            DB::table('model_has_roles')->insert([
                'role_id' => $teacherRole->id, 'model_type' => 'App\\Models\\User', 'model_id' => $teacherUserId,
            ]);
            $teacherIds[] = $teacherUserId;
        }

        $classSectionIds = [];
        foreach ($classIds as $cIdx => $classRoomId) {
            foreach ($sectionIds as $sectionId) {
                $classSectionIds[] = DB::table('class_sections')->insertGetId([
                    'school_id'        => $schoolId,
                    'class_room_id'    => $classRoomId,
                    'section_id'       => $sectionId,
                    'medium_id'        => $mediumId,
                    'academic_year_id' => $academicYearId,
                    'class_teacher_id' => $teacherIds[$cIdx],
                    'created_at'       => $now, 'updated_at' => $now,
                ]);
            }
        }

        $userInserts = [];
        for ($s = 1; $s <= $this->studentsPerSchool; $s++) {
            $userInserts[] = [
                'name'      => $this->firstNames[array_rand($this->firstNames)] . ' ' . $this->lastNames[array_rand($this->lastNames)],
                'email'     => "siswa{$s}@{$subdomain}.sikadpro",
                'password'  => $this->hashedPassword,
                'school_id' => $schoolId,
                'is_active' => true,
                'locale'    => 'id',
                'created_at' => $now, 'updated_at' => $now,
            ];
        }
        foreach (array_chunk($userInserts, 1000) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        $studentUsers = DB::table('users')
            ->where('school_id', $schoolId)
            ->where('email', 'like', "siswa%@{$subdomain}.sikadpro")
            ->orderBy('id')
            ->select('id')
            ->get();

        $studentInserts = [];
        $roleInserts = [];
        $sectionCount = count($classSectionIds);
        foreach ($studentUsers as $idx2 => $u) {
            $sectionId = $classSectionIds[$idx2 % $sectionCount];
            $studentInserts[] = [
                'user_id'          => $u->id,
                'school_id'        => $schoolId,
                'class_section_id' => $sectionId,
                'admission_no'     => sprintf('S%02d-%05d', $idx, $idx2 + 1),
                'gender'           => $idx2 % 2 === 0 ? 'male' : 'female',
                'created_at'       => $now, 'updated_at' => $now,
            ];
            $roleInserts[] = [
                'role_id' => $studentRole->id, 'model_type' => 'App\\Models\\User', 'model_id' => $u->id,
            ];
        }
        foreach (array_chunk($studentInserts, 1000) as $chunk) {
            DB::table('students')->insert($chunk);
        }
        foreach (array_chunk($roleInserts, 1000) as $chunk) {
            DB::table('model_has_roles')->insert($chunk);
        }

        $feeStructureId = DB::table('fee_structures')->insertGetId([
            'school_id' => $schoolId, 'class_room_id' => null, 'name' => 'SPP Bulanan',
            'frequency' => 'monthly', 'amount' => 25000000, 'is_active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $studentRows = DB::table('students')->where('school_id', $schoolId)->select('id')->get();
        $months = [];
        for ($m = $this->monthsOfInvoices - 1; $m >= 0; $m--) {
            $months[] = now()->subMonths($m)->format('Y-m');
        }

        $invoiceInserts = [];
        foreach ($studentRows as $student) {
            foreach ($months as $period) {
                $isPaid = rand(1, 100) <= $this->paidPercentage;
                $invoiceInserts[] = [
                    'school_id'        => $schoolId,
                    'student_id'       => $student->id,
                    'fee_structure_id' => $feeStructureId,
                    'invoice_no'       => 'INV-' . strtoupper(Str::random(12)),
                    'due_date'         => Carbon::createFromFormat('Y-m', $period)->endOfMonth()->toDateString(),
                    'amount'           => 25000000,
                    'paid_amount'      => $isPaid ? 25000000 : 0,
                    'status'           => $isPaid ? 'paid' : 'unpaid',
                    'period'           => $period,
                    'created_at'       => $now, 'updated_at' => $now,
                ];

                if (count($invoiceInserts) >= 1000) {
                    DB::table('fee_invoices')->insert($invoiceInserts);
                    $invoiceInserts = [];
                }
            }
        }
        if (!empty($invoiceInserts)) {
            DB::table('fee_invoices')->insert($invoiceInserts);
        }

        $methods = ['cash', 'transfer', 'qris', 'va', 'ewallet'];
        $paidInvoices = DB::table('fee_invoices')
            ->where('school_id', $schoolId)
            ->where('status', 'paid')
            ->select('id', 'amount', 'due_date')
            ->cursor();

        $paymentInserts = [];
        foreach ($paidInvoices as $inv) {
            $paymentInserts[] = [
                'fee_invoice_id' => $inv->id,
                'collected_by'   => $adminId,
                'amount'         => $inv->amount,
                'payment_method' => $methods[array_rand($methods)],
                'reference'      => 'TRX-' . strtoupper(Str::random(10)),
                'payment_date'   => $inv->due_date,
                'created_at'     => $now, 'updated_at' => $now,
            ];
            if (count($paymentInserts) >= 1000) {
                DB::table('fee_payments')->insert($paymentInserts);
                $paymentInserts = [];
            }
        }
        if (!empty($paymentInserts)) {
            DB::table('fee_payments')->insert($paymentInserts);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Student;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeeStructure;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Scales demo school sman1demo to ~10,000+ records.
 * Idempotent: only adds delta — safe to re-run.
 *
 *   php artisan db:seed --class=BulkDemoDataSeeder
 */
class BulkDemoDataSeeder extends Seeder
{
    private int $targetStudents = 1000;
    private int $monthsOfInvoices = 12;
    private int $paidPercentage = 30;

    public function run(): void
    {
        $school = School::where('subdomain', 'sman1demo')->first();
        if (!$school) {
            $this->command->error('Demo school sman1demo not found. Run DemoSchoolSeeder first.');
            return;
        }

        $classSections = ClassSection::where('school_id', $school->id)->get();
        if ($classSections->isEmpty()) {
            $this->command->error('No class sections in demo school.');
            return;
        }

        $structure = FeeStructure::where('school_id', $school->id)->first();
        if (!$structure) {
            $this->command->error('No fee structure. Run DemoSchoolSeeder first.');
            return;
        }

        $admin = User::where('email', 'admin@sman1demo.sch.id')->first();

        $now = now();
        $hashedPassword = Hash::make('Siswa123!');
        $studentRole = Role::where('name', 'student')->first();

        $existing = Student::where('school_id', $school->id)->count();
        $toAdd = max(0, $this->targetStudents - $existing);

        $this->command->info("Existing students: {$existing}. Adding: {$toAdd}.");

        if ($toAdd > 0) {
            $this->bulkCreateStudents($school, $classSections, $toAdd, $existing, $hashedPassword, $studentRole, $now);
        }

        $this->bulkCreateInvoicesAndPayments($school, $structure, $admin, $now);

        $this->command->info("=== DONE ===");
        $this->command->info("Students: " . Student::where('school_id', $school->id)->count());
        $this->command->info("Invoices: " . FeeInvoice::where('school_id', $school->id)->count());
        $this->command->info("Payments: " . DB::table('fee_payments')
            ->join('fee_invoices', 'fee_payments.fee_invoice_id', '=', 'fee_invoices.id')
            ->where('fee_invoices.school_id', $school->id)->count());
    }

    private function bulkCreateStudents(
        School $school,
        $classSections,
        int $toAdd,
        int $existing,
        string $hashedPassword,
        Role $studentRole,
        Carbon $now
    ): void {
        $firstNames = ['Budi', 'Dewi', 'Rudi', 'Sari', 'Andi', 'Nina', 'Eko', 'Lisa', 'Agus', 'Wati',
            'Tono', 'Yuni', 'Bambang', 'Indah', 'Hadi', 'Citra', 'Wawan', 'Diah', 'Fajar', 'Mega',
            'Galih', 'Putri', 'Hendra', 'Rina', 'Iwan', 'Ayu', 'Joko', 'Ratna', 'Krisna', 'Sinta'];
        $lastNames = ['Santoso', 'Rahayu', 'Pratama', 'Susanti', 'Wibowo', 'Hidayat', 'Lestari',
            'Saputra', 'Kurniawan', 'Handayani', 'Setiawan', 'Maulana', 'Wijaya', 'Prasetyo',
            'Aditya', 'Permana', 'Yulianto', 'Mahendra', 'Nugroho', 'Cahyono'];

        $batchSize = 500;
        $userIdsForRoles = [];

        for ($offset = 0; $offset < $toAdd; $offset += $batchSize) {
            $batch = min($batchSize, $toAdd - $offset);
            $userInserts = [];

            for ($i = 0; $i < $batch; $i++) {
                $emailNum = $existing + $offset + $i + 1;
                $userInserts[] = [
                    'name'      => $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)],
                    'email'     => "siswa_bulk_{$emailNum}@sman1demo.sch.id",
                    'password'  => $hashedPassword,
                    'school_id' => $school->id,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('users')->insert($userInserts);
        }

        $newUsers = User::where('school_id', $school->id)
            ->where('email', 'like', 'siswa_bulk_%')
            ->orderBy('id')
            ->select('id', 'email')
            ->get();

        $studentInserts = [];
        $roleInserts    = [];
        $sectionsArr    = $classSections->all();
        $sectionCount   = count($sectionsArr);

        foreach ($newUsers as $idx => $user) {
            $section = $sectionsArr[$idx % $sectionCount];
            $studentInserts[] = [
                'user_id'          => $user->id,
                'school_id'        => $school->id,
                'class_section_id' => $section->id,
                'admission_no'     => 'BULK-' . str_pad($existing + $idx + 1, 5, '0', STR_PAD_LEFT),
                'gender'           => $idx % 2 === 0 ? 'male' : 'female',
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
            $roleInserts[] = [
                'role_id'    => $studentRole->id,
                'model_type' => 'App\\Models\\User',
                'model_id'   => $user->id,
            ];
        }

        foreach (array_chunk($studentInserts, 500) as $chunk) {
            DB::table('students')->insert($chunk);
        }
        foreach (array_chunk($roleInserts, 500) as $chunk) {
            DB::table('model_has_roles')->insert($chunk);
        }

        $this->command->info("✓ Added {$toAdd} students.");
    }

    private function bulkCreateInvoicesAndPayments(
        School $school,
        FeeStructure $structure,
        ?User $admin,
        Carbon $now
    ): void {
        $allStudents = Student::where('school_id', $school->id)->select('id')->get();
        $studentCount = $allStudents->count();
        $expectedInvoices = $studentCount * $this->monthsOfInvoices;
        $existingInvoices = FeeInvoice::where('school_id', $school->id)->count();

        if ($existingInvoices >= $expectedInvoices) {
            $this->command->info("Invoices already at target ({$existingInvoices}). Skipping.");
            return;
        }

        $months = [];
        for ($m = $this->monthsOfInvoices - 1; $m >= 0; $m--) {
            $months[] = now()->subMonths($m)->format('Y-m');
        }

        $existingPeriods = FeeInvoice::where('school_id', $school->id)
            ->select('student_id', 'period')->get()
            ->groupBy('student_id')
            ->map(fn ($items) => $items->pluck('period')->toArray())
            ->toArray();

        $invoiceInserts = [];

        foreach ($allStudents as $student) {
            $studentExisting = $existingPeriods[$student->id] ?? [];
            foreach ($months as $period) {
                if (in_array($period, $studentExisting, true)) continue;

                $isPaid = rand(1, 100) <= $this->paidPercentage;
                $invoiceInserts[] = [
                    'school_id'        => $school->id,
                    'student_id'       => $student->id,
                    'fee_structure_id' => $structure->id,
                    'invoice_no'       => 'INV-' . strtoupper(Str::random(10)),
                    'due_date'         => Carbon::createFromFormat('Y-m', $period)->endOfMonth()->toDateString(),
                    'amount'           => 25000000,
                    'paid_amount'      => $isPaid ? 25000000 : 0,
                    'status'           => $isPaid ? 'paid' : 'unpaid',
                    'period'           => $period,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }
        }

        $totalToInsert = count($invoiceInserts);
        $this->command->info("Inserting {$totalToInsert} new invoices...");

        foreach (array_chunk($invoiceInserts, 500) as $chunk) {
            DB::table('fee_invoices')->insert($chunk);
        }

        $this->command->info("✓ Added invoices.");

        if (!$admin) {
            $this->command->warn("No admin user found — skipping payment generation.");
            return;
        }

        $unpaidPayments = FeeInvoice::where('school_id', $school->id)
            ->where('status', 'paid')
            ->whereNotIn('id', function ($q) {
                $q->select('fee_invoice_id')->from('fee_payments');
            })
            ->select('id', 'amount', 'due_date')
            ->get();

        if ($unpaidPayments->isEmpty()) {
            $this->command->info("No new payments needed.");
            return;
        }

        $paymentInserts = [];
        $methods = ['cash', 'transfer', 'qris', 'va', 'ewallet'];

        foreach ($unpaidPayments as $inv) {
            $paymentInserts[] = [
                'fee_invoice_id' => $inv->id,
                'collected_by'   => $admin->id,
                'amount'         => $inv->amount,
                'payment_method' => $methods[array_rand($methods)],
                'reference'      => 'TRX-' . strtoupper(Str::random(10)),
                'payment_date'   => $inv->due_date,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        foreach (array_chunk($paymentInserts, 500) as $chunk) {
            DB::table('fee_payments')->insert($chunk);
        }

        $this->command->info("✓ Added " . count($paymentInserts) . " payments.");
    }
}

<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Top-up every demo* school + sman1demo to exactly $targetPerSchool students.
 * Idempotent: only adds the delta. Bulk insert chunked for performance.
 *
 *   php artisan db:seed --class=TopUpStudentsSeeder
 */
class TopUpStudentsSeeder extends Seeder
{
    private int $targetPerSchool = 1000;
    private int $monthsOfInvoices = 12;
    private int $paidPercentage = 30;

    private string $hashedPassword;

    private array $firstNames = [
        'Budi', 'Dewi', 'Rudi', 'Sari', 'Andi', 'Nina', 'Eko', 'Lisa', 'Agus', 'Wati',
        'Tono', 'Yuni', 'Bambang', 'Indah', 'Hadi', 'Citra', 'Wawan', 'Diah', 'Fajar', 'Mega',
        'Galih', 'Putri', 'Hendra', 'Rina', 'Iwan', 'Ayu', 'Joko', 'Ratna', 'Krisna', 'Sinta',
    ];
    private array $lastNames = [
        'Santoso', 'Rahayu', 'Pratama', 'Susanti', 'Wibowo', 'Hidayat', 'Lestari',
        'Saputra', 'Kurniawan', 'Handayani', 'Setiawan', 'Maulana', 'Wijaya', 'Prasetyo',
    ];

    public function run(): void
    {
        $this->hashedPassword = Hash::make('Password123!');

        $studentRole = Role::where('name', 'student')->first();
        if (!$studentRole) {
            $this->command->error('Student role missing. Run RolePermissionSeeder first.');
            return;
        }

        DB::disableQueryLog();

        $schools = DB::table('schools')
            ->where('subdomain', 'like', 'demo%')
            ->orWhere('subdomain', 'sman1demo')
            ->orderBy('subdomain')
            ->get(['id', 'subdomain', 'name']);

        $totalAdded = 0;
        $totalInvoices = 0;
        $totalPayments = 0;

        foreach ($schools as $school) {
            $current = DB::table('students')->where('school_id', $school->id)->count();
            $delta = $this->targetPerSchool - $current;
            if ($delta <= 0) {
                $this->command->info("[{$school->subdomain}] already at {$current} — skip.");
                continue;
            }

            $start = microtime(true);
            $this->command->info("[{$school->subdomain}] {$current} → {$this->targetPerSchool} (+{$delta})");
            $stats = $this->topUpSchool($school, $current, $delta, $studentRole);
            $elapsed = round(microtime(true) - $start, 1);
            $this->command->info("    +{$stats['students']} siswa · +{$stats['invoices']} invoice · +{$stats['payments']} payment · {$elapsed}s");
            $totalAdded += $stats['students'];
            $totalInvoices += $stats['invoices'];
            $totalPayments += $stats['payments'];
        }

        $this->command->info("=== DONE ===");
        $this->command->info("Total students added: {$totalAdded}");
        $this->command->info("Total invoices added: {$totalInvoices}");
        $this->command->info("Total payments added: {$totalPayments}");
        $this->command->info("Schools snapshot:");
        foreach ($schools as $school) {
            $count = DB::table('students')->where('school_id', $school->id)->count();
            $this->command->info("  - {$school->subdomain}: {$count} siswa");
        }
    }

    /** @return array{students:int,invoices:int,payments:int} */
    private function topUpSchool(object $school, int $existingCount, int $delta, Role $studentRole): array
    {
        $now = now();

        $classSections = DB::table('class_sections')->where('school_id', $school->id)->pluck('id')->all();
        if (empty($classSections)) {
            $this->command->warn("    no class_sections — skip");
            return ['students' => 0, 'invoices' => 0, 'payments' => 0];
        }

        $feeStructureId = DB::table('fee_structures')->where('school_id', $school->id)->value('id');
        if (!$feeStructureId) {
            $this->command->warn("    no fee_structure — skip");
            return ['students' => 0, 'invoices' => 0, 'payments' => 0];
        }

        $emailPrefix = $school->subdomain === 'sman1demo' ? 'siswa_extra_' : 'siswa';
        $emailDomain = $school->subdomain === 'sman1demo' ? 'sman1demo.sch.id' : "{$school->subdomain}.eschool";

        $existingEmails = DB::table('users')
            ->where('school_id', $school->id)
            ->where('email', 'like', "{$emailPrefix}%@{$emailDomain}")
            ->pluck('email')
            ->all();

        $userInserts = [];
        $emailNum = $existingCount;
        $created = 0;
        while ($created < $delta) {
            $emailNum++;
            $email = "{$emailPrefix}{$emailNum}@{$emailDomain}";
            if (in_array($email, $existingEmails, true)) continue;

            $userInserts[] = [
                'name'      => $this->firstNames[array_rand($this->firstNames)] . ' ' . $this->lastNames[array_rand($this->lastNames)],
                'email'     => $email,
                'password'  => $this->hashedPassword,
                'school_id' => $school->id,
                'is_active' => true,
                'locale'    => 'id',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $created++;
        }

        foreach (array_chunk($userInserts, 1000) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        $newUserIds = DB::table('users')
            ->where('school_id', $school->id)
            ->whereIn('email', array_column($userInserts, 'email'))
            ->orderBy('id')
            ->pluck('id', 'email')
            ->all();

        $studentInserts = [];
        $roleInserts = [];
        $sectionCount = count($classSections);
        $idx = 0;
        foreach ($newUserIds as $userId) {
            $sectionId = $classSections[$idx % $sectionCount];
            $studentInserts[] = [
                'user_id'          => $userId,
                'school_id'        => $school->id,
                'class_section_id' => $sectionId,
                'admission_no'     => sprintf('TOPUP-%s-%05d', strtoupper(substr($school->subdomain, 0, 6)), $existingCount + $idx + 1),
                'gender'           => $idx % 2 === 0 ? 'male' : 'female',
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
            $roleInserts[] = [
                'role_id' => $studentRole->id, 'model_type' => 'App\\Models\\User', 'model_id' => $userId,
            ];
            $idx++;
        }

        foreach (array_chunk($studentInserts, 1000) as $chunk) {
            DB::table('students')->insert($chunk);
        }
        foreach (array_chunk($roleInserts, 1000) as $chunk) {
            DB::table('model_has_roles')->insert($chunk);
        }

        $newStudentIds = DB::table('students')
            ->where('school_id', $school->id)
            ->whereIn('user_id', array_values($newUserIds))
            ->pluck('id')
            ->all();

        $months = [];
        for ($m = $this->monthsOfInvoices - 1; $m >= 0; $m--) {
            $months[] = now()->subMonths($m)->format('Y-m');
        }

        $invoiceInserts = [];
        $invoicesAdded = 0;
        foreach ($newStudentIds as $sid) {
            foreach ($months as $period) {
                $isPaid = rand(1, 100) <= $this->paidPercentage;
                $invoiceInserts[] = [
                    'school_id'        => $school->id,
                    'student_id'       => $sid,
                    'fee_structure_id' => $feeStructureId,
                    'invoice_no'       => 'INV-' . strtoupper(Str::random(12)),
                    'due_date'         => Carbon::createFromFormat('Y-m', $period)->endOfMonth()->toDateString(),
                    'amount'           => 25000000,
                    'paid_amount'      => $isPaid ? 25000000 : 0,
                    'status'           => $isPaid ? 'paid' : 'unpaid',
                    'period'           => $period,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
                $invoicesAdded++;

                if (count($invoiceInserts) >= 1000) {
                    DB::table('fee_invoices')->insert($invoiceInserts);
                    $invoiceInserts = [];
                }
            }
        }
        if (!empty($invoiceInserts)) {
            DB::table('fee_invoices')->insert($invoiceInserts);
        }

        $adminId = DB::table('users')
            ->where('school_id', $school->id)
            ->where(function ($q) {
                $q->where('email', 'like', 'admin@%');
            })
            ->value('id');

        $paymentsAdded = 0;
        if ($adminId) {
            $methods = ['cash', 'transfer', 'qris', 'va', 'ewallet'];
            $paidInvoices = DB::table('fee_invoices')
                ->where('school_id', $school->id)
                ->whereIn('student_id', $newStudentIds)
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
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
                $paymentsAdded++;
                if (count($paymentInserts) >= 1000) {
                    DB::table('fee_payments')->insert($paymentInserts);
                    $paymentInserts = [];
                }
            }
            if (!empty($paymentInserts)) {
                DB::table('fee_payments')->insert($paymentInserts);
            }
        }

        return [
            'students' => count($newUserIds),
            'invoices' => $invoicesAdded,
            'payments' => $paymentsAdded,
        ];
    }
}

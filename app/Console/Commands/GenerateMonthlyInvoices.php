<?php

namespace App\Console\Commands;

use App\Models\Academic\Student;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeeStructure;
use App\Models\School;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateMonthlyInvoices extends Command
{
    protected $signature   = 'fee:generate-monthly {--period= : Period in Y-m format (default: current month)}';
    protected $description = 'Generate monthly fee invoices for all active students in all schools';

    public function handle(): int
    {
        $period = $this->option('period') ?? now()->format('Y-m');

        if (!preg_match('/^\d{4}-\d{2}$/', $period)) {
            $this->error("Invalid period format. Use Y-m (e.g. 2025-01)");
            return self::FAILURE;
        }

        $this->info("Generating invoices for period: {$period}");

        $schools  = School::where('is_active', true)->get();
        $total    = 0;
        $bar      = $this->output->createProgressBar($schools->count());

        foreach ($schools as $school) {
            $structures = FeeStructure::where('school_id', $school->id)
                ->where('frequency', 'monthly')
                ->where('is_active', true)
                ->get();

            if ($structures->isEmpty()) {
                $bar->advance();
                continue;
            }

            $students = Student::where('school_id', $school->id)->get();
            $dueDate  = now()->setDateFrom(\Carbon\Carbon::createFromFormat('Y-m', $period))->endOfMonth()->toDateString();

            foreach ($students as $student) {
                foreach ($structures as $structure) {
                    $exists = FeeInvoice::where('school_id', $school->id)
                        ->where('student_id', $student->id)
                        ->where('fee_structure_id', $structure->id)
                        ->where('period', $period)
                        ->exists();

                    if (!$exists) {
                        FeeInvoice::create([
                            'school_id'        => $school->id,
                            'student_id'       => $student->id,
                            'fee_structure_id' => $structure->id,
                            'invoice_no'       => 'INV-' . strtoupper(Str::random(8)),
                            'due_date'         => $dueDate,
                            'amount'           => $structure->amount,
                            'status'           => 'unpaid',
                            'period'           => $period,
                        ]);
                        $total++;
                    }
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Generated {$total} invoice(s) for {$period}.");

        return self::SUCCESS;
    }
}

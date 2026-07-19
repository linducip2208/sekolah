<?php

namespace App\Services\Finance;

use App\Models\Academic\Student;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeeStructure;
use Illuminate\Support\Facades\DB;

class FeeService
{
    public function generateMonthlyInvoices(int $schoolId, string $period): int
    {
        $structures = FeeStructure::where('school_id', $schoolId)
            ->where('is_active', true)
            ->where('frequency', 'monthly')
            ->get();

        $count = 0;

        foreach ($structures as $structure) {
            $students = Student::where('school_id', $schoolId)
                ->when($structure->class_room_id, function ($q) use ($structure) {
                    $q->whereHas('classSection', fn($s) => $s->where('class_room_id', $structure->class_room_id));
                })
                ->get();

            foreach ($students as $student) {
                $exists = FeeInvoice::where('student_id', $student->id)
                    ->where('fee_structure_id', $structure->id)
                    ->where('period', $period)
                    ->exists();

                if ($exists) {
                    continue;
                }

                FeeInvoice::create([
                    'school_id'        => $schoolId,
                    'student_id'       => $student->id,
                    'fee_structure_id' => $structure->id,
                    'invoice_no'       => 'INV-' . $schoolId . '-' . $student->id . '-' . $period . '-' . $structure->id,
                    'due_date'         => now()->endOfMonth(),
                    'amount'           => $structure->amount,
                    'status'           => 'unpaid',
                    'period'           => $period,
                ]);

                $count++;
            }
        }

        return $count;
    }

    public function recordPayment(int $invoiceId, int $amount, int $collectedBy, string $method = 'cash'): FeeInvoice
    {
        return DB::transaction(function () use ($invoiceId, $amount, $collectedBy, $method) {
            $invoice = FeeInvoice::lockForUpdate()->findOrFail($invoiceId);

            $invoice->payments()->create([
                'fee_invoice_id' => $invoiceId,
                'collected_by'   => $collectedBy,
                'amount'         => $amount,
                'payment_method' => $method,
                'payment_date'   => today(),
            ]);

            $totalPaid = $invoice->payments()->sum('amount');
            $invoice->update([
                'paid_amount' => $totalPaid,
                'status'      => $totalPaid >= ($invoice->amount - $invoice->discount)
                    ? 'paid'
                    : 'partial',
            ]);

            return $invoice->fresh();
        });
    }
}

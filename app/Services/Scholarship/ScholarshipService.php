<?php

namespace App\Services\Scholarship;

use App\Models\Finance\FeeInvoice;
use App\Models\Scholarship\ScholarshipApplication;
use App\Models\Scholarship\ScholarshipGrant;
use App\Models\Scholarship\ScholarshipProgram;
use Illuminate\Support\Facades\DB;

class ScholarshipService
{
    public function apply(int $schoolId, int $programId, int $studentId, array $data): ScholarshipApplication
    {
        return ScholarshipApplication::create([
            'school_id'              => $schoolId,
            'scholarship_program_id' => $programId,
            'student_id'             => $studentId,
            'documents'              => $data['documents'] ?? [],
            'motivation'             => $data['motivation'] ?? null,
            'status'                 => 'submitted',
        ]);
    }

    public function grant(ScholarshipApplication $app, int $reviewerId, ?string $note = null): ScholarshipApplication
    {
        $app->update([
            'status'         => 'granted',
            'reviewer_id'    => $reviewerId,
            'reviewer_note'  => $note,
            'granted_from'   => $app->granted_from ?? today(),
            'granted_until'  => $app->granted_until ?? today()->addYear(),
        ]);
        return $app->fresh();
    }

    public function applyToInvoice(FeeInvoice $invoice, ScholarshipApplication $app): FeeInvoice
    {
        if ($app->status !== 'granted') {
            throw new \RuntimeException('Beasiswa belum di-grant');
        }

        $program = ScholarshipProgram::find($app->scholarship_program_id);
        if (!$program) throw new \RuntimeException('Program tidak ditemukan');

        return DB::transaction(function () use ($invoice, $app, $program) {
            $remaining = $invoice->amount - $invoice->discount - $invoice->paid_amount;

            $discount = match ($program->discount_type) {
                'full'       => $remaining,
                'fixed'      => min((int) $program->discount_value, $remaining),
                'percentage' => intdiv($remaining * (int) $program->discount_value, 100),
            };

            $invoice->increment('discount', $discount);

            ScholarshipGrant::create([
                'school_id'                  => $invoice->school_id,
                'scholarship_application_id' => $app->id,
                'student_id'                 => $invoice->student_id,
                'fee_invoice_id'             => $invoice->id,
                'discount_applied'           => $discount,
                'applied_at'                 => today(),
            ]);

            return $invoice->fresh();
        });
    }
}

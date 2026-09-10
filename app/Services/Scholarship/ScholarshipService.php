<?php

namespace App\Services\Scholarship;

use App\Models\Academic\Student;
use App\Models\Finance\FeeInvoice;
use App\Models\Scholarship\ScholarshipApplication;
use App\Models\Scholarship\ScholarshipGrant;
use App\Models\Scholarship\ScholarshipProgram;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ScholarshipService
{
    public function apply(int $schoolId, int $programId, int $studentId, array $data): ScholarshipApplication
    {
        $this->assertSchoolAccess($schoolId);

        return DB::transaction(function () use ($schoolId, $programId, $studentId, $data) {
            $program = ScholarshipProgram::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->lockForUpdate()
                ->findOrFail($programId);

            Student::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->findOrFail($studentId);

            abort_unless($program->is_active, 422, 'Program beasiswa tidak aktif.');
            abort_unless(
                today()->between($program->open_date, $program->close_date),
                422,
                'Periode pendaftaran beasiswa sudah ditutup.'
            );

            $duplicate = ScholarshipApplication::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->where('scholarship_program_id', $program->id)
                ->where('student_id', $studentId)
                ->whereNotIn('status', ['rejected', 'withdrawn'])
                ->exists();
            abort_if($duplicate, 422, 'Siswa sudah memiliki pengajuan pada program ini.');

            if ($program->quota !== null) {
                $granted = ScholarshipApplication::withoutGlobalScopes()
                    ->where('school_id', $schoolId)
                    ->where('scholarship_program_id', $program->id)
                    ->where('status', 'granted')
                    ->count();
                abort_if($granted >= $program->quota, 422, 'Kuota program beasiswa sudah penuh.');
            }

            return ScholarshipApplication::create([
                'school_id' => $schoolId,
                'scholarship_program_id' => $programId,
                'student_id' => $studentId,
                'documents' => $data['documents'] ?? [],
                'motivation' => $data['motivation'] ?? null,
                'status' => 'submitted',
            ]);
        });
    }

    public function grant(ScholarshipApplication $app, int $reviewerId, ?string $note = null): ScholarshipApplication
    {
        $schoolId = (int) $app->school_id;
        $this->assertSchoolAccess($schoolId);

        return DB::transaction(function () use ($app, $reviewerId, $note, $schoolId) {
            $app = ScholarshipApplication::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->lockForUpdate()
                ->findOrFail($app->id);
            $program = ScholarshipProgram::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->lockForUpdate()
                ->findOrFail($app->scholarship_program_id);
            User::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($reviewerId);
            Student::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($app->student_id);

            if ($app->status === 'granted') {
                return $app->fresh();
            }
            abort_if(in_array($app->status, ['rejected', 'withdrawn'], true), 422, 'Pengajuan beasiswa sudah berstatus final.');

            if ($program->quota !== null) {
                $granted = ScholarshipApplication::withoutGlobalScopes()
                    ->where('school_id', $schoolId)
                    ->where('scholarship_program_id', $program->id)
                    ->where('status', 'granted')
                    ->where('id', '<>', $app->id)
                    ->count();
                abort_if($granted >= $program->quota, 422, 'Kuota program beasiswa sudah penuh.');
            }

            $app->update([
                'status' => 'granted',
                'reviewer_id' => $reviewerId,
                'reviewer_note' => $note,
                'granted_from' => $app->granted_from ?? today(),
                'granted_until' => $app->granted_until ?? today()->addYear(),
            ]);

            return $app->fresh();
        });
    }

    public function applyToInvoice(FeeInvoice $invoice, ScholarshipApplication $app): FeeInvoice
    {
        $schoolId = (int) $invoice->school_id;
        $this->assertSchoolAccess($schoolId);

        return DB::transaction(function () use ($invoice, $app, $schoolId) {
            $invoice = FeeInvoice::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->lockForUpdate()
                ->findOrFail($invoice->id);
            $app = ScholarshipApplication::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->lockForUpdate()
                ->findOrFail($app->id);
            abort_unless((int) $app->student_id === (int) $invoice->student_id, 422, 'Beasiswa dan invoice harus dimiliki siswa yang sama.');

            if ($app->status !== 'granted') {
                throw new \RuntimeException('Beasiswa belum di-grant');
            }

            $program = ScholarshipProgram::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->findOrFail($app->scholarship_program_id);

            $existing = ScholarshipGrant::where('school_id', $invoice->school_id)
                ->where('scholarship_application_id', $app->id)
                ->where('fee_invoice_id', $invoice->id)
                ->exists();
            if ($existing) {
                return $invoice->fresh();
            }

            $remaining = $invoice->amount - $invoice->discount - $invoice->paid_amount;
            abort_if($remaining <= 0, 422, 'Invoice tidak memiliki saldo yang dapat diberi discount.');

            $discount = match ($program->discount_type) {
                'full' => $remaining,
                'fixed' => min((int) $program->discount_value, $remaining),
                'percentage' => intdiv($remaining * (int) $program->discount_value, 100),
            };

            $invoice->increment('discount', $discount);

            ScholarshipGrant::create([
                'school_id' => $invoice->school_id,
                'scholarship_application_id' => $app->id,
                'student_id' => $invoice->student_id,
                'fee_invoice_id' => $invoice->id,
                'discount_applied' => $discount,
                'applied_at' => today(),
            ]);

            return $invoice->fresh();
        });
    }

    private function assertSchoolAccess(int $schoolId): void
    {
        if (auth()->check() && (int) auth()->user()->school_id !== $schoolId && ! auth()->user()->hasRole('super_admin')) {
            abort(403, 'Akses sekolah tidak valid.');
        }
    }
}

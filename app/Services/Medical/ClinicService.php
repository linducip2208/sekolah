<?php

namespace App\Services\Medical;

use App\Models\Medical\ClinicVisit;
use App\Models\Medical\MedicalRecord;
use App\Models\Medical\Vaccination;
use Illuminate\Support\Facades\DB;

class ClinicService
{
    public function getOrCreateRecord(int $schoolId, int $studentId): MedicalRecord
    {
        return MedicalRecord::firstOrCreate(
            ['school_id' => $schoolId, 'student_id' => $studentId],
            [],
        );
    }

    public function updateRecord(MedicalRecord $record, array $data): MedicalRecord
    {
        $record->update($data);
        return $record->fresh();
    }

    public function recordVisit(int $schoolId, int $studentId, int $attendedBy, array $data): ClinicVisit
    {
        return DB::transaction(function () use ($schoolId, $studentId, $attendedBy, $data) {
            $visit = ClinicVisit::create([
                'school_id'         => $schoolId,
                'student_id'        => $studentId,
                'attended_by'       => $attendedBy,
                'visit_at'          => $data['visit_at'] ?? now(),
                'symptoms'          => $data['symptoms'],
                'diagnosis'         => $data['diagnosis'] ?? null,
                'treatment'         => $data['treatment'] ?? null,
                'medications_given' => $data['medications_given'] ?? [],
                'temperature_c'     => $data['temperature_c'] ?? null,
                'blood_pressure'    => $data['blood_pressure'] ?? null,
                'returned_to_class' => $data['returned_to_class'] ?? true,
                'sent_home'         => $data['sent_home'] ?? false,
                'referred_external' => $data['referred_external'] ?? false,
                'referred_to'       => $data['referred_to'] ?? null,
            ]);

            if ($visit->sent_home || $visit->referred_external) {
                $this->notifyParent($visit);
            }

            return $visit;
        });
    }

    public function recordVaccination(int $schoolId, int $studentId, array $data): Vaccination
    {
        return Vaccination::create([
            'school_id'        => $schoolId,
            'student_id'       => $studentId,
            'vaccine_name'     => $data['vaccine_name'],
            'vaccinated_at'    => $data['vaccinated_at'],
            'batch_number'     => $data['batch_number'] ?? null,
            'administered_by'  => $data['administered_by'] ?? null,
            'next_dose_due'    => $data['next_dose_due'] ?? null,
            'certificate_path' => $data['certificate_path'] ?? null,
        ]);
    }

    protected function notifyParent(ClinicVisit $visit): void
    {
        $visit->update(['parent_notified' => true]);
        \App\Jobs\NotifyParentClinicVisitJob::dispatch($visit->id);
    }
}

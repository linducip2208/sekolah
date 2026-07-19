<?php

namespace App\Services\Dapodik;

use App\Models\Academic\Student;
use App\Models\Dapodik\DapodikConfig;
use App\Models\Dapodik\DapodikSyncLog;
use Illuminate\Support\Facades\DB;

class DapodikService
{
    public function getOrCreateConfig(int $schoolId): DapodikConfig
    {
        return DapodikConfig::firstOrCreate(['school_id' => $schoolId], []);
    }

    public function importStudentsFromCsv(int $schoolId, int $triggerUserId, string $csvContent): DapodikSyncLog
    {
        $log = DapodikSyncLog::create([
            'school_id'    => $schoolId,
            'direction'    => 'import',
            'entity'       => 'students',
            'status'       => 'running',
            'triggered_by' => $triggerUserId,
        ]);

        try {
            $rows  = $this->parseCsv($csvContent);
            $total = count($rows);
            $ok    = 0;
            $fail  = 0;
            $errors = [];

            DB::transaction(function () use ($schoolId, $rows, &$ok, &$fail, &$errors) {
                foreach ($rows as $i => $row) {
                    try {
                        // Validate NISN (10 digits) & NIK (16 digits) per Dapodik spec
                        if (!empty($row['nisn']) && !preg_match('/^\d{10}$/', $row['nisn'])) {
                            throw new \RuntimeException('NISN must be 10 digits');
                        }
                        if (!empty($row['nik']) && !preg_match('/^\d{16}$/', $row['nik'])) {
                            throw new \RuntimeException('NIK must be 16 digits');
                        }

                        Student::updateOrCreate(
                            ['school_id' => $schoolId, 'admission_no' => $row['admission_no'] ?? $row['nisn'] ?? null],
                            array_filter([
                                'school_id'      => $schoolId,
                                'admission_no'   => $row['admission_no'] ?? $row['nisn'] ?? null,
                                'admission_date' => $row['admission_date'] ?? null,
                                'date_of_birth'  => $row['date_of_birth'] ?? null,
                                'gender'         => $row['gender'] ?? null,
                                'address'        => $row['address'] ?? null,
                            ], fn ($v) => $v !== null),
                        );
                        $ok++;
                    } catch (\Throwable $e) {
                        $fail++;
                        $errors[] = ['row' => $i, 'error' => $e->getMessage()];
                    }
                }
            });

            $log->update([
                'status'          => 'completed',
                'records_total'   => $total,
                'records_success' => $ok,
                'records_failed'  => $fail,
                'errors'          => $errors,
            ]);
        } catch (\Throwable $e) {
            $log->update([
                'status' => 'failed',
                'errors' => [['error' => $e->getMessage()]],
            ]);
        }

        return $log->fresh();
    }

    public function exportStudentsToCsv(int $schoolId): string
    {
        $students = Student::where('school_id', $schoolId)->get();

        $rows = [['admission_no', 'name', 'date_of_birth', 'gender', 'address']];
        foreach ($students as $s) {
            $rows[] = [
                $s->admission_no,
                $s->user?->name ?? '',
                $s->date_of_birth?->format('Y-m-d'),
                $s->gender,
                $s->address,
            ];
        }

        $csv = '';
        foreach ($rows as $r) {
            $csv .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', $r)) . "\n";
        }
        return $csv;
    }

    protected function parseCsv(string $content): array
    {
        $lines = explode("\n", trim($content));
        if (count($lines) < 2) return [];

        $headers = str_getcsv(array_shift($lines));
        $rows    = [];
        foreach ($lines as $line) {
            if (trim($line) === '') continue;
            $values = str_getcsv($line);
            $rows[] = array_combine($headers, array_pad($values, count($headers), null));
        }
        return $rows;
    }
}

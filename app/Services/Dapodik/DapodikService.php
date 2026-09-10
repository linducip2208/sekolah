<?php

namespace App\Services\Dapodik;

use App\Jobs\RunDapodikSyncJob;
use App\Models\Academic\Student;
use App\Models\Dapodik\DapodikConfig;
use App\Models\Dapodik\DapodikConnection;
use App\Models\Dapodik\DapodikEntityMapping;
use App\Models\Dapodik\DapodikSyncItem;
use App\Models\Dapodik\DapodikSyncLog;
use App\Models\Dapodik\DapodikSyncRun;
use App\Models\User;
use App\Services\Integrations\Dapodik\DapodikClientInterface;
use App\Services\Integrations\Dapodik\DapodikRestClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Dapodik orchestration: fetch/normalize/preview/confirm/sync. External
 * identifiers are mandatory for matching; names are never used as keys.
 */
class DapodikService
{
    public function getOrCreateConfig(int $schoolId): DapodikConfig
    {
        return DapodikConfig::withoutGlobalScopes()->firstOrCreate(['school_id' => $schoolId], ['npsn' => '']);
    }

    public function getOrCreateConnection(int $schoolId): DapodikConnection
    {
        $connection = DapodikConnection::withoutGlobalScopes()->firstWhere('school_id', $schoolId);
        if ($connection) {
            return $connection;
        }

        $legacy = $this->getOrCreateConfig($schoolId);
        $connection = DapodikConnection::withoutGlobalScopes()->create([
            'school_id' => $schoolId,
            'connection_type' => 'rest',
            'host' => $legacy->endpoint_url,
            'npsn' => $legacy->npsn,
            'field_mappings' => $legacy->field_mappings,
            'last_sync_at' => $legacy->last_sync_at,
            'status' => $legacy->endpoint_url ? 'configured' : 'unconfigured',
        ]);
        foreach (['username', 'password'] as $secret) {
            if ($legacy->{$secret}) {
                $connection->setSecret($secret, $legacy->{$secret});
            }
        }
        $connection->save();

        return $connection;
    }

    public function client(?DapodikClientInterface $client = null): DapodikClientInterface
    {
        return $client ?? app(DapodikRestClient::class);
    }

    public function testConnection(int $schoolId, ?DapodikClientInterface $client = null): array
    {
        $connection = $this->getOrCreateConnection($schoolId);
        $result = $this->client($client)->testConnection($connection);
        $connection->update([
            'status' => $result['ok'] ? 'connected' : 'error',
            'last_tested_at' => now(),
        ]);

        return $result;
    }

    public function previewFromRemote(int $schoolId, int $userId, string $entityType, ?DapodikClientInterface $client = null): DapodikSyncRun
    {
        $connection = $this->getOrCreateConnection($schoolId);
        $records = $this->client($client)->fetch($connection, $entityType);

        return $this->createPreview($schoolId, $userId, $entityType, $records);
    }

    public function createPreview(int $schoolId, int $userId, string $entityType, array $records): DapodikSyncRun
    {
        abort_unless(in_array($entityType, ['students', 'staff', 'class_sections', 'subjects'], true), 422, 'Entitas Dapodik tidak didukung.');

        $run = DapodikSyncRun::withoutGlobalScopes()->create([
            'run_uuid' => (string) Str::uuid(),
            'school_id' => $schoolId,
            'entity_type' => $entityType,
            'direction' => 'import',
            'status' => 'preview',
            'initiated_by' => $userId,
            'total' => count($records),
        ]);

        foreach ($records as $record) {
            $normalized = $this->normalize($schoolId, $entityType, $record);
            $externalId = (string) ($normalized['external_id'] ?? '');
            $status = $externalId === '' ? 'failed' : $this->previewStatus($schoolId, $entityType, $externalId, $normalized);

            DapodikSyncItem::withoutGlobalScopes()->create([
                'school_id' => $schoolId,
                'dapodik_sync_run_id' => $run->id,
                'entity_type' => $entityType,
                'external_id' => $externalId !== '' ? $externalId : 'missing-'.Str::uuid(),
                'local_type' => $this->localType($entityType),
                'local_id' => $this->mappedLocalId($schoolId, $entityType, $externalId),
                'status' => $status,
                'source_payload' => $record,
                'normalized_payload' => $normalized,
                'error_message' => $externalId === '' ? 'external_id/dapodik_id wajib diisi.' : null,
            ]);
        }

        return $run->load('items');
    }

    public function confirm(DapodikSyncRun $run): DapodikSyncRun
    {
        abort_if($run->status !== 'preview', 422, 'Run Dapodik ini sudah dikonfirmasi atau diproses.');
        $run->update(['status' => 'queued']);
        RunDapodikSyncJob::dispatch($run->id);

        return $run->fresh('items');
    }

    public function syncPreviewedRun(DapodikSyncRun $run): DapodikSyncRun
    {
        $run->update(['status' => 'running', 'started_at' => now()]);
        $counts = ['inserted' => 0, 'updated' => 0, 'unchanged' => 0, 'conflicted' => 0, 'failed' => 0];

        foreach ($run->items()->orderBy('id')->cursor() as $item) {
            if ($item->status === 'failed') {
                $counts['failed']++;

                continue;
            }

            try {
                $result = $this->syncItem($run->school_id, $item);
                $item->update([
                    'status' => $result['status'],
                    'local_type' => $result['local_type'],
                    'local_id' => $result['local_id'],
                    'local_snapshot' => $result['snapshot'],
                    'error_message' => null,
                ]);
                $counts[$result['status']]++;
            } catch (\Throwable $e) {
                $item->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
                $counts['failed']++;
            }
        }

        $run->update([
            'status' => $counts['failed'] > 0 ? 'completed_with_errors' : 'completed',
            'completed_at' => now(),
            ...$counts,
        ]);
        DapodikConnection::withoutGlobalScopes()->where('school_id', $run->school_id)->update(['last_sync_at' => now(), 'status' => 'synced']);

        return $run->fresh('items');
    }

    public function importStudentsFromCsv(int $schoolId, int $triggerUserId, string $csvContent): DapodikSyncLog
    {
        $rows = $this->parseCsv($csvContent);
        $records = array_map(function (array $row): array {
            return [
                'external_id' => $row['dapodik_id'] ?? $row['nisn'] ?? null,
                'admission_no' => $row['admission_no'] ?? $row['nisn'] ?? null,
                'name' => $row['name'] ?? null,
                'email' => $row['email'] ?? null,
                'date_of_birth' => $row['date_of_birth'] ?? null,
                'gender' => $row['gender'] ?? null,
                'address' => $row['address'] ?? null,
            ];
        }, $rows);

        $run = $this->createPreview($schoolId, $triggerUserId, 'students', $records);
        $this->syncPreviewedRun($run);
        $run = $run->fresh();

        return DapodikSyncLog::withoutGlobalScopes()->create([
            'school_id' => $schoolId,
            'direction' => 'import',
            'entity' => 'students',
            'records_total' => $run->total,
            'records_success' => $run->inserted + $run->updated + $run->unchanged,
            'records_failed' => $run->failed + $run->conflicted,
            'errors' => $run->items()->where('status', 'failed')->pluck('error_message')->values()->all(),
            'status' => $run->status === 'completed' ? 'completed' : 'failed',
            'triggered_by' => $triggerUserId,
        ]);
    }

    public function exportStudentsToCsv(int $schoolId): string
    {
        $students = Student::withoutGlobalScopes()->where('school_id', $schoolId)->with('user:id,name')->get();
        $rows = [['dapodik_id', 'admission_no', 'name', 'date_of_birth', 'gender', 'address']];
        foreach ($students as $student) {
            $rows[] = [$student->dapodik_id, $student->admission_no, $student->user?->name ?? '', $student->date_of_birth?->format('Y-m-d'), $student->gender, $student->address];
        }

        return collect($rows)->map(fn ($row) => implode(',', array_map(fn ($value) => '"'.str_replace('"', '""', (string) $value).'"', $row)))->implode("\n")."\n";
    }

    private function syncItem(int $schoolId, DapodikSyncItem $item): array
    {
        if ($item->entity_type !== 'students') {
            throw new \RuntimeException('Entity sync belum tersedia untuk '.$item->entity_type.'.');
        }

        return DB::transaction(function () use ($schoolId, $item) {
            $data = $item->normalized_payload ?? [];
            $student = Student::withoutGlobalScopes()->where('school_id', $schoolId)->where('dapodik_id', $item->external_id)->first();
            $wasExisting = (bool) $student;

            if (! $student) {
                $mapped = DapodikEntityMapping::withoutGlobalScopes()->where([
                    'school_id' => $schoolId,
                    'entity_type' => 'students',
                    'external_id' => $item->external_id,
                ])->first();
                $student = $mapped ? Student::withoutGlobalScopes()->where('school_id', $schoolId)->find($mapped->local_id) : null;
            }

            if (! $student) {
                $email = $data['email'] ?? 'dapodik-'.Str::lower($item->external_id).'-'.$schoolId.'@invalid.local';
                if (User::where('email', $email)->exists()) {
                    $email = 'dapodik-'.Str::lower($item->external_id).'-'.$schoolId.'-'.Str::lower(Str::random(6)).'@invalid.local';
                }
                $user = User::withoutEvents(function () use ($schoolId, $data, $email) {
                    return User::create([
                        'school_id' => $schoolId,
                        'name' => $data['name'] ?? 'Siswa Dapodik',
                        'email' => $email,
                        'password' => Hash::make(Str::random(40)),
                        'is_active' => false,
                    ]);
                });
                try {
                    $user->assignRole('student');
                } catch (\Throwable) {
                }
                $student = Student::withoutGlobalScopes()->create([
                    'school_id' => $schoolId,
                    'user_id' => $user->id,
                    'dapodik_id' => $item->external_id,
                    'admission_no' => $data['admission_no'] ?? $item->external_id,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'address' => $data['address'] ?? null,
                ]);
            } else {
                $student->update(array_filter([
                    'dapodik_id' => $item->external_id,
                    'admission_no' => $data['admission_no'] ?? null,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'address' => $data['address'] ?? null,
                ], fn ($value) => $value !== null));
                if (! empty($data['name']) && $student->user && $student->user->name !== $data['name']) {
                    $student->user->update(['name' => $data['name']]);
                }
            }

            DapodikEntityMapping::withoutGlobalScopes()->updateOrCreate(
                ['school_id' => $schoolId, 'entity_type' => 'students', 'external_id' => $item->external_id],
                ['local_type' => Student::class, 'local_id' => $student->id, 'last_synced_at' => now()],
            );

            return [
                'status' => $wasExisting ? 'updated' : 'inserted',
                'local_type' => Student::class,
                'local_id' => $student->id,
                'snapshot' => $student->fresh()->toArray(),
            ];
        });
    }

    private function normalize(int $schoolId, string $entityType, array $record): array
    {
        $connection = $this->getOrCreateConnection($schoolId);
        $mapping = (array) data_get($connection->field_mappings, $entityType, []);
        $normalized = ['external_id' => $record['external_id'] ?? $record['dapodik_id'] ?? null];
        foreach (['name', 'email', 'admission_no', 'date_of_birth', 'gender', 'address'] as $field) {
            $source = $mapping[$field] ?? $field;
            $normalized[$field] = $record[$source] ?? null;
        }

        return $normalized;
    }

    private function previewStatus(int $schoolId, string $entityType, string $externalId, array $normalized): string
    {
        $localId = $this->mappedLocalId($schoolId, $entityType, $externalId);
        if (! $localId) {
            return 'new';
        }
        $local = $entityType === 'students'
            ? Student::withoutGlobalScopes()->where('school_id', $schoolId)->find($localId)
            : null;
        if (! $local) {
            return 'new';
        }

        return ($normalized['name'] && $local->user?->name && $normalized['name'] !== $local->user->name) ? 'changed' : 'matched';
    }

    private function mappedLocalId(int $schoolId, string $entityType, string $externalId): ?int
    {
        if ($externalId === '') {
            return null;
        }

        return DapodikEntityMapping::withoutGlobalScopes()->where([
            'school_id' => $schoolId,
            'entity_type' => $entityType,
            'external_id' => $externalId,
        ])->value('local_id');
    }

    private function localType(string $entityType): ?string
    {
        return ['students' => Student::class][$entityType] ?? null;
    }

    protected function parseCsv(string $content): array
    {
        $lines = explode("\n", trim($content));
        if (count($lines) < 2) {
            return [];
        }
        $headers = str_getcsv(array_shift($lines));
        $rows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $values = str_getcsv($line);
            $rows[] = array_combine($headers, array_pad($values, count($headers), null));
        }

        return $rows;
    }
}

<?php

namespace App\Services\Import;

use App\Models\Academic\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CsvImportPreviewService
{
    private const TTL_MINUTES = 30;

    /**
     * Parse and validate an import without writing domain records.
     * Passwords stay in the encrypted cache payload and are never returned to the view.
     */
    public function preview(UploadedFile $file, int $schoolId, string $kind): array
    {
        $required = $kind === 'students'
            ? ['admission_no', 'name', 'email', 'gender', 'password']
            : ['name', 'email', 'role', 'password'];

        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            throw new InvalidArgumentException('File CSV tidak dapat dibaca.');
        }

        try {
            $rawHeaders = fgetcsv($handle);
            if (! is_array($rawHeaders)) {
                throw new InvalidArgumentException('CSV tidak memiliki header.');
            }

            $headers = array_map(function ($header) {
                return trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $header));
            }, $rawHeaders);

            $missing = array_values(array_diff($required, $headers));
            if ($missing !== []) {
                throw new InvalidArgumentException('CSV harus punya header: '.implode(', ', $required));
            }

            $rows = [];
            $seenEmails = [];
            $seenAdmissionNumbers = [];
            $line = 1;

            while (($rawRow = fgetcsv($handle)) !== false) {
                $line++;
                if ($line > 5001) {
                    throw new InvalidArgumentException('Maksimal 5.000 baris per import. Pecah file menjadi beberapa batch.');
                }

                $values = array_pad($rawRow, count($headers), null);
                $data = array_combine($headers, array_slice($values, 0, count($headers)));
                if (! is_array($data)) {
                    $rows[] = ['line' => $line, 'data' => [], 'errors' => ['Jumlah kolom tidak sesuai header.'], 'valid' => false];

                    continue;
                }

                $errors = [];
                $email = strtolower(trim((string) ($data['email'] ?? '')));
                $name = trim((string) ($data['name'] ?? ''));

                if ($name === '') {
                    $errors[] = 'Nama wajib diisi.';
                }
                if (mb_strlen((string) ($data['password'] ?? '')) < 8) {
                    $errors[] = 'Password wajib diisi minimal 8 karakter.';
                }
                if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Email wajib berupa alamat email yang valid.';
                } elseif (isset($seenEmails[$email]) || User::withoutGlobalScopes()->where('email', $email)->exists()) {
                    $errors[] = 'Email sudah digunakan atau duplikat di file.';
                }

                if ($kind === 'students') {
                    $admissionNo = trim((string) ($data['admission_no'] ?? ''));
                    if ($admissionNo === '') {
                        $errors[] = 'Nomor pendaftaran wajib diisi.';
                    } elseif (
                        isset($seenAdmissionNumbers[$admissionNo])
                        || Student::withoutGlobalScopes()->where('school_id', $schoolId)->where('admission_no', $admissionNo)->exists()
                    ) {
                        $errors[] = 'Nomor pendaftaran sudah digunakan atau duplikat di file.';
                    }
                } else {
                    $allowedRoles = ['teacher', 'admin', 'accountant', 'librarian', 'counselor', 'nurse', 'receptionist'];
                    if (! in_array((string) ($data['role'] ?? ''), $allowedRoles, true)) {
                        $errors[] = 'Role staff tidak valid.';
                    }
                }

                if ($email !== '') {
                    $seenEmails[$email] = true;
                }
                if ($kind === 'students' && ($admissionNo ?? '') !== '') {
                    $seenAdmissionNumbers[$admissionNo] = true;
                }

                $rows[] = [
                    'line' => $line,
                    'data' => $data,
                    'errors' => $errors,
                    'valid' => $errors === [],
                ];
            }

            $valid = count(array_filter($rows, fn (array $row) => $row['valid']));

            return [
                'kind' => $kind,
                'school_id' => $schoolId,
                'headers' => $headers,
                'rows' => $rows,
                'valid_count' => $valid,
                'invalid_count' => count($rows) - $valid,
            ];
        } finally {
            fclose($handle);
        }
    }

    public function store(array $payload, int $userId): string
    {
        $token = Str::random(64);
        $payload['user_id'] = $userId;
        $payload['created_at'] = now()->toIso8601String();
        $encrypted = Crypt::encryptString(json_encode($payload, JSON_THROW_ON_ERROR));

        Cache::put($this->key($token), $encrypted, now()->addMinutes(self::TTL_MINUTES));

        return $token;
    }

    /** Return a browser-safe copy without credentials from CSV rows. */
    public function forDisplay(array $payload): array
    {
        foreach ($payload['rows'] as &$row) {
            unset($row['data']['password']);
        }

        return $payload;
    }

    public function retrieve(string $token, int $schoolId, int $userId): ?array
    {
        $encrypted = Cache::get($this->key($token));
        if (! is_string($encrypted)) {
            return null;
        }

        try {
            $payload = json_decode(Crypt::decryptString($encrypted), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }

        if ((int) ($payload['school_id'] ?? 0) !== $schoolId || (int) ($payload['user_id'] ?? 0) !== $userId) {
            return null;
        }

        return $payload;
    }

    public function forget(string $token): void
    {
        Cache::forget($this->key($token));
    }

    private function key(string $token): string
    {
        return 'import-preview:'.$token;
    }
}

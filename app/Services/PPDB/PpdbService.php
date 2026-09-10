<?php

namespace App\Services\PPDB;

use App\Mail\PpdbAcceptanceMail;
use App\Mail\PpdbRejectionMail;
use App\Mail\PpdbSubmissionMail;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Student;
use App\Models\PPDB\PpdbApplication;
use App\Models\PPDB\PpdbPeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PpdbService
{
    public function register(PpdbPeriod $period, array $data): PpdbApplication
    {
        return DB::transaction(function () use ($period, $data) {
            $period = PpdbPeriod::withoutGlobalScopes()->lockForUpdate()->findOrFail($period->id);
            abort_unless($period->isOpen(), 422, 'Periode PPDB tidak sedang dibuka.');

            $jalur = $data['jalur'] ?? 'reguler';
            abort_unless(in_array($jalur, PpdbApplication::JALUR, true), 422, 'Jalur PPDB tidak valid.');
            abort_unless($this->jalurIsConfigured($period, $jalur), 422, 'Jalur PPDB belum dibuka pada periode ini.');

            $this->ensureApplicantIsUnique($period, $data);
            $registrationNo = $this->generateRegistrationNo($period);

            $distance = null;
            if (isset($data['home_lat'], $data['home_lng'], $data['school_lat'], $data['school_lng'])) {
                $distance = $this->haversineKm(
                    (float) $data['home_lat'], (float) $data['home_lng'],
                    (float) $data['school_lat'], (float) $data['school_lng'],
                );
            }

            return PpdbApplication::create([
                'school_id' => $period->school_id,
                'ppdb_period_id' => $period->id,
                'registration_no' => $registrationNo,
                'jalur' => $jalur,
                'student_name' => $data['student_name'],
                'nisn' => $data['nisn'] ?? null,
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender'],
                'address' => $data['address'],
                'district' => $data['district'],
                'city' => $data['city'],
                'home_lat' => $data['home_lat'] ?? null,
                'home_lng' => $data['home_lng'] ?? null,
                'distance_km' => $distance,
                'previous_school' => $data['previous_school'] ?? null,
                'parent_name' => $data['parent_name'],
                'parent_phone' => $data['parent_phone'],
                'parent_email' => $data['parent_email'],
                'documents' => $data['documents'] ?? [],
                'achievements' => $data['achievements'] ?? [],
                'average_score' => $data['average_score'] ?? null,
                'status' => 'draft',
            ]);
        });
    }

    public function submit(PpdbApplication $app): PpdbApplication
    {
        $fresh = DB::transaction(function () use ($app) {
            $locked = $this->lockApplication($app);
            abort_unless($locked->status === 'draft', 422, 'Hanya pendaftaran draft yang dapat dikirim.');
            abort_unless($locked->period?->isOpen(), 422, 'Periode PPDB sudah ditutup.');

            $locked->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            return $locked->fresh();
        });

        $this->dispatchEmail($fresh, 'submission');

        return $fresh;
    }

    public function verify(PpdbApplication $app, int $reviewerId): PpdbApplication
    {
        return DB::transaction(function () use ($app, $reviewerId) {
            $locked = $this->lockApplication($app);
            $this->reviewerForSchool($locked->school_id, $reviewerId);
            abort_unless($locked->status === 'submitted', 422, 'Pendaftaran harus berstatus diajukan sebelum diverifikasi.');

            $locked->update([
                'status' => 'verified',
                'reviewer_id' => $reviewerId,
                'verified_at' => now(),
            ]);

            return $locked->fresh();
        });
    }

    public function accept(PpdbApplication $app, int $reviewerId, ?string $note = null): PpdbApplication
    {
        $fresh = DB::transaction(function () use ($app, $reviewerId, $note) {
            $locked = $this->lockApplication($app);
            $this->reviewerForSchool($locked->school_id, $reviewerId);
            abort_unless(in_array($locked->status, ['verified', 'waitlist'], true), 422, 'Hanya pendaftar terverifikasi yang dapat diterima.');
            $this->ensureQuotaAvailable($locked, true);

            $locked->update([
                'status' => 'accepted',
                'reviewer_id' => $reviewerId,
                'reviewer_note' => $note,
                'accepted_at' => now(),
                'waiting_list_position' => null,
            ]);

            return $locked->fresh();
        });

        $this->dispatchEmail($fresh, 'acceptance');

        return $fresh;
    }

    public function reject(PpdbApplication $app, int $reviewerId, string $note): PpdbApplication
    {
        $fresh = DB::transaction(function () use ($app, $reviewerId, $note) {
            $locked = $this->lockApplication($app);
            $this->reviewerForSchool($locked->school_id, $reviewerId);
            abort_unless(in_array($locked->status, ['submitted', 'verified', 'waitlist'], true), 422, 'Status pendaftaran tidak dapat ditolak pada tahap ini.');

            $locked->update([
                'status' => 'rejected',
                'reviewer_id' => $reviewerId,
                'reviewer_note' => $note,
                'waiting_list_position' => null,
            ]);

            return $locked->fresh();
        });

        $this->dispatchEmail($fresh, 'rejection');

        return $fresh;
    }

    public function runSelection(PpdbPeriod $period): array
    {
        return DB::transaction(function () use ($period) {
            $period = PpdbPeriod::withoutGlobalScopes()->lockForUpdate()->findOrFail($period->id);
            $jalurConfig = (array) ($period->jalur_config ?? []);
            $accepted = 0;
            $waitlisted = 0;

            PpdbApplication::withoutGlobalScopes()
                ->where('school_id', $period->school_id)
                ->where('ppdb_period_id', $period->id)
                ->where('status', 'waitlist')
                ->update(['waiting_list_position' => null]);

            foreach ($jalurConfig as $jalur => $config) {
                $quota = $this->quotaFromConfig($config);
                $apps = PpdbApplication::withoutGlobalScopes()
                    ->where('school_id', $period->school_id)
                    ->where('ppdb_period_id', $period->id)
                    ->whereIn('status', ['verified', 'waitlist'])
                    ->where('jalur', $jalur)
                    ->lockForUpdate()
                    ->get();

                $apps = $this->scoreApplications($apps, $jalur, is_array($config) ? ($config['weights'] ?? []) : []);
                $apps = $apps->sortByDesc('ranking_score')->values();
                $position = 1;

                foreach ($apps as $app) {
                    $updates = ['ranking_score' => $app->ranking_score, 'rank_position' => $position];
                    if ($position <= $quota) {
                        $updates += ['status' => 'accepted', 'accepted_at' => $app->accepted_at ?? now(), 'waiting_list_position' => null];
                        $accepted++;
                    } else {
                        $updates += ['status' => 'waitlist', 'waiting_list_position' => $this->nextWaitingListPosition($period)];
                        $waitlisted++;
                    }
                    $app->update($updates);
                    if ($position <= $quota) {
                        $this->dispatchEmail($app->fresh(), 'acceptance');
                    }
                    $position++;
                }
            }

            return ['accepted_total' => $accepted, 'waitlist_total' => $waitlisted];
        });
    }

    public function uploadDocument(PpdbApplication $app, string $docType, $file): PpdbApplication
    {
        $path = $file->store("ppdb/{$app->school_id}/{$app->id}", 'public');

        $documents = (array) $app->documents;
        $documents[] = [
            'type' => $docType,
            'path' => $path,
            'original' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'uploaded_at' => now()->toIso8601String(),
        ];

        $app->update(['documents' => $documents]);

        return $app->fresh();
    }

    public function batchEnroll(array $applicationIds, int $classSectionId, int $enrollerId, ?int $schoolId = null): array
    {
        $schoolId ??= auth()->user()?->school_id;
        $classSection = ClassSection::withoutGlobalScopes()
            ->whereKey($classSectionId)
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->firstOrFail();

        $enrolled = 0;
        $failed = [];

        foreach ($applicationIds as $appId) {
            $app = PpdbApplication::withoutGlobalScopes()
                ->whereKey($appId)
                ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
                ->first();
            if (! $app || $app->status !== 'accepted' || $app->enrolled_student_id) {
                $failed[] = $appId;

                continue;
            }

            try {
                $this->enrollStudent($app, $classSection->id, null, $enrollerId);
                $enrolled++;
            } catch (\Throwable) {
                $failed[] = $appId;
            }
        }

        return ['enrolled' => $enrolled, 'failed' => $failed];
    }

    public function getReports(int $schoolId, ?int $periodId = null): array
    {
        $query = PpdbApplication::where('school_id', $schoolId);

        if ($periodId) {
            $query->where('ppdb_period_id', $periodId);
        }

        $all = $query->get();

        $byStatus = [];
        foreach (PpdbApplication::STATUSES as $status) {
            $byStatus[$status] = $all->where('status', $status)->count();
        }

        $byJalur = [];
        foreach (PpdbApplication::JALUR as $jalur) {
            $jalurApps = $all->where('jalur', $jalur);
            $byJalur[$jalur] = [
                'total' => $jalurApps->count(),
                'draft' => $jalurApps->where('status', 'draft')->count(),
                'submitted' => $jalurApps->where('status', 'submitted')->count(),
                'verified' => $jalurApps->where('status', 'verified')->count(),
                'accepted' => $jalurApps->where('status', 'accepted')->count(),
                'waitlist' => $jalurApps->where('status', 'waitlist')->count(),
                'rejected' => $jalurApps->where('status', 'rejected')->count(),
                'enrolled' => $jalurApps->where('status', 'enrolled')->count(),
            ];
        }

        $total = $all->count();
        $conversionRates = [
            'draft_to_submitted' => $total > 0 ? round($byStatus['submitted'] / $total * 100, 1) : 0,
            'submitted_to_verified' => $byStatus['submitted'] > 0 ? round($byStatus['verified'] / max($byStatus['submitted'], 1) * 100, 1) : 0,
            'verified_to_accepted' => $byStatus['verified'] > 0 ? round($byStatus['accepted'] / max($byStatus['verified'], 1) * 100, 1) : 0,
            'accepted_to_enrolled' => $byStatus['accepted'] > 0 ? round($byStatus['enrolled'] / max($byStatus['accepted'], 1) * 100, 1) : 0,
            'overall_enrollment' => $total > 0 ? round($byStatus['enrolled'] / $total * 100, 1) : 0,
        ];

        return [
            'total' => $total,
            'by_status' => $byStatus,
            'by_jalur' => $byJalur,
            'conversion_rates' => $conversionRates,
        ];
    }

    protected function dispatchEmail(PpdbApplication $app, string $type): void
    {
        if (empty($app->parent_email)) {
            return;
        }

        $mailable = match ($type) {
            'submission' => new PpdbSubmissionMail($app),
            'acceptance' => new PpdbAcceptanceMail($app),
            'rejection' => new PpdbRejectionMail($app),
            default => null,
        };

        if ($mailable) {
            Mail::to($app->parent_email)->queue($mailable);
        }
    }

    protected function scoreApplications($apps, string $jalur, array $weights = [])
    {
        return $apps->map(function (PpdbApplication $app) use ($jalur, $weights) {
            if ($weights !== []) {
                $score = (float) ($app->average_score ?? 0) * (float) ($weights['average_score'] ?? 1);
                $score += (float) ($app->entrance_test_score ?? 0) * (float) ($weights['entrance_test_score'] ?? 0);
                $score += (float) ($app->interview_score ?? 0) * (float) ($weights['interview_score'] ?? 0);
                $score += count((array) $app->achievements) * (float) ($weights['achievement'] ?? 0);
                $score -= (float) ($app->distance_km ?? 0) * (float) ($weights['distance_penalty'] ?? 0);
                $app->ranking_score = round($score, 3);

                return $app;
            }

            $score = match ($jalur) {
                'zonasi' => $app->distance_km !== null ? max(0, 100 - (float) $app->distance_km * 10) : 0,
                'prestasi' => (float) ($app->average_score ?? 0) + count((array) $app->achievements) * 5,
                'afirmasi' => 50 + ((float) $app->average_score ?? 0) * 0.5,
                'undian' => mt_rand(0, 1000) / 10,
                default => (float) ($app->average_score ?? 0),
            };

            $score += (float) ($app->entrance_test_score ?? 0) * 0.3;
            $score += (float) ($app->interview_score ?? 0) * 0.2;

            $app->ranking_score = round($score, 3);

            return $app;
        });
    }

    /** Convert an accepted applicant into an actual Student (+ login user). */
    public function enrollStudent(PpdbApplication $app, int $classSectionId, ?string $admissionNo = null, ?int $enrollerId = null): Student
    {
        abort_unless(ClassSection::withoutGlobalScopes()
            ->whereKey($classSectionId)
            ->where('school_id', $app->school_id)
            ->exists(), 422, 'Rombel tujuan tidak berasal dari sekolah pendaftar.');

        return DB::transaction(function () use ($app, $classSectionId, $admissionNo, $enrollerId) {
            $app = $this->lockApplication($app);
            abort_unless($app->status === 'accepted', 422, 'Hanya pendaftar yang sudah diterima yang bisa didaftarkan.');
            abort_if($app->enrolled_student_id, 422, 'Pendaftar ini sudah menjadi siswa.');
            $email = strtolower(Str::slug($app->student_name, '.').'.'.$app->id.'@'.'student.sikadpro.app');

            $user = User::create([
                'name' => $app->student_name,
                'email' => $email,
                'password' => Hash::make(Str::random(16)),
                'school_id' => $app->school_id,
                'is_active' => true,
            ]);
            $user->assignRole('student');

            $student = Student::create([
                'user_id' => $user->id,
                'school_id' => $app->school_id,
                'class_section_id' => $classSectionId,
                'admission_no' => $admissionNo ?? $app->registration_no,
                'admission_date' => now()->toDateString(),
                'enrolled_at' => now()->toDateString(),
                'date_of_birth' => $app->date_of_birth,
                'gender' => $app->gender,
                'address' => $app->address,
                'guardian_name' => $app->parent_name,
                'guardian_phone' => $app->parent_phone,
                'status' => 'enrolled',
            ]);

            $app->update([
                'status' => 'enrolled',
                'enrolled_student_id' => $student->id,
                'reviewer_id' => $enrollerId ?? $app->reviewer_id,
            ]);

            return $student;
        });
    }

    /* ==================== WAITING LIST ==================== */

    public function addToWaitingList(PpdbApplication $application): PpdbApplication
    {
        return DB::transaction(function () use ($application) {
            $locked = $this->lockApplication($application);
            abort_unless(in_array($locked->status, ['verified', 'waitlist'], true), 422, 'Hanya pendaftar terverifikasi yang dapat masuk daftar tunggu.');
            $period = PpdbPeriod::withoutGlobalScopes()->lockForUpdate()->findOrFail($locked->ppdb_period_id);
            $locked->update([
                'status' => 'waitlist',
                'waiting_list_position' => $locked->waiting_list_position ?? $this->nextWaitingListPosition($period),
            ]);

            return $locked->fresh();
        });
    }

    public function promoteFromWaitingList(int $periodId, int $schoolId): ?PpdbApplication
    {
        return DB::transaction(function () use ($periodId, $schoolId) {
            $period = PpdbPeriod::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->lockForUpdate()
                ->findOrFail($periodId);
            $next = PpdbApplication::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->where('ppdb_period_id', $period->id)
                ->where('status', 'waitlist')
                ->whereNotNull('waiting_list_position')
                ->orderBy('waiting_list_position')
                ->lockForUpdate()
                ->first();

            if (! $next) {
                return null;
            }

            $this->ensureQuotaAvailable($next, true);
            $next->update([
                'status' => 'accepted',
                'accepted_at' => now(),
                'waiting_list_position' => null,
            ]);

            $fresh = $next->fresh();
            $this->dispatchEmail($fresh, 'acceptance');

            return $fresh;
        });
    }

    public function getWaitingList(int $periodId, int $schoolId): Collection
    {
        return PpdbApplication::where('school_id', $schoolId)
            ->where('ppdb_period_id', $periodId)
            ->where('status', 'waitlist')
            ->whereNotNull('waiting_list_position')
            ->orderBy('waiting_list_position')
            ->get();
    }

    protected function lockApplication(PpdbApplication $application): PpdbApplication
    {
        $locked = PpdbApplication::withoutGlobalScopes()
            ->whereKey($application->id)
            ->lockForUpdate()
            ->firstOrFail();

        abort_unless($locked->school_id === $application->school_id, 403, 'Pendaftar bukan bagian dari sekolah ini.');

        $period = PpdbPeriod::withoutGlobalScopes()->findOrFail($locked->ppdb_period_id);
        abort_unless($period->school_id === $locked->school_id, 422, 'Periode PPDB tidak cocok dengan sekolah pendaftar.');
        $locked->setRelation('period', $period);

        return $locked;
    }

    protected function reviewerForSchool(int $schoolId, int $reviewerId): User
    {
        return User::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->findOrFail($reviewerId);
    }

    protected function ensureApplicantIsUnique(PpdbPeriod $period, array $data): void
    {
        $base = PpdbApplication::withoutGlobalScopes()
            ->where('school_id', $period->school_id)
            ->where('ppdb_period_id', $period->id)
            ->whereNotIn('status', ['rejected', 'withdrew']);

        if (! empty($data['nisn'])) {
            abort_if((clone $base)->where('nisn', trim((string) $data['nisn']))->exists(), 422, 'NISN sudah terdaftar pada periode PPDB ini.');

            return;
        }

        $duplicate = (clone $base)
            ->where('parent_phone', trim((string) ($data['parent_phone'] ?? '')))
            ->whereDate('date_of_birth', $data['date_of_birth'])
            ->whereRaw('LOWER(student_name) = ?', [mb_strtolower(trim((string) $data['student_name']))])
            ->exists();

        abort_if($duplicate, 422, 'Pendaftaran dengan data siswa, tanggal lahir, dan nomor wali yang sama sudah ada.');
    }

    protected function jalurIsConfigured(PpdbPeriod $period, string $jalur): bool
    {
        $config = (array) ($period->jalur_config ?? []);

        return $config === [] || array_key_exists($jalur, $config);
    }

    protected function quotaFromConfig(mixed $config): int
    {
        if (is_array($config)) {
            return max(0, (int) ($config['quota'] ?? 0));
        }

        return max(0, (int) $config);
    }

    protected function ensureQuotaAvailable(PpdbApplication $application, bool $allowWaitlist): void
    {
        $period = $application->relationLoaded('period')
            ? $application->period
            : PpdbPeriod::withoutGlobalScopes()->findOrFail($application->ppdb_period_id);
        $config = (array) ($period->jalur_config ?? []);

        if ($config === [] || ! array_key_exists($application->jalur, $config)) {
            return;
        }

        $quota = $this->quotaFromConfig($config[$application->jalur]);
        $accepted = PpdbApplication::withoutGlobalScopes()
            ->where('school_id', $application->school_id)
            ->where('ppdb_period_id', $application->ppdb_period_id)
            ->where('jalur', $application->jalur)
            ->whereIn('status', ['accepted', 'enrolled'])
            ->where($application->getKeyName(), '<>', $application->id)
            ->count();

        abort_unless($allowWaitlist && $accepted < $quota, 422, 'Kuota jalur PPDB sudah penuh.');
    }

    protected function nextWaitingListPosition(PpdbPeriod $period): int
    {
        return ((int) PpdbApplication::withoutGlobalScopes()
            ->where('school_id', $period->school_id)
            ->where('ppdb_period_id', $period->id)
            ->max('waiting_list_position')) + 1;
    }

    protected function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c, 3);
    }

    protected function generateRegistrationNo(PpdbPeriod $period): string
    {
        $year = $period->open_date->format('Y');

        return sprintf('PPDB-%s-%s-%s',
            $year,
            $period->school_id,
            strtoupper(Str::random(6)),
        );
    }
}

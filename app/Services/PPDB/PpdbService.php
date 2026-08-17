<?php

namespace App\Services\PPDB;

use App\Models\PPDB\PpdbApplication;
use App\Models\PPDB\PpdbPeriod;
use App\Models\PPDB\PpdbZonasiZone;
use App\Models\Academic\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PpdbService
{
    public function register(PpdbPeriod $period, array $data): PpdbApplication
    {
        return DB::transaction(function () use ($period, $data) {
            $registrationNo = $this->generateRegistrationNo($period);

            $distance = null;
            if (isset($data['home_lat'], $data['home_lng'], $data['school_lat'], $data['school_lng'])) {
                $distance = $this->haversineKm(
                    (float) $data['home_lat'], (float) $data['home_lng'],
                    (float) $data['school_lat'], (float) $data['school_lng'],
                );
            }

            return PpdbApplication::create([
                'school_id'        => $period->school_id,
                'ppdb_period_id'   => $period->id,
                'registration_no'  => $registrationNo,
                'jalur'            => $data['jalur'] ?? 'reguler',
                'student_name'     => $data['student_name'],
                'nisn'             => $data['nisn'] ?? null,
                'date_of_birth'    => $data['date_of_birth'],
                'gender'           => $data['gender'],
                'address'          => $data['address'],
                'district'         => $data['district'],
                'city'             => $data['city'],
                'home_lat'         => $data['home_lat'] ?? null,
                'home_lng'         => $data['home_lng'] ?? null,
                'distance_km'      => $distance,
                'previous_school'  => $data['previous_school'] ?? null,
                'parent_name'      => $data['parent_name'],
                'parent_phone'     => $data['parent_phone'],
                'parent_email'     => $data['parent_email'],
                'documents'        => $data['documents'] ?? [],
                'achievements'     => $data['achievements'] ?? [],
                'average_score'    => $data['average_score'] ?? null,
                'status'           => 'draft',
            ]);
        });
    }

    public function submit(PpdbApplication $app): PpdbApplication
    {
        $app->update([
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);
        return $app->fresh();
    }

    public function verify(PpdbApplication $app, int $reviewerId): PpdbApplication
    {
        $app->update([
            'status'      => 'verified',
            'reviewer_id' => $reviewerId,
            'verified_at' => now(),
        ]);
        return $app->fresh();
    }

    public function accept(PpdbApplication $app, int $reviewerId, ?string $note = null): PpdbApplication
    {
        $app->update([
            'status'         => 'accepted',
            'reviewer_id'    => $reviewerId,
            'reviewer_note'  => $note,
            'accepted_at'    => now(),
        ]);
        return $app->fresh();
    }

    public function reject(PpdbApplication $app, int $reviewerId, string $note): PpdbApplication
    {
        $app->update([
            'status'        => 'rejected',
            'reviewer_id'   => $reviewerId,
            'reviewer_note' => $note,
        ]);
        return $app->fresh();
    }

    public function runSelection(PpdbPeriod $period): array
    {
        $jalurConfig = (array) ($period->jalur_config ?? []);
        $accepted    = 0;

        foreach ($jalurConfig as $jalur => $quota) {
            $apps = PpdbApplication::where('school_id', $period->school_id)
                ->where('ppdb_period_id', $period->id)
                ->where('status', 'verified')
                ->where('jalur', $jalur)
                ->get();

            $apps = $this->scoreApplications($apps, $jalur);
            $apps = $apps->sortByDesc('ranking_score')->values();

            $rank = 1;
            foreach ($apps as $app) {
                $app->update(['ranking_score' => $app->ranking_score, 'rank_position' => $rank]);
                if ($rank <= (int) $quota) {
                    $app->update([
                        'status'      => 'accepted',
                        'accepted_at' => now(),
                    ]);
                    $accepted++;
                }
                $rank++;
            }
        }

        return ['accepted_total' => $accepted];
    }

    protected function scoreApplications($apps, string $jalur)
    {
        return $apps->map(function (PpdbApplication $app) use ($jalur) {
            $score = match ($jalur) {
                'zonasi'    => $app->distance_km !== null ? max(0, 100 - (float) $app->distance_km * 10) : 0,
                'prestasi'  => (float) ($app->average_score ?? 0) + count((array) $app->achievements) * 5,
                'afirmasi'  => 50 + ((float) $app->average_score ?? 0) * 0.5,
                'undian'    => mt_rand(0, 1000) / 10,
                default     => (float) ($app->average_score ?? 0),
            };

            // Blend in entrance test + interview scores when present (weighted).
            $score += (float) ($app->entrance_test_score ?? 0) * 0.3;
            $score += (float) ($app->interview_score ?? 0) * 0.2;

            $app->ranking_score = round($score, 3);
            return $app;
        });
    }

    /** Convert an accepted applicant into an actual Student (+ login user). */
    public function enrollStudent(PpdbApplication $app, int $classSectionId, ?string $admissionNo = null, int $enrollerId = null): Student
    {
        abort_unless($app->status === 'accepted', 422, 'Hanya pendaftar yang sudah diterima yang bisa didaftarkan.');
        abort_if($app->enrolled_student_id, 422, 'Pendaftar ini sudah menjadi siswa.');

        return DB::transaction(function () use ($app, $classSectionId, $admissionNo, $enrollerId) {
            $email = strtolower(Str::slug($app->student_name, '.') . '.' . $app->id . '@' . 'student.sikadpro.app');

            $user = User::create([
                'name'      => $app->student_name,
                'email'     => $email,
                'password'  => Hash::make(Str::random(16)),
                'school_id' => $app->school_id,
                'is_active' => true,
            ]);
            $user->assignRole('student');

            $student = Student::create([
                'user_id'         => $user->id,
                'school_id'       => $app->school_id,
                'class_section_id'=> $classSectionId,
                'admission_no'    => $admissionNo ?? $app->registration_no,
                'admission_date'  => now()->toDateString(),
                'enrolled_at'     => now()->toDateString(),
                'date_of_birth'   => $app->date_of_birth,
                'gender'          => $app->gender,
                'address'         => $app->address,
                'guardian_name'   => $app->parent_name,
                'guardian_phone'  => $app->parent_phone,
                'status'          => 'enrolled',
            ]);

            $app->update([
                'status'              => 'enrolled',
                'enrolled_student_id' => $student->id,
                'reviewer_id'         => $enrollerId ?? $app->reviewer_id,
            ]);

            return $student;
        });
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

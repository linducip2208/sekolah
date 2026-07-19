<?php

namespace App\Services\Religious;

use App\Models\Religious\HafalanProgress;
use App\Models\Religious\IbadahLog;
use App\Models\Religious\KitabKuningProgress;
use App\Models\Religious\ReligiousModeConfig;

class ReligiousService
{
    public function getOrCreateConfig(int $schoolId): ReligiousModeConfig
    {
        return ReligiousModeConfig::firstOrCreate(
            ['school_id' => $schoolId],
            ['enabled' => false, 'religion' => 'islam'],
        );
    }

    public function updateConfig(int $schoolId, array $data): ReligiousModeConfig
    {
        $config = $this->getOrCreateConfig($schoolId);
        $config->update($data);
        return $config->fresh();
    }

    public function recordHafalan(int $schoolId, int $studentId, int $verifiedBy, array $data): HafalanProgress
    {
        return HafalanProgress::create([
            'school_id'         => $schoolId,
            'student_id'        => $studentId,
            'hafalan_target_id' => $data['hafalan_target_id'] ?? null,
            'verified_by'       => $verifiedBy,
            'surah'             => $data['surah'],
            'ayah_start'        => $data['ayah_start'],
            'ayah_end'          => $data['ayah_end'],
            'memorized_at'      => $data['memorized_at'] ?? today(),
            'quality'           => $data['quality'] ?? 'good',
            'note'              => $data['note'] ?? null,
            'audio_path'        => $data['audio_path'] ?? null,
        ]);
    }

    public function logIbadah(int $schoolId, int $studentId, ?int $verifiedBy, array $data): IbadahLog
    {
        return IbadahLog::updateOrCreate(
            ['student_id' => $studentId, 'log_date' => $data['log_date'] ?? today()],
            array_merge($data, [
                'school_id'   => $schoolId,
                'verified_by' => $verifiedBy,
            ]),
        );
    }

    public function studentHafalanSummary(int $schoolId, int $studentId): array
    {
        $progress = HafalanProgress::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->orderBy('surah')->orderBy('ayah_start')
            ->get();

        $totalAyat = $progress->sum(fn (HafalanProgress $p) => $p->ayah_end - $p->ayah_start + 1);

        return [
            'total_records'        => $progress->count(),
            'total_ayat_memorized' => $totalAyat,
            'quality_breakdown'    => $progress->groupBy('quality')->map->count(),
            'recent_progress'      => $progress->take(20)->values(),
        ];
    }

    public function ibadahMonthSummary(int $schoolId, int $studentId, string $yearMonth): array
    {
        [$y, $m] = explode('-', $yearMonth);
        $logs = IbadahLog::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->whereYear('log_date', $y)
            ->whereMonth('log_date', $m)
            ->get();

        $totalDays = (int) date('t', strtotime("$yearMonth-01"));

        $countDone = fn (string $field) => $logs->whereIn($field, ['done', 'jamaah'])->count();

        return [
            'month'       => $yearMonth,
            'total_days'  => $totalDays,
            'logged_days' => $logs->count(),
            'sholat_5_waktu' => [
                'subuh'   => $countDone('subuh'),
                'dzuhur'  => $countDone('dzuhur'),
                'ashar'   => $countDone('ashar'),
                'maghrib' => $countDone('maghrib'),
                'isya'    => $countDone('isya'),
            ],
            'puasa_sunnah_days'  => $logs->where('puasa_sunnah', true)->count(),
            'tilawah_done_days'  => $logs->where('tilawah_done', true)->count(),
            'tilawah_ayah_total' => $logs->sum('tilawah_ayah_count'),
        ];
    }
}

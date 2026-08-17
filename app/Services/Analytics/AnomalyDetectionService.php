<?php

namespace App\Services\Analytics;

use App\Models\Academic\Attendance;
use App\Models\Academic\ClassSection;
use App\Models\Analytics\AnomalyAlert;
use Illuminate\Support\Collection;

class AnomalyDetectionService
{
    /** Detect classes whose attendance rate dropped vs the previous equal-length window. */
    public function detectAttendanceDrops(int $schoolId, int $recentDays = 7, float $thresholdPct = 15): array
    {
        $now = now()->startOfDay();
        $recentStart   = $now->copy()->subDays($recentDays);
        $previousStart = $now->copy()->subDays($recentDays * 2);

        $alerts = [];

        foreach (ClassSection::where('school_id', $schoolId)->with(['classRoom', 'section'])->get() as $cs) {
            $recent   = $this->attendanceRate($schoolId, $cs->id, $recentStart, $now);
            $previous = $this->attendanceRate($schoolId, $cs->id, $previousStart, $recentStart);

            if ($previous['rate'] === null || $recent['rate'] === null) {
                continue;
            }

            $drop = round($previous['rate'] - $recent['rate'], 1);

            if ($drop >= $thresholdPct) {
                $alerts[] = [
                    'type'            => 'attendance_drop',
                    'severity'        => $drop >= 30 ? 'high' : 'medium',
                    'title'           => 'Kehadiran menurun — ' . trim(($cs->classRoom?->name ?? '') . ' ' . ($cs->section?->name ?? '')),
                    'description'     => "Tingkat kehadiran turun dari {$previous['rate']}% menjadi {$recent['rate']}% (penurunan {$drop}%).",
                    'metric_value'    => $recent['rate'],
                    'reference_value' => $previous['rate'],
                    'context'         => ['class_section_id' => $cs->id],
                ];
            }
        }

        return $alerts;
    }

    /** Run all detectors and persist new (unresolved) alerts. Returns count created. */
    public function run(int $schoolId): int
    {
        $alerts = array_merge(
            $this->detectAttendanceDrops($schoolId)
        );

        $count = 0;

        foreach ($alerts as $alert) {
            $contextKey = $alert['context'] ?? [];

            $exists = AnomalyAlert::where('school_id', $schoolId)
                ->where('type', $alert['type'])
                ->whereNull('resolved_at')
                ->when($contextKey, fn ($q) => $q->whereJsonContains('context', $contextKey))
                ->exists();

            if ($exists) {
                continue;
            }

            AnomalyAlert::create(array_merge($alert, [
                'school_id'   => $schoolId,
                'detected_at' => now(),
            ]));

            $count++;
        }

        return $count;
    }

    public function unresolved(int $schoolId): Collection
    {
        return AnomalyAlert::where('school_id', $schoolId)
            ->whereNull('resolved_at')
            ->orderByDesc('severity')
            ->orderByDesc('detected_at')
            ->get();
    }

    public function resolve(AnomalyAlert $alert, int $userId): AnomalyAlert
    {
        $alert->update([
            'resolved_at' => now(),
            'resolved_by' => $userId,
        ]);

        return $alert->fresh();
    }

    private function attendanceRate(int $schoolId, int $classSectionId, $from, $to): array
    {
        $base = fn () => Attendance::where('school_id', $schoolId)
            ->where('class_section_id', $classSectionId)
            ->whereBetween('date', [$from, $to]);

        $total   = $base()->count();
        $present = (clone $base())->where('status', 'present')->count();

        return [
            'total' => $total,
            'rate'  => $total > 0 ? round($present / $total * 100, 1) : null,
        ];
    }
}

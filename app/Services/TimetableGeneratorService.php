<?php

namespace App\Services;

use App\Models\Academic\ClassSection;
use App\Models\Academic\Staff;
use App\Models\Academic\Subject;
use App\Models\Academic\TeacherAvailability;
use App\Models\Academic\TimetableConfig;
use App\Models\Academic\TimetableSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TimetableGeneratorService
{
    private array $warnings = [];

    private int $schoolId;
    private array $teacherAvailability = [];
    private array $teacherSchedule = [];
    private array $classSchedule = [];
    private array $subjectHourTracker = [];

    private array $subjectTeacherMap = [];
    private array $subjectHoursRequired = [];
    private array $roomCapacity = [];

    public function __construct(int $schoolId)
    {
        $this->schoolId = $schoolId;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Generate timetable for multiple class sections
     */
    public function generate(array $classSectionIds, int $academicYearId, array $configOverrides = []): array
    {
        $this->warnings = [];
        $results = [];

        foreach ($classSectionIds as $csId) {
            $config = $this->loadConfig($csId, $academicYearId, $configOverrides);
            if (! $config) {
                $this->warnings[] = "Konfigurasi jadwal tidak ditemukan untuk rombel ID {$csId}.";
                continue;
            }

            $classSection = ClassSection::with('subjects')->find($csId);
            if (! $classSection) {
                $this->warnings[] = "Rombel ID {$csId} tidak ditemukan.";
                continue;
            }

            $slots = $this->generateForClassSection($classSection, $config);
            $results[$csId] = $slots;
        }

        return $results;
    }

    private function loadConfig(int $classSectionId, int $academicYearId, array $overrides): ?TimetableConfig
    {
        $config = TimetableConfig::where('school_id', $this->schoolId)
            ->where('academic_year_id', $academicYearId)
            ->where('class_section_id', $classSectionId)
            ->first();

        if (! $config) {
            $config = new TimetableConfig([
                'school_id'               => $this->schoolId,
                'academic_year_id'        => $academicYearId,
                'class_section_id'        => $classSectionId,
                'days_per_week'           => $overrides['days_per_week'] ?? 5,
                'periods_per_day'         => $overrides['periods_per_day'] ?? 8,
                'period_duration_minutes' => $overrides['period_duration_minutes'] ?? 45,
                'break_after_periods'     => $overrides['break_after_periods'] ?? [],
                'start_time'              => $overrides['start_time'] ?? '07:00',
            ]);
        }

        return $config;
    }

    /**
     * Core algorithm: greedy with conflict resolution
     */
    private function generateForClassSection(ClassSection $classSection, TimetableConfig $config): array
    {
        $this->teacherSchedule = [];
        $this->classSchedule = [];
        $this->subjectHourTracker = [];
        $this->subjectTeacherMap = [];
        $this->subjectHoursRequired = [];
        $slots = [];

        $daysPerWeek = $config->days_per_week;
        $periodsPerDay = $config->periods_per_day;
        $duration = $config->period_duration_minutes;
        $startTime = Carbon::parse($config->start_time);
        $breaks = $config->break_after_periods ?? [];

        $this->loadTeacherAvailability($classSection);

        $subjects = $classSection->subjects;

        if ($subjects->isEmpty()) {
            $this->warnings[] = "Rombel {$classSection->id} belum memiliki mata pelajaran.";
            return [];
        }

        foreach ($subjects as $subject) {
            $teacherId = $subject->pivot->teacher_id ?? null;
            if (! $teacherId) {
                $this->warnings[] = "Mata pelajaran '{$subject->name}' di rombel {$classSection->id} belum memiliki guru.";
                continue;
            }
            $this->subjectTeacherMap[$subject->id] = $teacherId;

            $hours = $subject->credit_hours ?? 2;
            $this->subjectHoursRequired[$subject->id] = $hours;
            $this->subjectHourTracker[$subject->id] = 0;
        }

        $subjectPool = collect($this->subjectTeacherMap)
            ->filter(fn($teacherId, $subjectId) => isset($this->subjectHoursRequired[$subjectId]))
            ->keys()
            ->toArray();

        if (empty($subjectPool)) {
            $this->warnings[] = "Tidak ada mata pelajaran valid untuk rombel {$classSection->id}.";
            return [];
        }

        $subjectSequence = $this->buildBalancedSequence($subjectPool, $daysPerWeek, $periodsPerDay, $breaks);

        $slotId = 0;
        foreach ($subjectSequence as [$day, $period, $subjectId]) {
            $teacherId = $this->subjectTeacherMap[$subjectId] ?? null;
            if (! $teacherId) continue;

            if ($this->isTeacherUnavailable($teacherId, $day)) {
                $subjectName = Subject::find($subjectId)?->name ?? "ID {$subjectId}";
                $this->warnings[] = "Guru mata pelajaran '{$subjectName}' tidak tersedia di hari " . $this->dayName($day) . " — dialokasikan tetap.";
            }

            if ($this->isTeacherDoubleBooked($teacherId, $day, $period)) {
                $backtrack = $this->backtrack($subjectPool, $day, $period, $classSection->id, $subjectId);
                if ($backtrack) {
                    $subjectId = $backtrack;
                    $teacherId = $this->subjectTeacherMap[$backtrack] ?? null;
                    if (! $teacherId) continue;
                }
            }

            if ($this->isTeacherDoubleBooked($teacherId, $day, $period)) {
                $subjectName = Subject::find($subjectId)?->name ?? "ID {$subjectId}";
                $this->warnings[] = "Konflik ganda: '{$subjectName}' di hari " . $this->dayName($day) . " periode {$period} — tidak bisa diselesaikan.";
                continue;
            }

            $pStart = (clone $startTime)->addMinutes(($period - 1) * $duration);
            $pEnd = (clone $pStart)->addMinutes($duration);

            $slots[] = [
                'class_section_id' => $classSection->id,
                'subject_id'       => $subjectId,
                'teacher_id'       => $teacherId,
                'day_of_week'      => $day,
                'start_time'       => $pStart->format('H:i'),
                'end_time'         => $pEnd->format('H:i'),
                'room'             => null,
            ];

            $this->teacherSchedule[$teacherId][$day][$period] = true;
            $this->classSchedule[$classSection->id][$day][$period] = true;
            $this->subjectHourTracker[$subjectId] = ($this->subjectHourTracker[$subjectId] ?? 0) + 1;
            $slotId++;
        }

        $this->checkUnfulfilledHours();

        return $slots;
    }

    private function loadTeacherAvailability(ClassSection $classSection): void
    {
        $teacherIds = $classSection->subjects->pluck('pivot.teacher_id')->filter()->unique()->toArray();
        if (empty($teacherIds)) return;

        $staffIds = Staff::where('school_id', $this->schoolId)
            ->whereIn('user_id', $teacherIds)
            ->pluck('id', 'user_id')
            ->toArray();

        $availabilities = TeacherAvailability::where('school_id', $this->schoolId)
            ->whereIn('staff_id', array_values($staffIds))
            ->get();

        foreach ($availabilities as $avail) {
            $staffRecord = Staff::find($avail->staff_id);
            if (! $staffRecord) continue;

            $userId = $staffRecord->user_id;
            $this->teacherAvailability[$userId][$avail->day_of_week] = $avail->is_available;
        }

        foreach ($teacherIds as $tid) {
            if (! isset($this->teacherAvailability[$tid])) {
                $this->teacherAvailability[$tid] = [];
            }
        }
    }

    private function buildBalancedSequence(array $subjectPool, int $days, int $periods, array $breaks): array
    {
        $sequence = [];
        $breakSet = array_flip($breaks);

        $subjectsWithTargets = [];
        foreach ($subjectPool as $sid) {
            $subjectsWithTargets[$sid] = $this->subjectHoursRequired[$sid] ?? 0;
        }

        arsort($subjectsWithTargets);

        for ($day = 1; $day <= $days; $day++) {
            $daySlots = 0;
            for ($period = 1; $period <= $periods; $period++) {
                if (isset($breakSet[$period])) continue;

                $candidates = array_keys(array_filter($subjectsWithTargets, fn($t) => $t > 0));
                if (empty($candidates)) continue;

                usort($candidates, function ($a, $b) use ($subjectsWithTargets) {
                    return $subjectsWithTargets[$b] - $subjectsWithTargets[$a];
                });

                $chosen = $this->selectBestCandidate($candidates, $day, $period, $sequence);

                if ($chosen !== null) {
                    $sequence[] = [$day, $period, $chosen];
                    $subjectsWithTargets[$chosen]--;
                    $daySlots++;
                }

                if ($daySlots >= ceil(count($subjectPool) * 1.5)) break;
            }
        }

        return $sequence;
    }

    private function selectBestCandidate(array $candidates, int $day, int $period, array $sequence): ?int
    {
        $bestScore = -1;
        $best = null;

        foreach ($candidates as $sid) {
            $score = 0;
            $teacherId = $this->subjectTeacherMap[$sid] ?? null;

            if ($teacherId && $this->isTeacherUnavailable($teacherId, $day)) {
                $score -= 100;
            }

            if ($teacherId && $this->isTeacherDoubleBooked($teacherId, $day, $period)) {
                continue;
            }

            $usedToday = 0;
            foreach ($sequence as $s) {
                if ($s[0] === $day && $s[2] === $sid) $usedToday++;
            }
            if ($usedToday >= 2) {
                $score -= 10;
            }

            $totalAssigned = $this->subjectHourTracker[$sid] ?? 0;
            $required = $this->subjectHoursRequired[$sid] ?? 0;
            $ratio = $required > 0 ? $totalAssigned / $required : 1;

            if ($ratio < 0.5) $score += 5;
            elseif ($ratio < 0.8) $score += 2;

            $score += $required;

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $sid;
            }
        }

        return $best;
    }

    private function backtrack(array $subjectPool, int $day, int $period, int $csId, int $conflictSid): ?int
    {
        foreach ($subjectPool as $sid) {
            if ($sid === $conflictSid) continue;

            $required = $this->subjectHoursRequired[$sid] ?? 0;
            $assigned = $this->subjectHourTracker[$sid] ?? 0;
            if ($assigned >= $required) continue;

            $teacherId = $this->subjectTeacherMap[$sid] ?? null;
            if (! $teacherId) continue;
            if ($this->isTeacherUnavailable($teacherId, $day)) continue;
            if ($this->isTeacherDoubleBooked($teacherId, $day, $period)) continue;

            return $sid;
        }
        return null;
    }

    private function isTeacherUnavailable(int $teacherId, int $day): bool
    {
        return isset($this->teacherAvailability[$teacherId][$day])
            && $this->teacherAvailability[$teacherId][$day] === false;
    }

    private function isTeacherDoubleBooked(int $teacherId, int $day, int $period): bool
    {
        return isset($this->teacherSchedule[$teacherId][$day][$period]);
    }

    private function checkUnfulfilledHours(): void
    {
        foreach ($this->subjectHoursRequired as $sid => $required) {
            $assigned = $this->subjectHourTracker[$sid] ?? 0;
            if ($assigned < $required) {
                $subject = Subject::find($sid);
                $name = $subject?->name ?? "ID {$sid}";
                $this->warnings[] = "'{$name}' hanya terpenuhi {$assigned}/{$required} jam — kurang " . ($required - $assigned) . " jam.";
            }
        }
    }

    public function saveSlots(array $slotsData): int
    {
        $count = 0;
        foreach ($slotsData as $data) {
            $data['school_id'] = $this->schoolId;
            TimetableSlot::create($data);
            $count++;
        }
        return $count;
    }

    public function clearExistingSlots(int $classSectionId): int
    {
        return TimetableSlot::where('school_id', $this->schoolId)
            ->where('class_section_id', $classSectionId)
            ->forceDelete();
    }

    public static function daysList(): array
    {
        return [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
    }

    public static function dayName(int $day): string
    {
        return self::daysList()[$day] ?? 'Hari ' . $day;
    }
}

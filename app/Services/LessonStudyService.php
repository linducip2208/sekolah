<?php

namespace App\Services;

use App\Models\Academic\LessonStudy;
use App\Models\Academic\LessonStudyObservation;
use App\Models\Academic\LessonStudyReflection;
use Illuminate\Support\Collection;

class LessonStudyService
{
    public function compileObservations(LessonStudy $lessonStudy): array
    {
        $observations = $lessonStudy->observations()->with('observer')->get();

        $grouped = $observations->groupBy('observation_type');
        $summary = [];

        $types = ['student_engagement', 'teaching_method', 'class_management', 'material_clarity'];
        $labels = [
            'student_engagement' => 'Keterlibatan Siswa',
            'teaching_method'    => 'Metode Pengajaran',
            'class_management'   => 'Manajemen Kelas',
            'material_clarity'   => 'Kejelasan Materi',
        ];

        foreach ($types as $type) {
            $items = $grouped->get($type, collect());
            $ratings = $items->pluck('rating')->filter();
            $summary[$type] = [
                'label'        => $labels[$type],
                'count'        => $items->count(),
                'avg_rating'   => $ratings->isNotEmpty() ? round($ratings->avg(), 1) : null,
                'notes'        => $items->pluck('notes')->filter()->toArray(),
                'observers'    => $items->pluck('observer.name')->unique()->toArray(),
            ];
        }

        return $summary;
    }

    public function compileReflections(LessonStudy $lessonStudy): array
    {
        $reflections = $lessonStudy->reflections()->with('staff')->get();

        $allStrengths = [];
        $allImprovements = [];
        $allActionPlans = [];
        $reflectors = [];

        foreach ($reflections as $r) {
            $reflectors[] = $r->staff->name;
            if ($r->strength_points) {
                $allStrengths[] = $r->strength_points;
            }
            if ($r->improvement_points) {
                $allImprovements[] = $r->improvement_points;
            }
            if ($r->action_plan) {
                $allActionPlans[] = "{$r->staff->name}: {$r->action_plan}";
            }
        }

        return [
            'count'          => $reflections->count(),
            'reflectors'     => $reflectors,
            'strengths'      => $allStrengths,
            'improvements'   => $allImprovements,
            'action_plans'   => $allActionPlans,
        ];
    }

    public function generateRecommendations(LessonStudy $lessonStudy): array
    {
        $observations = $this->compileObservations($lessonStudy);
        $recommendations = [];

        foreach ($observations as $type => $data) {
            if (($data['avg_rating'] ?? 5) <= 3) {
                $recommendations[] = "Perbaiki aspek '{$data['label']}' — rata-rata penilaian {$data['avg_rating']}/5.";
            }
        }

        $reflections = $this->compileReflections($lessonStudy);
        foreach ($reflections['action_plans'] as $plan) {
            $recommendations[] = "Rencana aksi: $plan";
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Semua aspek observasi mendapat penilaian baik. Lanjutkan konsistensi pengajaran.';
        }

        return $recommendations;
    }

    public function getTeacherParticipationStats(int $schoolId): array
    {
        $studies = LessonStudy::where('school_id', $schoolId)
            ->whereIn('status', ['observed', 'reflected', 'completed'])
            ->withCount('members')
            ->get();

        $totalStudies = $studies->count();
        $totalObservations = LessonStudyObservation::whereHas('lessonStudy', fn ($q) => $schoolId)
            ->count();
        $totalReflections = LessonStudyReflection::whereHas('lessonStudy', fn ($q) => $schoolId)
            ->count();

        return compact('totalStudies', 'totalObservations', 'totalReflections');
    }

    public function advancePhase(LessonStudy $lessonStudy): LessonStudy
    {
        $phases = ['plan', 'do', 'see'];
        $currentIndex = array_search($lessonStudy->phase, $phases);

        if ($currentIndex === false || $currentIndex >= count($phases) - 1) {
            $lessonStudy->update([
                'phase'  => 'see',
                'status' => 'completed',
            ]);
            return $lessonStudy;
        }

        $nextPhase = $phases[$currentIndex + 1];

        $statusMap = [
            'plan' => 'planned',
            'do'   => 'observed',
            'see'  => 'completed',
        ];

        $lessonStudy->update([
            'phase'  => $nextPhase,
            'status' => $statusMap[$nextPhase] ?? 'draft',
        ]);

        return $lessonStudy;
    }
}

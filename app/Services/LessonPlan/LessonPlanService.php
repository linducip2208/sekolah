<?php

namespace App\Services\LessonPlan;

use App\Models\LessonPlan\LessonPlan;
use Illuminate\Support\Facades\DB;

class LessonPlanService
{
    public function create(int $schoolId, int $teacherId, array $data): LessonPlan
    {
        return LessonPlan::create([
            'school_id'           => $schoolId,
            'teacher_id'          => $teacherId,
            'class_section_id'    => $data['class_section_id'],
            'subject_id'          => $data['subject_id'],
            'semester_id'         => $data['semester_id'] ?? null,
            'title'               => $data['title'],
            'lesson_date'         => $data['lesson_date'],
            'duration_minutes'    => $data['duration_minutes'],
            'learning_objectives' => $data['learning_objectives'],
            'material_summary'    => $data['material_summary'],
            'activities'          => $data['activities'],
            'assessment_methods'  => $data['assessment_methods'] ?? [],
            'resources'           => $data['resources'] ?? [],
            'curriculum_type'     => $data['curriculum_type'] ?? 'merdeka',
            'status'              => 'draft',
        ]);
    }

    public function submit(LessonPlan $plan): LessonPlan
    {
        $plan->update(['status' => 'submitted']);
        return $plan->fresh();
    }

    public function approve(LessonPlan $plan, int $reviewerId, ?string $feedback = null): LessonPlan
    {
        $plan->update([
            'status'            => 'approved',
            'reviewer_id'       => $reviewerId,
            'reviewed_at'       => now(),
            'reviewer_feedback' => $feedback,
        ]);
        return $plan->fresh();
    }

    public function reject(LessonPlan $plan, int $reviewerId, string $feedback): LessonPlan
    {
        $plan->update([
            'status'            => 'rejected',
            'reviewer_id'       => $reviewerId,
            'reviewed_at'       => now(),
            'reviewer_feedback' => $feedback,
        ]);
        return $plan->fresh();
    }

    public function markExecuted(LessonPlan $plan, ?string $note): LessonPlan
    {
        $plan->update([
            'actually_executed' => true,
            'execution_note'    => $note,
            'status'            => 'completed',
        ]);
        return $plan->fresh();
    }

    public function coverageReport(int $schoolId, int $semesterId): array
    {
        $plans = LessonPlan::where('school_id', $schoolId)
            ->where('semester_id', $semesterId)
            ->get();

        return [
            'total_plans'        => $plans->count(),
            'approved_plans'     => $plans->where('status', 'approved')->count(),
            'completed_plans'    => $plans->where('actually_executed', true)->count(),
            'completion_rate'    => $plans->count() > 0
                ? round(($plans->where('actually_executed', true)->count() / $plans->count()) * 100, 2)
                : 0,
            'by_teacher'         => $plans->groupBy('teacher_id')
                ->map(fn ($items) => [
                    'total'     => $items->count(),
                    'completed' => $items->where('actually_executed', true)->count(),
                ]),
        ];
    }
}

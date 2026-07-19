<?php

namespace App\Services\Curriculum;

use App\Models\Curriculum\CurriculumCompetency;

class CurriculumService
{
    public function coverageBySemester(int $schoolId, int $semesterId, int $subjectId, int $classRoomId): array
    {
        $totalComp = CurriculumCompetency::where('school_id', $schoolId)
            ->where('subject_id', $subjectId)
            ->where('class_room_id', $classRoomId)
            ->count();

        $coveredComp = CurriculumCompetency::where('school_id', $schoolId)
            ->where('subject_id', $subjectId)
            ->where('class_room_id', $classRoomId)
            ->whereExists(function ($q) use ($semesterId) {
                $q->select('id')
                  ->from('competency_lesson_map as clm')
                  ->join('lesson_plans as lp', 'lp.id', '=', 'clm.lesson_plan_id')
                  ->whereColumn('clm.curriculum_competency_id', 'curriculum_competencies.id')
                  ->where('lp.semester_id', $semesterId)
                  ->where('lp.status', 'approved');
            })
            ->count();

        return [
            'total_competencies'    => $totalComp,
            'covered_competencies'  => $coveredComp,
            'coverage_percent'      => $totalComp > 0 ? round(($coveredComp / $totalComp) * 100, 2) : 0,
        ];
    }
}

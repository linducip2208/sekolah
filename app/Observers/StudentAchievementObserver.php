<?php

namespace App\Observers;

use App\Models\Academic\StudentPoint;
use App\Models\Achievement\StudentAchievement;

class StudentAchievementObserver
{
    public function created(StudentAchievement $achievement): void
    {
        StudentPoint::create([
            'school_id'      => $achievement->school_id,
            'student_id'     => $achievement->student_id,
            'points'         => 25,
            'reason'         => "Prestasi: {$achievement->title}",
            'point_type'     => 'extracurricular',
            'reference_type' => 'student_achievement',
            'reference_id'   => $achievement->id,
            'awarded_by'     => $achievement->verified_by ?? auth()->id(),
            'awarded_at'     => now(),
        ]);
    }
}

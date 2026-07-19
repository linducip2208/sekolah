<?php

namespace App\Observers;

use App\Models\Academic\Mark;
use App\Models\Academic\StudentPoint;

class MarkObserver
{
    public function saved(Mark $mark): void
    {
        if (!$mark->total_marks || $mark->total_marks <= 0) {
            return;
        }

        $percentage = ($mark->obtained_marks / $mark->total_marks) * 100;

        $points = match (true) {
            $percentage >= 80 => 15,
            $percentage >= 60 => 5,
            default           => 0,
        };

        if ($points === 0) {
            return;
        }

        $existing = StudentPoint::where('school_id', $mark->school_id)
            ->where('student_id', $mark->student_id)
            ->where('reference_type', 'mark')
            ->where('reference_id', $mark->id)
            ->exists();

        if ($existing) {
            return;
        }

        StudentPoint::create([
            'school_id'      => $mark->school_id,
            'student_id'     => $mark->student_id,
            'points'         => $points,
            'reason'         => "Nilai akademik: {$mark->obtained_marks}/{$mark->total_marks} ({$percentage}%)",
            'point_type'     => 'academic',
            'reference_type' => 'mark',
            'reference_id'   => $mark->id,
            'awarded_by'     => auth()->id(),
            'awarded_at'     => now(),
        ]);
    }
}

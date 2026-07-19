<?php

namespace App\Observers;

use App\Models\Academic\Attendance;
use App\Models\Academic\StudentPoint;

class AttendanceObserver
{
    public function created(Attendance $attendance): void
    {
        $points = match ($attendance->status) {
            'present' => 10,
            'late'    => 5,
            'absent'  => -5,
            default   => 0,
        };

        if ($points === 0) {
            return;
        }

        StudentPoint::create([
            'school_id'      => $attendance->school_id,
            'student_id'     => $attendance->student_id,
            'points'         => $points,
            'reason'         => "Kehadiran: {$attendance->status} pada {$attendance->date->format('d/m/Y')}",
            'point_type'     => 'attendance',
            'reference_type' => 'attendance',
            'reference_id'   => $attendance->id,
            'awarded_by'     => $attendance->marked_by,
            'awarded_at'     => now(),
        ]);
    }
}

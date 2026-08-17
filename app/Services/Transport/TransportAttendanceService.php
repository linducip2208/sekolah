<?php

namespace App\Services\Transport;

use App\Models\Facilities\StudentTransport;
use App\Models\Transport\TransportAttendance;
use Illuminate\Support\Facades\DB;

class TransportAttendanceService
{
    /** Active students assigned to a route. */
    public function studentsForRoute(int $schoolId, int $routeId): \Illuminate\Support\Collection
    {
        return StudentTransport::where('school_id', $schoolId)
            ->where('transport_route_id', $routeId)
            ->where('is_active', true)
            ->with('student.user')
            ->get();
    }

    /** Save attendance for a route on a date + direction. */
    public function mark(int $schoolId, int $routeId, string $date, string $direction, array $attendance): int
    {
        $count = 0;

        DB::transaction(function () use ($schoolId, $routeId, $date, $direction, $attendance, &$count) {
            foreach ($attendance as $studentId => $status) {
                if (!in_array($status, ['present', 'absent'], true)) {
                    continue;
                }

                TransportAttendance::updateOrCreate(
                    [
                        'school_id'          => $schoolId,
                        'transport_route_id' => $routeId,
                        'student_id'         => $studentId,
                        'date'               => $date,
                        'direction'          => $direction,
                    ],
                    ['status' => $status]
                );
                $count++;
            }
        });

        return $count;
    }

    public function summary(int $schoolId, string $date): array
    {
        $records = TransportAttendance::where('school_id', $schoolId)
            ->where('date', $date)
            ->get();

        return [
            'present' => $records->where('status', 'present')->count(),
            'absent'  => $records->where('status', 'absent')->count(),
            'total'   => $records->count(),
        ];
    }
}

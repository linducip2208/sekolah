<?php

namespace App\Services\DailyReport;

use App\Models\Academic\Attendance;
use App\Models\Academic\Student;
use App\Models\Canteen\CanteenOrder;
use App\Models\Counseling\WellnessCheckin;
use App\Models\DailyReport\DailyReport;
use App\Models\Discipline\DisciplineRecord;
use App\Models\Medical\ClinicVisit;

class DailyReportService
{
    public function generateForSchool(int $schoolId, ?\DateTimeInterface $date = null): int
    {
        $date ??= today();
        $dateStr = $date->format('Y-m-d');

        $count = 0;
        Student::where('school_id', $schoolId)
            ->chunk(100, function ($students) use ($schoolId, $dateStr, &$count) {
                foreach ($students as $student) {
                    $this->generateForStudent($schoolId, $student->id, $dateStr);
                    $count++;
                }
            });

        return $count;
    }

    public function generateForStudent(int $schoolId, int $studentId, string $dateStr): DailyReport
    {
        $attendance = Attendance::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->whereDate('date', $dateStr)
            ->first();

        $canteenOrders = CanteenOrder::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->whereDate('created_at', $dateStr)
            ->get();

        $clinicVisit = ClinicVisit::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->whereDate('visit_at', $dateStr)
            ->first();

        $disciplineEvents = DisciplineRecord::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->whereDate('incident_date', $dateStr)
            ->get();

        $wellness = \App\Models\Wellness\WellnessCheckin::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->whereDate('checkin_date', $dateStr)
            ->first();

        return DailyReport::updateOrCreate(
            ['student_id' => $studentId, 'report_date' => $dateStr],
            [
                'school_id'        => $schoolId,
                'attendance'       => $attendance ? [
                    'status'  => $attendance->status,
                    'note'    => $attendance->note ?? null,
                ] : null,
                'canteen_summary'  => $canteenOrders->isNotEmpty() ? [
                    'orders' => $canteenOrders->count(),
                    'total'  => $canteenOrders->sum('total'),
                ] : null,
                'clinic_visit'     => $clinicVisit ? [
                    'symptoms'  => $clinicVisit->symptoms,
                    'diagnosis' => $clinicVisit->diagnosis,
                    'sent_home' => $clinicVisit->sent_home,
                ] : null,
                'discipline_events' => $disciplineEvents->isNotEmpty() ? $disciplineEvents->toArray() : null,
                'wellness_checkin'  => $wellness ? [
                    'mood_score' => $wellness->mood_score,
                    'tags'       => $wellness->feeling_tags,
                ] : null,
            ],
        );
    }

    public function send(DailyReport $report): DailyReport
    {
        // Hook to NotificationService when ready (FCM/email/WA)
        $report->update(['sent_at' => now()]);
        return $report->fresh();
    }
}

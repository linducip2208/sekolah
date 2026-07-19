<?php

namespace App\Services\Counseling;

use App\Models\Counseling\BullyingReport;
use App\Models\Counseling\CounselingSession;
use App\Models\Wellness\WellnessCheckin;
use Illuminate\Support\Facades\DB;

class CounselingService
{
    public function scheduleSession(int $schoolId, array $data): CounselingSession
    {
        return CounselingSession::create([
            'school_id'        => $schoolId,
            'student_id'       => $data['student_id'],
            'counselor_id'     => $data['counselor_id'],
            'scheduled_at'     => $data['scheduled_at'],
            'duration_minutes' => $data['duration_minutes'] ?? 45,
            'type'             => $data['type'],
            'status'           => 'scheduled',
        ]);
    }

    public function completeSession(CounselingSession $session, ?string $notes, bool $referExternal = false, ?string $referredTo = null): CounselingSession
    {
        $session->update([
            'status'         => 'completed',
            'notes'          => $notes,
            'refer_external' => $referExternal,
            'referred_to'    => $referredTo,
        ]);
        return $session->fresh();
    }

    public function reportBullying(int $schoolId, ?int $reporterId, array $data): BullyingReport
    {
        $isAnonymous = empty($reporterId) || ($data['anonymous'] ?? false);

        $report = BullyingReport::create([
            'school_id'              => $schoolId,
            'reporter_id'            => $isAnonymous ? null : $reporterId,
            'is_anonymous'           => $isAnonymous,
            'victims_described'      => $data['victims_described'] ?? null,
            'perpetrators_described' => $data['perpetrators_described'] ?? null,
            'type'                   => $data['type'],
            'incident_date'          => $data['incident_date'] ?? null,
            'location'               => $data['location'] ?? null,
            'description'            => $data['description'],
            'evidence_files'         => $data['evidence_files'] ?? null,
            'status'                 => 'received',
        ]);

        \App\Jobs\NotifyAdminBullyingReportJob::dispatch($report->id);

        return $report;
    }

    public function assignBullyingReport(BullyingReport $report, int $userId): BullyingReport
    {
        $report->update([
            'assigned_to' => $userId,
            'status'      => 'investigating',
        ]);
        return $report->fresh();
    }

    public function closeBullyingReport(BullyingReport $report, string $status, ?string $actionSummary = null): BullyingReport
    {
        $report->update([
            'status'         => $status,
            'action_summary' => $actionSummary,
        ]);
        return $report->fresh();
    }

    public function recordWellness(int $schoolId, int $studentId, int $moodScore, ?array $tags = null, ?string $note = null): WellnessCheckin
    {
        return DB::transaction(function () use ($schoolId, $studentId, $moodScore, $tags, $note) {
            $checkin = WellnessCheckin::updateOrCreate(
                ['student_id' => $studentId, 'checkin_date' => today()],
                [
                    'school_id'          => $schoolId,
                    'mood_score'         => $moodScore,
                    'feeling_tags'       => $tags,
                    'note'               => $note,
                    'flagged_for_review' => $moodScore <= 3,
                ],
            );
            return $checkin;
        });
    }

    public function atRiskStudents(int $schoolId, int $days = 14)
    {
        return WellnessCheckin::where('school_id', $schoolId)
            ->where('checkin_date', '>=', now()->subDays($days))
            ->where(function ($q) {
                $q->where('mood_score', '<=', 3)
                  ->orWhere('flagged_for_review', true);
            })
            ->get()
            ->groupBy('student_id');
    }
}

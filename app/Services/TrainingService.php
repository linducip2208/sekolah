<?php

namespace App\Services;

use App\Models\Academic\TeacherCertification;
use App\Models\Academic\Training;
use App\Models\Academic\TrainingParticipant;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrainingService
{
    private string $schoolCode;

    public function __construct()
    {
        $school = School::find(auth()->user()->school_id);
        $this->schoolCode = $school ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $school->name), 0, 4)) : 'SCH';
    }

    public function generateCertificateNumber(string $prefix = ''): string
    {
        $year = date('Y');
        $latest = TrainingParticipant::whereNotNull('certificate_number')
            ->where('certificate_number', 'like', "{$this->schoolCode}/DIK/{$year}/%")
            ->orderByDesc('id')
            ->first();

        $seq = $latest
            ? (int) substr($latest->certificate_number, strrpos($latest->certificate_number, '/') + 1) + 1
            : 1;

        $prefixPart = $prefix ? strtoupper($prefix) . '/' : '';

        return "{$this->schoolCode}/DIK/{$year}/{$prefixPart}" . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function getExpiringCertifications(int $daysThreshold = 30): array
    {
        $threshold = Carbon::now()->addDays($daysThreshold);

        return TeacherCertification::where('expiry_date', '<=', $threshold)
            ->where('expiry_date', '>=', Carbon::now())
            ->with('staff')
            ->get()
            ->toArray();
    }

    public function getTrainingHoursPerTeacher(int $schoolId, ?int $year = null): array
    {
        $year = $year ?? date('Y');

        $participants = TrainingParticipant::whereHas('training', function ($q) use ($schoolId, $year) {
            $q->where('school_id', $schoolId)
                ->whereYear('start_date', $year);
        })
            ->whereIn('status', ['attended', 'completed'])
            ->with('training', 'staff')
            ->get();

        $summary = [];
        foreach ($participants as $p) {
            $staffId = $p->staff_id;
            if (!isset($summary[$staffId])) {
                $summary[$staffId] = [
                    'staff_name'  => $p->staff->name,
                    'total_hours' => 0,
                    'count'       => 0,
                ];
            }
            $summary[$staffId]['total_hours'] += $p->training->duration_hours ?? 0;
            $summary[$staffId]['count']++;
        }

        return array_values($summary);
    }

    public function getTrainingCompletionRate(int $trainingId): array
    {
        $total = TrainingParticipant::where('training_id', $trainingId)->count();
        $completed = TrainingParticipant::where('training_id', $trainingId)
            ->whereIn('status', ['attended', 'completed'])
            ->count();

        return [
            'total'     => $total,
            'completed' => $completed,
            'rate'      => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
        ];
    }

    public function getTrainingStats(int $schoolId): array
    {
        $trainings = Training::where('school_id', $schoolId)
            ->withCount(['participants as total_participants'])
            ->withCount(['participants as completed_participants' => fn ($q) => $q->whereIn('status', ['attended', 'completed'])])
            ->orderByDesc('start_date')
            ->get();

        $totalTrainings = $trainings->count();
        $totalParticipants = $trainings->sum('total_participants');
        $totalCompleted = $trainings->sum('completed_participants');
        $totalHours = $trainings->sum('duration_hours');
        $completionRate = $totalParticipants > 0 ? round(($totalCompleted / $totalParticipants) * 100, 1) : 0;

        return compact('totalTrainings', 'totalParticipants', 'totalCompleted', 'totalHours', 'completionRate');
    }

    public function markAttendance(TrainingParticipant $participant, string $status): TrainingParticipant
    {
        $participant->update(['status' => $status]);
        return $participant;
    }

    public function issueCertificate(TrainingParticipant $participant): TrainingParticipant
    {
        $certNumber = $this->generateCertificateNumber();
        $participant->update([
            'certificate_number' => $certNumber,
            'status'             => 'completed',
        ]);
        return $participant;
    }
}

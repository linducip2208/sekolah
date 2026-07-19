<?php

namespace App\Services;

use App\Models\Activity\StudentActivityLog;
use App\Models\Academic\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ActivityTimelineService
{
    public function getTimeline(int $studentId, array $filters = []): LengthAwarePaginator
    {
        $query = StudentActivityLog::where('student_id', $studentId);

        if (!empty($filters['activity_type'])) {
            $query->where('activity_type', $filters['activity_type']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderByDesc('created_at')->paginate(30);
    }

    public function groupByDate(int $studentId, int $days = 30): Collection
    {
        $logs = StudentActivityLog::where('student_id', $studentId)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderByDesc('created_at')
            ->get();

        return $logs->groupBy(fn($log) => $log->created_at->format('Y-m-d'));
    }

    public function groupByWeek(int $studentId, int $weeks = 12): Collection
    {
        $logs = StudentActivityLog::where('student_id', $studentId)
            ->where('created_at', '>=', now()->subWeeks($weeks))
            ->orderByDesc('created_at')
            ->get();

        return $logs->groupBy(fn($log) => 'Minggu ' . $log->created_at->weekOfYear . ' (' . $log->created_at->format('d M') . ')');
    }

    public function log(int $schoolId, int $studentId, string $activityType, string $title, ?string $description = null, ?string $refType = null, ?int $refId = null, array $metadata = []): void
    {
        StudentActivityLog::create([
            'school_id'      => $schoolId,
            'student_id'     => $studentId,
            'activity_type'  => $activityType,
            'title'          => $title,
            'description'    => $description,
            'reference_type' => $refType,
            'reference_id'   => $refId,
            'metadata'       => $metadata,
        ]);
    }

    public function relativeTime(Carbon $date): string
    {
        $diff = $date->diffInSeconds(now());

        if ($diff < 60) return 'Baru saja';
        if ($diff < 3600) return floor($diff / 60) . ' menit yang lalu';
        if ($diff < 86400) return floor($diff / 3600) . ' jam yang lalu';
        if ($diff < 172800) return 'Kemarin';
        if ($diff < 604800) return floor($diff / 86400) . ' hari yang lalu';
        if ($diff < 2592000) return floor($diff / 604800) . ' minggu yang lalu';

        return $date->format('d M Y');
    }

    public function activityColor(string $type): string
    {
        return match ($type) {
            'attendance'   => '#10B981',
            'mark'         => '#3B82F6',
            'exam'         => '#8B5CF6',
            'assignment'   => '#F59E0B',
            'achievement'  => '#EC4899',
            'discipline'   => '#EF4444',
            'payment'      => '#06B6D4',
            'fee'          => '#6366F1',
            'portfolio'    => '#84CC16',
            'event'        => '#F97316',
            'counseling'   => '#14B8A6',
            default        => '#6B7280',
        };
    }

    public function activityIcon(string $type): string
    {
        return match ($type) {
            'attendance'   => '✅',
            'mark'         => '📝',
            'exam'         => '📋',
            'assignment'   => '📄',
            'achievement'  => '🏆',
            'discipline'   => '⚠️',
            'payment'      => '💳',
            'fee'          => '💰',
            'portfolio'    => '📂',
            'event'        => '🎉',
            'counseling'   => '💬',
            default        => '📌',
        };
    }
}

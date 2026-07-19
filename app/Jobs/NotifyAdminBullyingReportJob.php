<?php

namespace App\Jobs;

use App\Models\Counseling\BullyingReport;
use App\Models\User;
use App\Services\Notification\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyAdminBullyingReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $reportId) {}

    public function handle(FcmService $fcm): void
    {
        $report = BullyingReport::find($this->reportId);
        if (!$report) return;

        // Notify admin + counselors
        $adminIds = User::where('school_id', $report->school_id)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'counselor']))
            ->pluck('id');

        $fcm->sendToUsers($adminIds->toArray(),
            '⚠️ Laporan bullying baru',
            "Laporan {$report->type} di " . ($report->location ?? 'sekolah') . ". Mohon segera ditindaklanjuti.",
            ['type' => 'bullying_report', 'report_id' => $report->id],
        );
    }
}

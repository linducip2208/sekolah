<?php

namespace App\Jobs;

use App\Models\Dapodik\DapodikSyncRun;
use App\Services\Dapodik\DapodikService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunDapodikSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(public int $runId) {}

    public function handle(DapodikService $service): void
    {
        $run = DapodikSyncRun::withoutGlobalScopes()->findOrFail($this->runId);
        $service->syncPreviewedRun($run);
    }
}

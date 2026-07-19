<?php

namespace App\Jobs;

use App\Services\DailyReport\DailyReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateDailyReportsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $schoolId, public ?string $date = null) {}

    public function handle(DailyReportService $service): void
    {
        $service->generateForSchool(
            $this->schoolId,
            $this->date ? new \DateTimeImmutable($this->date) : null,
        );
    }
}

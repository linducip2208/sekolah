<?php

namespace App\Jobs;

use App\Services\TimetableGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateTimetableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public function __construct(
        public int $schoolId,
        public array $classSectionIds,
        public int $academicYearId,
        public array $configOverrides = []
    ) {}

    public function handle(): void
    {
        $service = new TimetableGeneratorService($this->schoolId);

        foreach ($this->classSectionIds as $csId) {
            $service->clearExistingSlots($csId);
        }

        $results = $service->generate(
            $this->classSectionIds,
            $this->academicYearId,
            $this->configOverrides
        );

        foreach ($results as $slots) {
            $service->saveSlots($slots);
        }
    }
}

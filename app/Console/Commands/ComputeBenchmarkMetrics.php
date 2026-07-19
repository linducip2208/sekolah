<?php

namespace App\Console\Commands;

use App\Models\Analytics\BenchmarkMetric;
use App\Models\Foundation\Foundation;
use App\Services\BenchmarkService;
use Illuminate\Console\Command;

class ComputeBenchmarkMetrics extends Command
{
    protected $signature = 'benchmark:compute
                            {--period= : Period in Y-m format (default: last month)}
                            {--foundation-id= : Specific foundation ID}
                            {--school-id= : Specific school ID}
                            {--seed : Seed default metrics first}';

    protected $description = 'Menghitung metrik benchmark antar sekolah per yayasan';

    public function handle(BenchmarkService $service): int
    {
        if ($this->option('seed')) {
            $service->seedDefaultMetrics();
            $this->info('Default metrics seeded.');
        }

        $period = $this->option('period') ?? now()->subMonth()->format('Y-m');

        if ($schoolId = $this->option('school-id')) {
            $this->info("Menghitung benchmark untuk sekolah {$schoolId} periode {$period}...");
            $results = $service->computeAllForSchool((int) $schoolId, $period);

            foreach ($results as $key => $result) {
                $status = isset($result['error']) ? "<error>ERROR: {$result['error']}</error>" : "<info>OK: {$result['value']}</info>";
                $this->line("  {$key}: {$status}");
            }

            return self::SUCCESS;
        }

        if ($foundationId = $this->option('foundation-id')) {
            $this->info("Menghitung benchmark untuk yayasan {$foundationId} periode {$period}...");
            $service->computeAndRankForFoundation((int) $foundationId, $period);
            $this->info('Selesai.');

            return self::SUCCESS;
        }

        $this->info("Menghitung benchmark untuk semua yayasan periode {$period}...");
        $foundations = Foundation::all();

        foreach ($foundations as $foundation) {
            $this->line("  Yayasan: {$foundation->name}");
            $service->computeAndRankForFoundation($foundation->id, $period);
        }

        $this->info('Selesai menghitung semua metrik benchmark.');

        return self::SUCCESS;
    }
}

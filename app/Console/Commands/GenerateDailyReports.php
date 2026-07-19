<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Services\DailyReport\DailyReportService;
use Illuminate\Console\Command;

class GenerateDailyReports extends Command
{
    protected $signature   = 'daily-reports:generate {--school_id=* : Limit to specific school IDs}';
    protected $description = 'Generate daily reports for all active schools (Module 43)';

    public function handle(DailyReportService $service): int
    {
        $query = School::where('is_active', true);
        if ($this->option('school_id')) {
            $query->whereIn('id', $this->option('school_id'));
        }

        $totalSchools = 0;
        $totalReports = 0;

        $query->chunk(50, function ($schools) use ($service, &$totalSchools, &$totalReports) {
            foreach ($schools as $school) {
                try {
                    $count = $service->generateForSchool($school->id);
                    $totalReports += $count;
                    $totalSchools++;
                    $this->info("✓ {$school->name}: {$count} reports");
                } catch (\Throwable $e) {
                    $this->error("✗ {$school->name}: " . $e->getMessage());
                }
            }
        });

        $this->info("Done. {$totalSchools} schools, {$totalReports} total reports.");
        return self::SUCCESS;
    }
}

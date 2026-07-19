<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Services\Analytics\RiskScoreService;
use Illuminate\Console\Command;

class ComputeRiskScores extends Command
{
    protected $signature   = 'analytics:risk-scores {--school_id=* : Limit to specific schools}';
    protected $description = 'Compute predictive drop-out risk scores for all active students (Module 45)';

    public function handle(RiskScoreService $service): int
    {
        $query = School::where('is_active', true);
        if ($this->option('school_id')) {
            $query->whereIn('id', $this->option('school_id'));
        }

        $total = 0;
        $query->chunk(20, function ($schools) use ($service, &$total) {
            foreach ($schools as $school) {
                try {
                    $count = $service->computeForSchool($school->id);
                    $total += $count;
                    $this->info("✓ {$school->name}: {$count} students scored");
                } catch (\Throwable $e) {
                    $this->error("✗ {$school->name}: " . $e->getMessage());
                }
            }
        });

        $this->info("Done. {$total} risk scores computed.");
        return self::SUCCESS;
    }
}

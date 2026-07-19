<?php

namespace App\Console\Commands;

use App\Models\Religious\ReligiousModeConfig;
use Illuminate\Console\Command;

class GenerateMonthlyHafalanReport extends Command
{
    protected $signature   = 'religious:monthly-report';
    protected $description = 'Generate monthly hafalan + ibadah reports for parents (Module 28)';

    public function handle(): int
    {
        $configs = ReligiousModeConfig::where('enabled', true)->get();
        $count = 0;

        foreach ($configs as $config) {
            // Stub: dispatch jobs to generate per-student PDFs and email parents
            $this->info("✓ Triggered for school {$config->school_id}");
            $count++;
        }

        $this->info("Done. {$count} schools processed.");
        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Services\Analytics\AnomalyDetectionService;
use Illuminate\Console\Command;

class DetectAnomalies extends Command
{
    protected $signature = 'analytics:detect-anomalies {school_id? : ID sekolah (default: semua)}';

    protected $description = 'Deteksi anomali data sekolah (penurunan kehadiran, dll)';

    public function handle(AnomalyDetectionService $service): int
    {
        $schools = $this->argument('school_id')
            ? collect([School::findOrFail($this->argument('school_id'))])
            : School::where('is_active', true)->get();

        $total = 0;

        foreach ($schools as $school) {
            $total += $service->run($school->id);
        }

        $this->info("Deteksi anomali selesai: {$total} alert baru.");

        return self::SUCCESS;
    }
}

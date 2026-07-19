<?php

namespace App\Console\Commands;

use App\Services\Transport\VehicleTrackingService;
use Illuminate\Console\Command;

class PruneVehicleLocations extends Command
{
    protected $signature   = 'transport:prune-locations {--days=7 : Keep N days of GPS history}';
    protected $description = 'Delete old vehicle GPS location records to prevent table bloat (Module 23)';

    public function handle(VehicleTrackingService $service): int
    {
        $days    = (int) $this->option('days');
        $deleted = $service->pruneOldLocations($days);

        $this->info("Pruned {$deleted} GPS records older than {$days} days");
        return self::SUCCESS;
    }
}

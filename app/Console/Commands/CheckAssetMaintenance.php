<?php

namespace App\Console\Commands;

use App\Models\Inventory\Asset;
use App\Models\Inventory\AssetMaintenanceSchedule;
use Illuminate\Console\Command;

class CheckAssetMaintenance extends Command
{
    protected $signature = 'asset:check-maintenance';
    protected $description = 'Periksa dan tandai maintenance aset yang overdue';

    public function handle(): int
    {
        $this->info('Memeriksa jadwal maintenance...');

        $overdue = AssetMaintenanceSchedule::where('status', 'scheduled')
            ->where('scheduled_date', '<', now()->toDateString())
            ->get();

        $count = 0;
        foreach ($overdue as $s) {
            $s->update(['status' => 'overdue']);
            $count++;
        }

        $this->info("{$count} jadwal maintenance overdue.");

        $dueSoon = Asset::whereNotNull('next_maintenance_date')
            ->where('next_maintenance_date', '<=', now()->addDays(7)->toDateString())
            ->where('next_maintenance_date', '>=', now()->toDateString())
            ->count();

        if ($dueSoon > 0) {
            $this->warn("{$dueSoon} aset memerlukan maintenance dalam 7 hari.");
        }

        return 0;
    }
}

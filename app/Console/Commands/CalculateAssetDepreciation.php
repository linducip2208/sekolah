<?php

namespace App\Console\Commands;

use App\Services\AssetDepreciationService;
use Illuminate\Console\Command;

class CalculateAssetDepreciation extends Command
{
    protected $signature = 'asset:depreciation';
    protected $description = 'Hitung dan simpan penyusutan bulanan untuk seluruh aset';

    public function handle(AssetDepreciationService $service): int
    {
        $this->info('Menghitung penyusutan aset...');
        $count = 0;

        \App\Models\Inventory\Asset::query()
            ->whereNotNull('purchase_date')
            ->whereNotNull('useful_life_years')
            ->where('useful_life_years', '>', 0)
            ->chunk(200, function ($assets) use ($service, &$count) {
                foreach ($assets as $asset) {
                    $dep = $service->calculateMonthlyDepreciation($asset);
                    $asset->update(['monthly_depreciation' => $dep]);
                    $count++;
                }
            });

        $this->info("Selesai. {$count} aset berhasil diperbarui.");
        return 0;
    }
}

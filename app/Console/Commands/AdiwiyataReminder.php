<?php

namespace App\Console\Commands;

use App\Models\Academic\AdiwiyataIndicator;
use App\Models\Academic\AdiwiyataEvidence;
use Illuminate\Console\Command;

class AdiwiyataReminder extends Command
{
    protected $signature = 'adiwiyata:reminder';
    protected $description = 'Ingatkan admin untuk upload bukti indikator Adiwiyata yang belum lengkap';

    public function handle(): int
    {
        $this->info('Memeriksa indikator Adiwiyata yang belum memiliki bukti...');

        $schools = \App\Models\School::all();
        $totalMissing = 0;

        foreach ($schools as $school) {
            $indicators = AdiwiyataIndicator::all();
            $missingCount = 0;

            foreach ($indicators as $indicator) {
                $hasEvidence = AdiwiyataEvidence::where('school_id', $school->id)
                    ->where('adiwiyata_indicator_id', $indicator->id)
                    ->whereIn('status', ['submitted', 'verified'])
                    ->exists();

                if (!$hasEvidence) {
                    $missingCount++;
                }
            }

            if ($missingCount > 0) {
                $totalMissing += $missingCount;
                $this->line("Sekolah ID {$school->id} ({$school->name}): {$missingCount} indikator belum memiliki bukti.");
            }
        }

        $this->info("Total: {$totalMissing} indikator belum terpenuhi di seluruh sekolah.");
        return 0;
    }
}

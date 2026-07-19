<?php

namespace App\Console\Commands;

use App\Models\Emergency\EmergencyAlert;
use App\Services\EmergencyAlertService;
use Illuminate\Console\Command;

class SendEmergencyAlerts extends Command
{
    protected $signature = 'emergency:send {--batch=20 : Jumlah alert per batch}';
    protected $description = 'Kirim alert darurat draft dalam queue batch';

    public function handle(EmergencyAlertService $service): int
    {
        $batchSize = (int) $this->option('batch');

        $alerts = EmergencyAlert::where('status', 'draft')
            ->orderBy('created_at')
            ->limit($batchSize)
            ->get();

        if ($alerts->isEmpty()) {
            $this->info('Tidak ada alert darurat yang tertunda.');

            return self::SUCCESS;
        }

        $this->info("Mengirim {$alerts->count()} alert darurat...");

        foreach ($alerts as $alert) {
            $this->info("Mengirim alert #{$alert->id}: {$alert->title}");

            try {
                $service->sendBroadcast($alert);
                $this->info("  -> Berhasil: {$alert->recipient_count} penerima");
            } catch (\Throwable $e) {
                $this->error("  -> Gagal: {$e->getMessage()}");
                $alert->update(['status' => 'draft']);
            }
        }

        $this->info('Selesai.');

        return self::SUCCESS;
    }
}

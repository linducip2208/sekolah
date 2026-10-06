<?php

namespace App\Console\Commands;

use App\Services\Finance\FeeDunningService;
use Illuminate\Console\Command;

class SendFeeDunning extends Command
{
    protected $signature = 'fee:send-dunning {--school= : Batasi ke school_id tertentu}';
    protected $description = 'Kirim WA dunning SPP bertahap H-7/H-3/H+1/H+7 (idempoten)';

    public function handle(FeeDunningService $service): int
    {
        $result = $service->run($this->option('school') ? (int) $this->option('school') : null);
        $this->info("Dunning sent={$result['sent']} skipped={$result['skipped']}.");

        return self::SUCCESS;
    }
}

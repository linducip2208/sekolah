<?php

namespace App\Console\Commands;

use App\Services\Finance\FeeOverdueService;
use Illuminate\Console\Command;

class MarkFeeOverdue extends Command
{
    protected $signature = 'fee:mark-overdue {--school= : Batasi ke school_id tertentu}';
    protected $description = 'Tandai invoice SPP lewat jatuh tempo sebagai overdue';

    public function handle(FeeOverdueService $service): int
    {
        $count = $service->markOverdue($this->option('school') ? (int) $this->option('school') : null);
        $this->info("Marked {$count} invoices overdue.");

        return self::SUCCESS;
    }
}

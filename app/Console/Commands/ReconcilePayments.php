<?php

namespace App\Console\Commands;

use App\Services\Payment\PaymentService;
use Illuminate\Console\Command;

class ReconcilePayments extends Command
{
    protected $signature   = 'payments:reconcile {--batch=50 : Number of pending transactions to check}';
    protected $description = 'Reconcile pending payment transactions with gateway status (Module 11b)';

    public function handle(PaymentService $service): int
    {
        $batch = (int) $this->option('batch');
        $count = $service->reconcilePending($batch);

        $this->info("Reconciled {$count} transactions");
        return self::SUCCESS;
    }
}

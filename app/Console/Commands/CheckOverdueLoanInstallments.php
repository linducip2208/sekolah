<?php

namespace App\Console\Commands;

use App\Models\Finance\CooperativeInstallment;
use Illuminate\Console\Command;

class CheckOverdueLoanInstallments extends Command
{
    protected $signature = 'cooperative:check-overdue';
    protected $description = 'Periksa dan tandai angsuran pinjaman yang overdue';

    public function handle(): int
    {
        $this->info('Memeriksa angsuran overdue...');

        $overdue = CooperativeInstallment::where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->get();

        $count = 0;
        foreach ($overdue as $inst) {
            $inst->update(['status' => 'overdue']);
            $count++;
        }

        $this->info("{$count} angsuran ditandai overdue.");

        $late = CooperativeInstallment::where('status', 'late')->count();
        if ($late > 0) {
            $this->warn("{$late} angsuran dalam status late (terlambat bayar).");
        }

        return 0;
    }
}

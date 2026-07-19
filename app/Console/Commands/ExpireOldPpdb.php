<?php

namespace App\Console\Commands;

use App\Models\PPDB\PpdbApplication;
use Illuminate\Console\Command;

class ExpireOldPpdb extends Command
{
    protected $signature   = 'ppdb:expire-drafts {--days=30 : Days before draft expires}';
    protected $description = 'Auto-mark old PPDB drafts as withdrew (Module 22)';

    public function handle(): int
    {
        $cutoff = now()->subDays((int) $this->option('days'));
        $count  = PpdbApplication::where('status', 'draft')
            ->where('created_at', '<', $cutoff)
            ->update(['status' => 'withdrew']);

        $this->info("Marked {$count} draft applications as withdrew");
        return self::SUCCESS;
    }
}

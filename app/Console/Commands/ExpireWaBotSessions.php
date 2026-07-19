<?php

namespace App\Console\Commands;

use App\Models\Communication\WaBotConversation;
use Illuminate\Console\Command;

class ExpireWaBotSessions extends Command
{
    protected $signature = 'wa-bot:expire-sessions';
    protected $description = 'Expire inactive WA bot sessions older than 1 hour';

    public function handle(): int
    {
        $expired = WaBotConversation::where('session_active', true)
            ->where('created_at', '<', now()->subHour())
            ->update(['session_active' => false]);

        $this->info("Expired {$expired} WA bot sessions.");

        WaBotConversation::where('created_at', '<', now()->subDays(7))->forceDelete();
        $this->info('Cleaned old WA bot conversations (>7 days).');

        return self::SUCCESS;
    }
}

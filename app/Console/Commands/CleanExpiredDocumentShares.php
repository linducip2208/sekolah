<?php

namespace App\Console\Commands;

use App\Models\Communication\DocumentShare;
use Illuminate\Console\Command;

class CleanExpiredDocumentShares extends Command
{
    protected $signature = 'documents:clean-expired-shares';
    protected $description = 'Nonaktifkan link berbagi dokumen yang sudah kadaluarsa';

    public function handle(): int
    {
        $expired = DocumentShare::where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['is_active' => false]);

        $this->info("{$expired} link berbagi dinonaktifkan.");
        return self::SUCCESS;
    }
}

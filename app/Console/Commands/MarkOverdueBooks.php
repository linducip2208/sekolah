<?php

namespace App\Console\Commands;

use App\Services\Facilities\LibraryService;
use Illuminate\Console\Command;

class MarkOverdueBooks extends Command
{
    protected $signature   = 'library:mark-overdue';
    protected $description = 'Mark books as overdue where due_date has passed';

    public function handle(LibraryService $library): int
    {
        $count = $library->markOverdue();
        $this->info("Marked {$count} book issue(s) as overdue.");
        return self::SUCCESS;
    }
}

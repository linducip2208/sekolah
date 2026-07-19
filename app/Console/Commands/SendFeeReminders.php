<?php

namespace App\Console\Commands;

use App\Models\Finance\FeeInvoice;
use App\Services\Notification\WhatsAppService;
use Illuminate\Console\Command;

class SendFeeReminders extends Command
{
    protected $signature   = 'fee:send-reminders {--days=3 : Remind invoices due within N days}';
    protected $description = 'Send WhatsApp reminders for unpaid fee invoices due soon';

    public function __construct(private WhatsAppService $whatsapp)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $days   = (int) $this->option('days');
        $cutoff = now()->addDays($days)->toDateString();

        $invoices = FeeInvoice::where('status', 'unpaid')
            ->where('due_date', '<=', $cutoff)
            ->with(['student.user', 'student.parents'])
            ->chunk(100, function ($chunk) {
                foreach ($chunk as $invoice) {
                    $student = $invoice->student;
                    if (!$student) {
                        continue;
                    }

                    foreach ($student->parents as $parent) {
                        if ($parent->phone) {
                            $this->whatsapp->sendFeeReminder(
                                $parent->phone,
                                $student->user->name,
                                $invoice->amount,
                                $invoice->due_date
                            );
                        }
                    }
                }
            });

        $this->info('Fee reminders sent.');
        return self::SUCCESS;
    }
}

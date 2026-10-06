<?php

namespace App\Services\Finance;

use App\Models\Finance\FeeDunningLog;
use App\Models\Finance\FeeInvoice;
use App\Services\Communication\WhatsAppNotificationService;
use Illuminate\Support\Facades\Log;

/**
 * FeeDunningService — WA reminder bertahap H-7, H-3, H+1, H+7.
 * Idempoten per (school, invoice, stage). Stop otomatis saat lunas.
 */
class FeeDunningService
{
    /** @var array<string,int> stage => offset hari dari due_date */
    public const STAGES = ['H-7' => -7, 'H-3' => -3, 'H+1' => 1, 'H+7' => 7];

    public function __construct(private WhatsAppNotificationService $wa) {}

    /** @return array{sent:int, skipped:int} */
    public function run(?int $schoolId = null): array
    {
        $sent = 0;
        $skipped = 0;
        $today = today()->toDateString();

        foreach (self::STAGES as $stage => $offset) {
            // H-7/H-3: due = hari ini + |offset| (pengingat sebelum tempo).
            // H+1/H+7: due = hari ini - offset (penagihan setelah tempo).
            $dueDate = today()->subDays($offset)->toDateString();

            $query = FeeInvoice::withoutGlobalScopes()
                ->with(['student.user'])
                ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->whereDate('due_date', $dueDate);

            if ($schoolId) {
                $query->where('school_id', $schoolId);
            }

            $query->orderBy('id')->chunkById(100, function ($invoices) use ($stage, &$sent, &$skipped) {
                foreach ($invoices as $invoice) {
                    if (FeeDunningLog::withoutGlobalScopes()
                        ->where('school_id', $invoice->school_id)
                        ->where('fee_invoice_id', $invoice->id)
                        ->where('stage', $stage)
                        ->exists()) {
                        $skipped++;

                        continue;
                    }

                    $remaining = (int) $invoice->amount - (int) $invoice->discount - (int) $invoice->paid_amount;
                    if ($remaining <= 0) {
                        $skipped++;

                        continue;
                    }

                    $msg = $this->message($invoice, $stage, $remaining);
                    try {
                        $this->wa->sendToGuardian((int) $invoice->student_id, $msg);
                        $status = 'sent';
                    } catch (\Throwable $e) {
                        Log::warning('Dunning WA gagal', ['invoice' => $invoice->id, 'error' => $e->getMessage()]);
                        $status = 'failed';
                    }

                    FeeDunningLog::create([
                        'school_id' => $invoice->school_id,
                        'fee_invoice_id' => $invoice->id,
                        'stage' => $stage,
                        'channel' => 'whatsapp',
                        'status' => $status,
                        'message' => $msg,
                    ]);
                    $sent++;
                }
            });
        }

        return ['sent' => $sent, 'skipped' => $skipped];
    }

    private function message(FeeInvoice $invoice, string $stage, int $remainingCents): string
    {
        $nominal = 'Rp '.number_format($remainingCents / 100, 0, ',', '.');
        $due = $invoice->due_date instanceof \DateTimeInterface
            ? $invoice->due_date->format('d M Y')
            : (string) $invoice->due_date;
        $prefix = match ($stage) {
            'H-7' => 'Pengingat: tagihan SPP jatuh tempo 7 hari lagi.',
            'H-3' => 'Pengingat: tagihan SPP jatuh tempo 3 hari lagi.',
            'H+1' => 'Tagihan SPP telah lewat jatuh tempo 1 hari. Mohon segera dibayar.',
            default => 'Tagihan SPP menunggak 7 hari. Akun dapat dibatasi. Mohon segera dibayar.',
        };

        return "{$prefix} Invoice {$invoice->invoice_no}, sisa {$nominal}, jatuh tempo {$due}. Hubungi admin sekolah untuk link pembayaran.";
    }
}

<?php

namespace App\Observers;

use App\Models\Finance\FeeInvoice;
use App\Services\Notification\FcmService;
use App\Services\Webhook\WebhookDispatcher;

class FeeInvoiceObserver
{
    public function __construct(private FcmService $fcm, private WebhookDispatcher $webhooks) {}

    public function created(FeeInvoice $invoice): void
    {
        $this->webhooks->fire($invoice->school_id, 'invoice.created', $invoice->toArray(), 'invoice-' . $invoice->id);
    }

    public function updated(FeeInvoice $invoice): void
    {
        if ($invoice->wasChanged('status') && $invoice->status === 'paid') {
            $student = $invoice->student;

            if ($student && method_exists($student, 'parents')) {
                $parentIds = $student->parents()->pluck('id')->toArray();
                if (!empty($parentIds)) {
                    $this->fcm->logAndSend(
                        $invoice->school_id,
                        $parentIds,
                        'fee_paid',
                        'Pembayaran Diterima',
                        "Tagihan {$invoice->invoice_no} telah lunas.",
                        ['invoice_id' => $invoice->id]
                    );
                }
            }

            $this->webhooks->fire($invoice->school_id, 'invoice.paid', $invoice->toArray(), 'invoice-paid-' . $invoice->id);
        }
    }
}

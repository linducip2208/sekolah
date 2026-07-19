<?php

namespace App\Jobs;

use App\Models\Payment\PaymentTransaction;
use App\Services\Notification\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyParentPaymentReceivedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $paymentTransactionId) {}

    public function handle(FcmService $fcm): void
    {
        $tx = PaymentTransaction::with('invoice.student')->find($this->paymentTransactionId);
        if (!$tx || $tx->status !== 'paid') return;

        $student = $tx->invoice?->student;
        if (!$student) return;

        $parents = \DB::table('parent_student')
            ->where('student_id', $student->id)
            ->pluck('parent_id');

        $amount = number_format($tx->amount / 100, 0, ',', '.');

        $fcm->sendToUsers($parents->toArray(),
            '✅ Pembayaran SPP berhasil',
            "Pembayaran sebesar Rp {$amount} berhasil diterima. Ref: {$tx->reference_no}",
            ['type' => 'payment_received', 'reference_no' => $tx->reference_no],
        );
    }
}

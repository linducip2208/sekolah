<?php

namespace App\Services\Payment;

use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeePayment;
use App\Models\Payment\PaymentMethod;
use App\Models\Payment\PaymentProvider;
use App\Models\Payment\PaymentTransaction;
use App\Models\Payment\PaymentWebhookLog;
use App\Models\User;
use App\Services\Payment\Exceptions\GatewayException;
use App\Services\Payment\Exceptions\InvalidWebhookSignatureException;
use App\Services\Payment\Support\PaymentTransactionContext;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(protected PaymentAdapterFactory $factory) {}

    public function initiate(
        FeeInvoice $invoice,
        PaymentMethod $method,
        User $initiator,
        ?string $idempotencyKey = null,
    ): PaymentTransaction {
        $idempotencyKey ??= (string) Str::uuid();
        $cacheKey = "payment:idempotency:{$invoice->school_id}:{$idempotencyKey}";

        $existingId = Cache::get($cacheKey);
        if ($existingId) {
            $existing = PaymentTransaction::find($existingId);
            if ($existing) {
                return $existing;
            }
        }

        if ($invoice->status === 'paid') {
            throw new GatewayException('Invoice already paid');
        }

        if ($method->school_id !== $invoice->school_id) {
            throw new GatewayException('Method does not belong to invoice school');
        }

        if (!$method->is_active || !$method->provider->is_active) {
            throw new GatewayException('Payment method is not active');
        }

        $remainingAmount = $invoice->amount - $invoice->discount - $invoice->paid_amount;
        if ($remainingAmount <= 0) {
            throw new GatewayException('No remaining balance');
        }

        if ($remainingAmount < $method->min_amount) {
            throw new GatewayException('Amount below method minimum');
        }
        if ($method->max_amount && $remainingAmount > $method->max_amount) {
            throw new GatewayException('Amount exceeds method maximum');
        }

        $feeAmount = $method->calculateFee($remainingAmount);
        $grossAmount = $method->feeBorneByParent() ? $remainingAmount + $feeAmount : $remainingAmount;
        $netAmount   = $remainingAmount - ($method->feeBorneByParent() ? 0 : $feeAmount);

        $student = $invoice->student;
        $user    = $student?->user;

        $transaction = DB::transaction(function () use (
            $invoice, $method, $initiator, $grossAmount, $feeAmount, $netAmount, $idempotencyKey, $user
        ) {
            $referenceNo = $this->generateReferenceNo($invoice);

            $tx = PaymentTransaction::create([
                'school_id'           => $invoice->school_id,
                'fee_invoice_id'      => $invoice->id,
                'payment_method_id'   => $method->id,
                'payment_provider_id' => $method->payment_provider_id,
                'initiated_by'        => $initiator->id,
                'reference_no'        => $referenceNo,
                'amount'              => $grossAmount,
                'fee_amount'          => $feeAmount,
                'net_amount'          => $netAmount,
                'currency'            => 'IDR',
                'status'              => PaymentTransaction::STATUS_PENDING,
            ]);

            $context = new PaymentTransactionContext(
                invoice:        $invoice,
                method:         $method,
                provider:       $method->provider,
                amountCents:    $grossAmount,
                feeAmountCents: $feeAmount,
                netAmountCents: $netAmount,
                customer: [
                    'name'  => $user?->name ?? 'Student',
                    'email' => $user?->email ?? '',
                    'phone' => $user?->phone ?? '',
                ],
                referenceNo:    $referenceNo,
                callbackUrl:    url('/payment/return?ref=' . $referenceNo),
                webhookUrl:     url('/api/v1/payments/webhook/' . $method->provider->slug),
                expiryMinutes:  $method->expiry_minutes,
                idempotencyKey: $idempotencyKey,
            );

            try {
                $result = $this->factory->for($method->provider)->createTransaction($context);
            } catch (\Throwable $e) {
                $tx->update([
                    'status'       => PaymentTransaction::STATUS_FAILED,
                    'raw_response' => ['error' => $e->getMessage()],
                ]);
                throw $e instanceof GatewayException
                    ? $e
                    : new GatewayException($e->getMessage());
            }

            $tx->update([
                'status'       => PaymentTransaction::STATUS_AWAITING_PAYMENT,
                'external_id'  => $result['external_id'] ?? null,
                'redirect_url' => $result['redirect_url'] ?? null,
                'va_number'    => $result['va_number'] ?? null,
                'va_bank_code' => $result['va_bank_code'] ?? null,
                'qr_string'    => $result['qr_string'] ?? null,
                'deeplink_url' => $result['deeplink_url'] ?? null,
                'expired_at'   => $result['expired_at'] ?? null,
                'raw_request'  => $result['raw_request'] ?? null,
                'raw_response' => $result['raw_response'] ?? null,
            ]);

            return $tx->fresh();
        });

        Cache::put($cacheKey, $transaction->id, now()->addHours(24));

        return $transaction;
    }

    public function handleWebhook(PaymentProvider $provider, array $headers, string $rawBody): PaymentWebhookLog
    {
        $log = PaymentWebhookLog::create([
            'payment_provider_id' => $provider->id,
            'source_ip'           => request()->ip(),
            'headers'             => $headers,
            'payload'             => json_decode($rawBody, true) ?: [],
            'processing_status'   => PaymentWebhookLog::PROCESSING_RECEIVED,
        ]);

        $adapter = $this->factory->for($provider);

        try {
            $adapter->verifyWebhook($headers, $rawBody);
            $log->update(['signature_status' => PaymentWebhookLog::SIGNATURE_VALID]);
        } catch (InvalidWebhookSignatureException $e) {
            $log->update([
                'signature_status'  => PaymentWebhookLog::SIGNATURE_INVALID,
                'processing_status' => PaymentWebhookLog::PROCESSING_FAILED,
                'error_message'     => $e->getMessage(),
            ]);
            Log::warning('Payment webhook signature invalid', [
                'provider' => $provider->id,
                'error'    => $e->getMessage(),
            ]);
            throw $e;
        }

        try {
            $event = $adapter->parseWebhook($log->payload);
            $tx    = PaymentTransaction::where('school_id', $provider->school_id)
                ->where(function ($q) use ($event) {
                    $q->where('external_id', $event['external_id'])
                      ->orWhere('reference_no', $event['external_id']);
                })
                ->first();

            if (!$tx) {
                $log->update([
                    'processing_status' => PaymentWebhookLog::PROCESSING_FAILED,
                    'error_message'     => 'Transaction not found: ' . $event['external_id'],
                ]);
                return $log;
            }

            $log->update(['payment_transaction_id' => $tx->id]);

            if ($tx->isTerminal() && $tx->status === $event['status']) {
                $log->update(['processing_status' => PaymentWebhookLog::PROCESSING_DUPLICATE]);
                return $log;
            }

            $this->applyStatusUpdate($tx, $event);

            $log->update(['processing_status' => PaymentWebhookLog::PROCESSING_PROCESSED]);
        } catch (\Throwable $e) {
            $log->update([
                'processing_status' => PaymentWebhookLog::PROCESSING_FAILED,
                'error_message'     => $e->getMessage(),
            ]);
            throw $e;
        }

        return $log;
    }

    public function applyStatusUpdate(PaymentTransaction $tx, array $event): PaymentTransaction
    {
        return DB::transaction(function () use ($tx, $event) {
            $tx->refresh();

            if ($event['status'] === 'paid' && $tx->status !== PaymentTransaction::STATUS_PAID) {
                $payment = FeePayment::create([
                    'fee_invoice_id' => $tx->fee_invoice_id,
                    'collected_by'   => $tx->initiated_by,
                    'amount'         => $tx->net_amount,
                    'payment_method' => 'gateway',
                    'reference'      => $tx->reference_no,
                    'note'           => 'Online payment via ' . $tx->provider->name,
                    'payment_date'   => now()->toDateString(),
                ]);

                $tx->update([
                    'status'                 => PaymentTransaction::STATUS_PAID,
                    'paid_at'                => $event['paid_at'] ?? now(),
                    'gateway_transaction_id' => $event['gateway_transaction_id'] ?? null,
                    'fee_payment_id'         => $payment->id,
                ]);

                $invoice    = $tx->invoice;
                $totalPaid  = $invoice->payments()->sum('amount');
                $remaining  = $invoice->amount - $invoice->discount - $totalPaid;

                $invoice->update([
                    'paid_amount' => $totalPaid,
                    'status'      => $remaining <= 0 ? 'paid' : 'partial',
                ]);

                \App\Jobs\NotifyParentPaymentReceivedJob::dispatch($tx->id);
            } elseif (in_array($event['status'], ['expired', 'failed', 'cancelled', 'refunded'], true)) {
                $tx->update([
                    'status'                 => match ($event['status']) {
                        'expired'   => PaymentTransaction::STATUS_EXPIRED,
                        'failed'    => PaymentTransaction::STATUS_FAILED,
                        'cancelled' => PaymentTransaction::STATUS_CANCELLED,
                        'refunded'  => PaymentTransaction::STATUS_REFUNDED,
                    },
                    'gateway_transaction_id' => $event['gateway_transaction_id'] ?? $tx->gateway_transaction_id,
                ]);
            }

            return $tx->fresh();
        });
    }

    public function verifyManualPayment(PaymentTransaction $tx, User $verifier): PaymentTransaction
    {
        if ($tx->status !== PaymentTransaction::STATUS_AWAITING_PAYMENT) {
            throw new GatewayException('Transaction not awaiting payment');
        }

        return $this->applyStatusUpdate($tx, [
            'status'                 => 'paid',
            'paid_at'                => now(),
            'gateway_transaction_id' => 'manual-verified-by-' . $verifier->id,
            'external_id'            => $tx->reference_no,
        ]);
    }

    public function rejectManualPayment(PaymentTransaction $tx, string $reason): PaymentTransaction
    {
        return $this->applyStatusUpdate($tx, [
            'status'      => 'failed',
            'external_id' => $tx->reference_no,
            'reason'      => $reason,
        ]);
    }

    public function cancel(PaymentTransaction $tx): PaymentTransaction
    {
        if ($tx->isTerminal()) {
            throw new GatewayException('Cannot cancel terminal transaction');
        }
        return $this->applyStatusUpdate($tx, [
            'status'      => 'cancelled',
            'external_id' => $tx->reference_no,
        ]);
    }

    public function reconcilePending(int $batchSize = 50): int
    {
        $pending = PaymentTransaction::where('status', PaymentTransaction::STATUS_AWAITING_PAYMENT)
            ->where(function ($q) {
                $q->whereNull('expired_at')
                  ->orWhere('expired_at', '<', now()->subMinute());
            })
            ->limit($batchSize)
            ->get();

        $count = 0;
        foreach ($pending as $tx) {
            try {
                if ($tx->isExpired()) {
                    $this->applyStatusUpdate($tx, [
                        'status'      => 'expired',
                        'external_id' => $tx->external_id ?? $tx->reference_no,
                    ]);
                    $count++;
                    continue;
                }

                $adapter = $this->factory->for($tx->provider);
                $status  = $adapter->fetchStatus($tx->external_id ?? $tx->reference_no);

                if (!empty($status['status']) && $status['status'] !== 'pending') {
                    $this->applyStatusUpdate($tx, $status);
                    $count++;
                }
            } catch (\Throwable $e) {
                Log::info('Reconciliation skipped', [
                    'tx'    => $tx->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    protected function generateReferenceNo(FeeInvoice $invoice): string
    {
        return sprintf('PAY-%d-%d-%s', $invoice->school_id, $invoice->id, strtoupper(Str::random(8)));
    }
}

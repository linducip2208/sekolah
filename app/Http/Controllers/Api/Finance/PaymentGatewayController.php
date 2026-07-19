<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\FeeInvoice;
use App\Models\Payment\PaymentMethod;
use App\Models\Payment\PaymentProvider;
use App\Models\Payment\PaymentTransaction;
use App\Services\Payment\Exceptions\GatewayException;
use App\Services\Payment\Exceptions\InvalidWebhookSignatureException;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    public function methods(Request $request): JsonResponse
    {
        $methods = PaymentMethod::with('provider')
            ->where('school_id', $request->user()->school_id)
            ->where('is_active', true)
            ->whereHas('provider', fn ($q) => $q->where('is_active', true))
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PaymentMethod $m) => [
                'id'             => $m->id,
                'code'           => $m->code,
                'display_name'   => $m->display_name,
                'logo_url'       => $m->logo_url,
                'fee_flat'       => $m->fee_flat,
                'fee_percent_bp' => $m->fee_percent_bp,
                'fee_borne_by'   => $m->fee_borne_by,
                'instruction'    => $m->instruction_template,
                'api_format'     => $m->provider->api_format,
                'expiry_minutes' => $m->expiry_minutes,
            ]);

        return response()->json(['data' => $methods]);
    }

    public function initiate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'invoice_id'        => 'required|integer',
            'payment_method_id' => 'required|integer',
            'idempotency_key'   => 'nullable|string|max:100',
        ]);

        $invoice = FeeInvoice::where('school_id', $request->user()->school_id)
            ->findOrFail($data['invoice_id']);

        $method = PaymentMethod::where('school_id', $request->user()->school_id)
            ->findOrFail($data['payment_method_id']);

        try {
            $tx = $this->payments->initiate(
                $invoice,
                $method,
                $request->user(),
                $data['idempotency_key'] ?? $request->header('Idempotency-Key'),
            );
        } catch (GatewayException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($this->presentTransaction($tx), 201);
    }

    public function show(Request $request, string $referenceNo): JsonResponse
    {
        $tx = PaymentTransaction::where('school_id', $request->user()->school_id)
            ->where('reference_no', $referenceNo)
            ->firstOrFail();

        return response()->json($this->presentTransaction($tx));
    }

    public function cancel(Request $request, string $referenceNo): JsonResponse
    {
        $tx = PaymentTransaction::where('school_id', $request->user()->school_id)
            ->where('reference_no', $referenceNo)
            ->firstOrFail();

        if ($tx->initiated_by !== $request->user()->id && !$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $tx = $this->payments->cancel($tx);
        } catch (GatewayException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($this->presentTransaction($tx));
    }

    public function webhook(Request $request, string $providerSlug): JsonResponse
    {
        $provider = PaymentProvider::withoutGlobalScopes()
            ->where('slug', $providerSlug)
            ->where('is_active', true)
            ->firstOrFail();

        try {
            $log = $this->payments->handleWebhook(
                $provider,
                $request->headers->all(),
                $request->getContent(),
            );
        } catch (InvalidWebhookSignatureException) {
            return response()->json(['ok' => false, 'reason' => 'signature'], 401);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'reason' => 'error'], 500);
        }

        return response()->json([
            'ok'         => true,
            'log_id'     => $log->id,
            'processed'  => $log->processing_status,
        ]);
    }

    public function createPaymentLink(Request $request, int $invoiceId): JsonResponse
    {
        $request->merge(['invoice_id' => $invoiceId]);
        $request->validate([
            'payment_method_id' => 'required|integer',
        ]);
        return $this->initiate($request);
    }

    protected function presentTransaction(PaymentTransaction $tx): array
    {
        return [
            'id'                => $tx->id,
            'reference_no'      => $tx->reference_no,
            'external_id'       => $tx->external_id,
            'status'            => $tx->status,
            'amount'            => $tx->amount,
            'fee_amount'        => $tx->fee_amount,
            'net_amount'        => $tx->net_amount,
            'currency'          => $tx->currency,
            'redirect_url'      => $tx->redirect_url,
            'va_number'         => $tx->va_number,
            'va_bank_code'      => $tx->va_bank_code,
            'qr_string'         => $tx->qr_string,
            'deeplink_url'      => $tx->deeplink_url,
            'expired_at'        => $tx->expired_at,
            'paid_at'           => $tx->paid_at,
            'manual_instructions' => $tx->raw_response['bank_accounts'] ?? null,
            'invoice' => [
                'id'         => $tx->invoice->id,
                'invoice_no' => $tx->invoice->invoice_no,
                'amount'     => $tx->invoice->amount,
                'period'     => $tx->invoice->period,
            ],
            'method' => [
                'code'         => $tx->method->code,
                'display_name' => $tx->method->display_name,
                'instruction'  => $tx->method->instruction_template,
            ],
        ];
    }
}

<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Payment\PaymentMethod;
use App\Models\Payment\PaymentProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $methods = PaymentMethod::with('provider')
            ->where('school_id', $request->user()->school_id)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PaymentMethod $m) => $this->present($m));

        return response()->json(['data' => $methods]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateMethod($request);
        $data['school_id'] = $request->user()->school_id;

        PaymentProvider::where('school_id', $data['school_id'])->findOrFail($data['payment_provider_id']);

        $method = PaymentMethod::create($data);
        return response()->json($this->present($method->load('provider')), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $method = PaymentMethod::where('school_id', $request->user()->school_id)->findOrFail($id);
        $method->update($this->validateMethod($request, true));
        return response()->json($this->present($method->fresh('provider')));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $method = PaymentMethod::where('school_id', $request->user()->school_id)->findOrFail($id);
        $method->delete();
        return response()->json(['ok' => true]);
    }

    protected function validateMethod(Request $request, bool $update = false): array
    {
        return $request->validate([
            'payment_provider_id'   => ($update ? 'sometimes|' : '') . 'required|integer|exists:payment_providers,id',
            'code'                  => ($update ? 'sometimes|' : '') . 'required|string|max:50',
            'display_name'          => ($update ? 'sometimes|' : '') . 'required|string|max:200',
            'logo_url'              => 'nullable|url|max:500',
            'instruction_template'  => 'nullable|string',
            'fee_flat'              => 'nullable|integer|min:0',
            'fee_percent_bp'        => 'nullable|integer|min:0|max:10000',
            'fee_borne_by'          => 'nullable|integer|in:0,1',
            'min_amount'            => 'nullable|integer|min:0',
            'max_amount'            => 'nullable|integer|min:0',
            'expiry_minutes'        => 'nullable|integer|min:1|max:43200',
            'is_active'             => 'nullable|boolean',
            'sort_order'            => 'nullable|integer|min:0|max:1000',
        ]);
    }

    protected function present(PaymentMethod $m): array
    {
        return [
            'id'                   => $m->id,
            'code'                 => $m->code,
            'display_name'         => $m->display_name,
            'logo_url'             => $m->logo_url,
            'instruction_template' => $m->instruction_template,
            'fee_flat'             => $m->fee_flat,
            'fee_percent_bp'       => $m->fee_percent_bp,
            'fee_borne_by'         => $m->fee_borne_by,
            'min_amount'           => $m->min_amount,
            'max_amount'           => $m->max_amount,
            'expiry_minutes'       => $m->expiry_minutes,
            'is_active'            => $m->is_active,
            'sort_order'           => $m->sort_order,
            'provider' => [
                'id'         => $m->provider->id,
                'name'       => $m->provider->name,
                'api_format' => $m->provider->api_format,
                'is_active'  => $m->provider->is_active,
            ],
        ];
    }
}

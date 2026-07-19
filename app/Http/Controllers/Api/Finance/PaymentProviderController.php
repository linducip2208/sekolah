<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Payment\PaymentMethod;
use App\Models\Payment\PaymentProvider;
use App\Services\Payment\PaymentAdapterFactory;
use App\Services\Payment\Support\PaymentTransactionContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentProviderController extends Controller
{
    public function __construct(private PaymentAdapterFactory $factory) {}

    public function index(Request $request): JsonResponse
    {
        $providers = PaymentProvider::where('school_id', $request->user()->school_id)
            ->orderBy('priority')
            ->get()
            ->map(fn (PaymentProvider $p) => $this->present($p));

        return response()->json(['data' => $providers]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateProvider($request);
        $data['school_id'] = $request->user()->school_id;
        $data['slug'] = Str::slug($data['name']) . '-' . Str::lower(Str::random(4));

        $provider = new PaymentProvider();
        $provider->fill(collect($data)->except(['api_key', 'secret_key', 'merchant_id', 'webhook_secret'])->toArray());
        $provider->api_key        = $data['api_key'] ?? null;
        $provider->secret_key     = $data['secret_key'] ?? null;
        $provider->merchant_id    = $data['merchant_id'] ?? null;
        $provider->webhook_secret = $data['webhook_secret'] ?? null;
        $provider->save();

        return response()->json($this->present($provider), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $provider = PaymentProvider::where('school_id', $request->user()->school_id)->findOrFail($id);
        $data     = $this->validateProvider($request, true);

        $provider->fill(collect($data)->except(['api_key', 'secret_key', 'merchant_id', 'webhook_secret'])->toArray());
        if (array_key_exists('api_key', $data) && $data['api_key'] !== null) {
            $provider->api_key = $data['api_key'];
        }
        if (array_key_exists('secret_key', $data) && $data['secret_key'] !== null) {
            $provider->secret_key = $data['secret_key'];
        }
        if (array_key_exists('merchant_id', $data) && $data['merchant_id'] !== null) {
            $provider->merchant_id = $data['merchant_id'];
        }
        if (array_key_exists('webhook_secret', $data) && $data['webhook_secret'] !== null) {
            $provider->webhook_secret = $data['webhook_secret'];
        }
        $provider->save();

        return response()->json($this->present($provider));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $provider = PaymentProvider::where('school_id', $request->user()->school_id)->findOrFail($id);
        $provider->delete();
        return response()->json(['ok' => true]);
    }

    public function listPresets(): JsonResponse
    {
        $disk = Storage::disk('local');
        $files = collect($disk->files('payment-presets'))
            ->filter(fn ($f) => str_ends_with($f, '.json'))
            ->map(function ($f) use ($disk) {
                $json = json_decode($disk->get($f), true) ?: [];
                return [
                    'file'        => basename($f),
                    'label'       => $json['label'] ?? basename($f, '.json'),
                    'api_format'  => $json['api_format'] ?? null,
                    'description' => $json['description'] ?? null,
                ];
            })
            ->values();

        return response()->json(['data' => $files]);
    }

    public function getPreset(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|string|regex:/^[\w\-]+\.json$/']);
        $path = 'payment-presets/' . $request->input('file');
        if (!Storage::disk('local')->exists($path)) {
            return response()->json(['message' => 'Preset not found'], 404);
        }
        $data = json_decode(Storage::disk('local')->get($path), true);
        return response()->json($data);
    }

    public function test(Request $request, int $id): JsonResponse
    {
        $provider = PaymentProvider::where('school_id', $request->user()->school_id)->findOrFail($id);

        try {
            $adapter = $this->factory->for($provider);

            if (in_array($provider->api_format, [
                PaymentProvider::FORMAT_CASH,
                PaymentProvider::FORMAT_BANK_TRANSFER_MANUAL,
                PaymentProvider::FORMAT_QRIS_STATIC,
            ], true)) {
                return response()->json([
                    'ok'      => true,
                    'message' => 'Adapter loaded. ' . $provider->api_format . ' has no remote API to test.',
                ]);
            }

            return response()->json([
                'ok'      => true,
                'message' => 'Adapter resolved successfully. Use a real test transaction to verify connectivity.',
                'class'   => get_class($adapter),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    protected function validateProvider(Request $request, bool $update = false): array
    {
        return $request->validate([
            'name'           => ($update ? 'sometimes|' : '') . 'required|string|max:200',
            'api_format'     => ($update ? 'sometimes|' : '') . 'required|in:' . implode(',', PaymentProvider::FORMATS),
            'base_url'       => 'nullable|url|max:500',
            'callback_url'   => 'nullable|url|max:500',
            'api_key'        => 'nullable|string|max:500',
            'secret_key'     => 'nullable|string|max:500',
            'merchant_id'    => 'nullable|string|max:500',
            'webhook_secret' => 'nullable|string|max:500',
            'extra_config'   => 'nullable|array',
            'extra_headers'  => 'nullable|array',
            'is_sandbox'     => 'nullable|boolean',
            'is_active'      => 'nullable|boolean',
            'priority'       => 'nullable|integer|min:0|max:1000',
        ]);
    }

    protected function present(PaymentProvider $p): array
    {
        return [
            'id'             => $p->id,
            'name'           => $p->name,
            'slug'           => $p->slug,
            'api_format'     => $p->api_format,
            'base_url'       => $p->base_url,
            'callback_url'   => $p->callback_url,
            'extra_config'   => $p->extra_config,
            'extra_headers'  => $p->extra_headers,
            'is_sandbox'     => $p->is_sandbox,
            'is_active'      => $p->is_active,
            'priority'       => $p->priority,
            'masked_api_key' => $p->maskedApiKey(),
            'has_secret_key' => !empty($p->secret_key),
            'has_merchant'   => !empty($p->merchant_id),
            'has_webhook'    => !empty($p->webhook_secret),
            'methods_count'  => $p->methods()->count(),
            'created_at'     => $p->created_at,
        ];
    }
}

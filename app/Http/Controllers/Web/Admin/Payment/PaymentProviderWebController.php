<?php

namespace App\Http\Controllers\Web\Admin\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment\PaymentMethod;
use App\Models\Payment\PaymentProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentProviderWebController extends Controller
{
    public function index(): View
    {
        $providers = PaymentProvider::where('school_id', auth()->user()->school_id)
            ->orderBy('priority')
            ->withCount('methods')
            ->get();

        return view('school-admin.payment.providers.index', compact('providers'));
    }

    public function create(): View
    {
        $presets = $this->loadPresets();
        return view('school-admin.payment.providers.create', compact('presets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProvider($request);

        $provider = new PaymentProvider();
        $provider->school_id      = auth()->user()->school_id;
        $provider->name           = $data['name'];
        $provider->slug           = Str::slug($data['name']) . '-' . Str::lower(Str::random(4));
        $provider->api_format     = $data['api_format'];
        $provider->base_url       = $data['base_url'] ?? null;
        $provider->callback_url   = $data['callback_url'] ?? null;
        $provider->extra_config   = $this->parseJson($data['extra_config'] ?? null);
        $provider->extra_headers  = $this->parseJson($data['extra_headers'] ?? null);
        $provider->is_sandbox     = (bool) ($data['is_sandbox'] ?? true);
        $provider->is_active      = (bool) ($data['is_active'] ?? true);
        $provider->priority       = (int) ($data['priority'] ?? 0);

        if (!empty($data['api_key']))         $provider->api_key        = $data['api_key'];
        if (!empty($data['secret_key']))      $provider->secret_key     = $data['secret_key'];
        if (!empty($data['merchant_id']))     $provider->merchant_id    = $data['merchant_id'];
        if (!empty($data['webhook_secret']))  $provider->webhook_secret = $data['webhook_secret'];

        $provider->save();

        return redirect()
            ->route('admin.payment.providers.index')
            ->with('success', 'Provider pembayaran berhasil ditambahkan.');
    }

    public function edit(int $id): View
    {
        $provider = PaymentProvider::where('school_id', auth()->user()->school_id)->findOrFail($id);
        $presets  = $this->loadPresets();
        return view('school-admin.payment.providers.edit', compact('provider', 'presets'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $provider = PaymentProvider::where('school_id', auth()->user()->school_id)->findOrFail($id);
        $data     = $this->validateProvider($request, true);

        $provider->name          = $data['name'] ?? $provider->name;
        $provider->api_format    = $data['api_format'] ?? $provider->api_format;
        $provider->base_url      = $data['base_url'] ?? $provider->base_url;
        $provider->callback_url  = $data['callback_url'] ?? $provider->callback_url;
        $provider->extra_config  = $this->parseJson($data['extra_config'] ?? null) ?? $provider->extra_config;
        $provider->extra_headers = $this->parseJson($data['extra_headers'] ?? null) ?? $provider->extra_headers;
        $provider->is_sandbox    = (bool) $request->input('is_sandbox', $provider->is_sandbox);
        $provider->is_active     = (bool) $request->input('is_active', $provider->is_active);
        $provider->priority      = (int) ($data['priority'] ?? $provider->priority);

        if (!empty($data['api_key']))        $provider->api_key        = $data['api_key'];
        if (!empty($data['secret_key']))     $provider->secret_key     = $data['secret_key'];
        if (!empty($data['merchant_id']))    $provider->merchant_id    = $data['merchant_id'];
        if (!empty($data['webhook_secret'])) $provider->webhook_secret = $data['webhook_secret'];

        $provider->save();

        return redirect()
            ->route('admin.payment.providers.index')
            ->with('success', 'Provider pembayaran berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $provider = PaymentProvider::where('school_id', auth()->user()->school_id)->findOrFail($id);
        $provider->delete();
        return redirect()
            ->route('admin.payment.providers.index')
            ->with('success', 'Provider dihapus.');
    }

    protected function loadPresets(): array
    {
        $merged = [];

        $vendorDir = storage_path('app/payment-gateway-presets');
        if (is_dir($vendorDir)) {
            foreach (glob($vendorDir . DIRECTORY_SEPARATOR . '*.json') ?: [] as $file) {
                $json = @file_get_contents($file);
                if (!$json) continue;
                $data = json_decode($json, true);
                if (!is_array($data) || empty($data['key'])) continue;

                $merged[] = [
                    'file'             => basename($file),
                    'key'              => $data['key'],
                    'label'            => $data['label'] ?? basename($file, '.json'),
                    'logo'             => $data['logo'] ?? '•',
                    'country'          => $data['country'] ?? '',
                    'api_format'       => $data['suggested_format'] ?? null,
                    'base_url'         => $data['base_url_sandbox'] ?? $data['base_url_live'] ?? '',
                    'base_url_live'    => $data['base_url_live'] ?? '',
                    'base_url_sandbox' => $data['base_url_sandbox'] ?? '',
                    'callback_path'    => $data['callback_path'] ?? '',
                    'docs_url'         => $data['docs_url'] ?? '',
                    'fields'           => $data['fields'] ?? [],
                    'priority'         => $data['suggested_priority'] ?? 99,
                    'extra_headers'    => null,
                    'extra_config'     => null,
                    'description'      => null,
                    'is_vendor'        => true,
                ];
            }
        }

        $formatDir = storage_path('app/payment-presets');
        if (is_dir($formatDir)) {
            foreach (glob($formatDir . DIRECTORY_SEPARATOR . '*.json') ?: [] as $file) {
                $json = @file_get_contents($file);
                if (!$json) continue;
                $data = json_decode($json, true);
                if (!is_array($data)) continue;

                $merged[] = [
                    'file'             => basename($file),
                    'key'              => 'fmt_' . basename($file, '.json'),
                    'label'            => $data['label'] ?? basename($file, '.json'),
                    'logo'             => '⚙',
                    'country'          => '',
                    'api_format'       => $data['api_format'] ?? null,
                    'base_url'         => $data['base_url'] ?? '',
                    'base_url_live'    => '',
                    'base_url_sandbox' => $data['base_url'] ?? '',
                    'callback_path'    => '',
                    'docs_url'         => '',
                    'fields'           => [],
                    'priority'         => 999,
                    'extra_headers'    => $data['extra_headers'] ?? null,
                    'extra_config'     => $data['extra_config']  ?? null,
                    'description'      => $data['description'] ?? null,
                    'is_vendor'        => false,
                ];
            }
        }

        usort($merged, fn ($a, $b) => $a['priority'] <=> $b['priority']);
        return $merged;
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
            'extra_config'   => 'nullable|string',
            'extra_headers'  => 'nullable|string',
            'is_sandbox'     => 'nullable|in:0,1,true,false',
            'is_active'      => 'nullable|in:0,1,true,false',
            'priority'       => 'nullable|integer|min:0|max:1000',
        ]);
    }

    protected function parseJson(?string $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : null;
    }
}

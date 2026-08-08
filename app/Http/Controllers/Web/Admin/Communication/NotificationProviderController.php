<?php

namespace App\Http\Controllers\Web\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\Communication\NotificationProvider;
use App\Services\Notification\NotificationDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationProviderController extends Controller
{
    public function index()
    {
        $schoolId = auth()->user()->school_id;
        $providers = NotificationProvider::where('school_id', $schoolId)
            ->orderBy('transport')
            ->orderByDesc('is_default')
            ->get();

        $presets = $this->loadPresets();

        return view('school-admin.notifications.providers', compact('providers', 'presets'));
    }

    public function preset(string $name)
    {
        $safe = preg_replace('/[^a-z0-9_\-]/i', '', $name);
        $path = storage_path("app/notification-provider-presets/{$safe}.json");
        abort_unless(is_file($path), 404);
        $json = json_decode(file_get_contents($path), true);
        return response()->json($json ?: []);
    }

    private function loadPresets(): array
    {
        $dir = storage_path('app/notification-provider-presets');
        if (!is_dir($dir)) return [];
        $files = glob($dir . '/*.json') ?: [];
        $out = [];
        foreach ($files as $f) {
            $slug = basename($f, '.json');
            $j    = json_decode(file_get_contents($f), true);
            $out[$slug] = [
                'slug'        => $slug,
                'transport'   => $j['transport'] ?? '',
                'api_format'  => $j['api_format'] ?? '',
                'label_hint'  => $j['label_hint'] ?? '',
            ];
        }
        return $out;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'transport'   => 'required|in:push,sms,whatsapp,email',
            'api_format'  => 'required|string|max:40',
            'base_url'    => 'nullable|url|max:500',
            'api_key'     => 'nullable|string|max:2000',
            'secret'      => 'nullable|string|max:2000',
            'sender_id'   => 'nullable|string|max:200',
            'extra_headers' => 'nullable|string',
            'extra_config'  => 'nullable|string',
            'is_default'  => 'nullable|boolean',
        ]);

        $schoolId = auth()->user()->school_id;

        $provider = new NotificationProvider();
        $provider->school_id = $schoolId;
        $provider->name = $data['name'];
        $provider->transport = $data['transport'];
        $provider->api_format = $data['api_format'];
        $provider->base_url = $data['base_url'] ?? null;
        $provider->api_key = $data['api_key'] ?? null;
        $provider->secret = $data['secret'] ?? null;
        $provider->sender_id = $data['sender_id'] ?? null;
        $provider->extra_headers = $this->parseJson($data['extra_headers'] ?? null);
        $provider->extra_config  = $this->parseJson($data['extra_config'] ?? null);
        $provider->is_active = true;
        $provider->is_default = (bool) ($data['is_default'] ?? false);
        $provider->save();

        if ($provider->is_default) {
            NotificationProvider::where('school_id', $schoolId)
                ->where('transport', $provider->transport)
                ->where('id', '!=', $provider->id)
                ->update(['is_default' => false]);
        }

        return back()->with('success', 'Provider "' . $provider->name . '" tersimpan.');
    }

    public function toggle(NotificationProvider $provider)
    {
        $this->authorizeAccess($provider);
        $provider->is_active = !$provider->is_active;
        $provider->save();
        return back()->with('success', 'Status provider diperbarui.');
    }

    public function setDefault(NotificationProvider $provider)
    {
        $this->authorizeAccess($provider);
        DB::transaction(function () use ($provider) {
            NotificationProvider::where('school_id', $provider->school_id)
                ->where('transport', $provider->transport)
                ->update(['is_default' => false]);
            $provider->is_default = true;
            $provider->save();
        });
        return back()->with('success', '"' . $provider->name . '" dijadikan default.');
    }

    public function destroy(NotificationProvider $provider)
    {
        $this->authorizeAccess($provider);
        $provider->delete();
        return back()->with('success', 'Provider dihapus.');
    }

    public function test(Request $request, NotificationProvider $provider, NotificationDispatcher $dispatcher)
    {
        $this->authorizeAccess($provider);
        $data = $request->validate([
            'recipient' => 'required|string|max:200',
            'title'     => 'nullable|string|max:200',
            'body'      => 'nullable|string|max:500',
        ]);
        $result = $dispatcher->test(
            $provider,
            $data['recipient'],
            $data['title'] ?? 'Test Notification',
            $data['body'] ?? 'Ini adalah pesan uji dari Sikad Pro.'
        );
        return back()->with('success', 'Hasil test: sent=' . ($result['sent'] ?? 0) . ', failed=' . ($result['failed'] ?? 0));
    }

    private function authorizeAccess(NotificationProvider $provider): void
    {
        abort_unless($provider->school_id === auth()->user()->school_id, 403);
    }

    private function parseJson(?string $value): ?array
    {
        if (!$value) return null;
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : null;
    }
}

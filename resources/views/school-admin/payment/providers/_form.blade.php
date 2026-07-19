@php
    /** @var \App\Models\Payment\PaymentProvider|null $provider */
    $provider ??= null;
    $isEdit = (bool) $provider;
@endphp

<div x-data="providerForm({{ json_encode($presets) }}, {{ json_encode([
        'name'          => old('name', $provider?->name ?? ''),
        'api_format'    => old('api_format', $provider?->api_format ?? 'redirect_checkout'),
        'base_url'      => old('base_url', $provider?->base_url ?? ''),
        'callback_url'  => old('callback_url', $provider?->callback_url ?? ''),
        'is_sandbox'    => (bool) old('is_sandbox', $provider?->is_sandbox ?? true),
        'is_active'     => (bool) old('is_active', $provider?->is_active ?? true),
        'priority'      => (int) old('priority', $provider?->priority ?? 0),
        'extra_config'  => old('extra_config', $provider ? json_encode($provider->extra_config, JSON_PRETTY_PRINT) : ''),
        'extra_headers' => old('extra_headers', $provider ? json_encode($provider->extra_headers, JSON_PRETTY_PRINT) : ''),
    ]) }})" class="space-y-5">

    {{-- Preset selector --}}
    <div class="bg-amber-50 border-l-4 border-amber-500 rounded p-4 text-sm">
        <label class="block font-semibold text-amber-900 mb-2">Pilih Provider Cepat</label>
        <select x-model="selectedPreset" @change="applyPreset()"
                class="w-full border-2 border-amber-200 rounded px-3 py-2 text-sm bg-white">
            <option value="">— Custom (input manual) —</option>
            <optgroup label="Vendor Indonesia & Global">
                @foreach($presets as $p)
                    @if($p['is_vendor'])
                        <option value="{{ $p['key'] }}">{{ $p['logo'] }} {{ $p['label'] }} @if($p['country']) ({{ $p['country'] }})@endif</option>
                    @endif
                @endforeach
            </optgroup>
            <optgroup label="Format Generik (Custom Gateway)">
                @foreach($presets as $p)
                    @if(!$p['is_vendor'])
                        <option value="{{ $p['key'] }}">{{ $p['logo'] }} {{ $p['label'] }}</option>
                    @endif
                @endforeach
            </optgroup>
        </select>
        <div class="mt-2 text-xs text-amber-800" x-show="selectedPreset" x-cloak>
            Form di bawah otomatis ter-isi sesuai provider yang dipilih.
            <span x-show="docsUrl" x-cloak>
                <a :href="docsUrl" target="_blank" class="underline font-semibold">Buka dokumentasi resmi →</a>
            </span>
        </div>
        <div class="mt-2 text-xs text-gray-500" x-show="!selectedPreset">
            Pilih dari dropdown di atas, atau biarkan di "Custom" untuk input manual semua field.
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Nama Provider <span class="text-red-600">*</span></label>
            <input name="name" x-model="form.name" type="text" required maxlength="200"
                   placeholder="contoh: Midtrans Production / VA BCA / QRIS Sekolah"
                   class="w-full border rounded px-3 py-2 text-sm">
            <p class="text-xs text-gray-500 mt-1">Nama bebas — buat label sendiri agar mudah dikenali.</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Format API <span class="text-red-600">*</span></label>
            <select name="api_format" x-model="form.api_format" required class="w-full border rounded px-3 py-2 text-sm">
                <option value="redirect_checkout">Redirect Checkout (Snap / Invoice)</option>
                <option value="virtual_account">Virtual Account (VA)</option>
                <option value="ewallet_deeplink">E-Wallet Deeplink</option>
                <option value="qris_dynamic">QRIS Dynamic (per-invoice)</option>
                <option value="qris_static">QRIS Static (1 QR sekolah)</option>
                <option value="bank_transfer_manual">Bank Transfer Manual</option>
                <option value="cash">Cash (Kasir)</option>
            </select>
        </div>
    </div>

    {{-- Sandbox toggle (auto-switch base URL when preset selected) --}}
    <label class="flex items-center gap-2 text-sm">
        <input type="hidden" name="is_sandbox" value="0">
        <input type="checkbox" name="is_sandbox" value="1" x-model="form.is_sandbox" @change="syncBaseUrl()">
        Mode Sandbox / Testing
    </label>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Base URL</label>
            <input name="base_url" x-model="form.base_url" type="url"
                   placeholder="https://api.gateway.id"
                   class="w-full border rounded px-3 py-2 text-sm font-mono">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Callback URL (opsional)</label>
            <input name="callback_url" x-model="form.callback_url" type="url"
                   placeholder="{{ url('/payment/return') }}"
                   class="w-full border rounded px-3 py-2 text-sm font-mono">
        </div>
    </div>

    {{-- Dynamic credential fields (per-vendor labels when preset selected) --}}
    <template x-if="Object.keys(dynFields).length > 0">
        <div class="space-y-3 p-4 bg-blue-50 border border-blue-200 rounded">
            <div class="text-xs font-semibold text-blue-800 uppercase tracking-wide">Kredensial Provider</div>
            <template x-for="(field, key) in dynFields" :key="key">
                <div>
                    <label class="block text-sm font-medium mb-1">
                        <span x-text="field.label"></span>
                        <span x-show="field.required" class="text-red-600">*</span>
                        <span x-show="!field.required" class="text-gray-400 text-xs normal-case">(opsional)</span>
                    </label>
                    <input type="password" :name="key" maxlength="500" autocomplete="off"
                           :placeholder="field.placeholder || '{{ $isEdit ? 'Kosongkan jika tidak diubah' : 'Masukkan ' }}'"
                           class="w-full border rounded px-3 py-2 text-sm font-mono">
                    <p x-show="field.help" x-text="field.help" class="text-xs text-gray-500 mt-1 italic"></p>
                </div>
            </template>
        </div>
    </template>

    {{-- Static credential fields (when no preset / custom mode) --}}
    <template x-if="Object.keys(dynFields).length === 0">
        <div class="space-y-4 p-4 bg-gray-50 border border-gray-200 rounded">
            <div class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Kredensial (Custom Mode)</div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">API Key</label>
                    <input name="api_key" type="password" autocomplete="off"
                           placeholder="{{ $isEdit ? 'Kosongkan jika tidak diubah' : 'Server Key / Secret Key' }}"
                           class="w-full border rounded px-3 py-2 text-sm font-mono">
                    @if($isEdit && $provider?->maskedApiKey())
                        <p class="text-xs text-gray-500 mt-1">Tersimpan: {{ $provider->maskedApiKey() }}</p>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Secret Key</label>
                    <input name="secret_key" type="password" autocomplete="off"
                           placeholder="{{ $isEdit ? 'Kosongkan jika tidak diubah' : 'Untuk gateway 2-key' }}"
                           class="w-full border rounded px-3 py-2 text-sm font-mono">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Merchant ID</label>
                    <input name="merchant_id" type="text" autocomplete="off"
                           placeholder="{{ $isEdit ? 'Kosongkan jika tidak diubah' : '' }}"
                           class="w-full border rounded px-3 py-2 text-sm font-mono">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Webhook Secret</label>
                    <input name="webhook_secret" type="password" autocomplete="off"
                           placeholder="{{ $isEdit ? 'Kosongkan jika tidak diubah' : 'Untuk verifikasi signature webhook' }}"
                           class="w-full border rounded px-3 py-2 text-sm font-mono">
                </div>
            </div>
        </div>
    </template>

    <div>
        <label class="block text-sm font-medium mb-1">Extra Headers (JSON, opsional)</label>
        <textarea name="extra_headers" x-model="form.extra_headers" rows="3"
                  class="w-full border rounded px-3 py-2 text-sm font-mono"
                  placeholder='{"X-API-Key": "...", "Accept": "application/json"}'></textarea>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Extra Config (JSON)</label>
        <textarea name="extra_config" x-model="form.extra_config" rows="8"
                  class="w-full border rounded px-3 py-2 text-sm font-mono"></textarea>
        <p class="text-xs text-gray-500 mt-1">Field tergantung format. Lihat preset untuk template lengkap.</p>
    </div>

    <div class="flex items-center gap-6">
        <label class="flex items-center gap-2 text-sm">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" x-model="form.is_active">
            Aktif
        </label>
        <label class="flex items-center gap-2 text-sm">
            Prioritas
            <input name="priority" x-model.number="form.priority" type="number" min="0" max="1000"
                   class="w-20 border rounded px-2 py-1 text-sm">
        </label>
    </div>

    <div class="pt-4 border-t flex justify-end gap-2">
        <a href="{{ route('admin.payment.providers.index') }}" class="px-4 py-2 text-sm">Batal</a>
        <button type="submit" class="btn-brand">{{ $isEdit ? 'Simpan Perubahan' : 'Tambah Provider' }}</button>
    </div>
</div>

<script>
function providerForm(presets, initial) {
    return {
        presets: presets,
        selectedPreset: '',
        docsUrl: '',
        form: {
            name:         initial.name || '',
            api_format:   initial.api_format || 'redirect_checkout',
            base_url:     initial.base_url || '',
            callback_url: initial.callback_url || '',
            is_sandbox:   initial.is_sandbox,
            is_active:    initial.is_active,
            priority:     initial.priority,
            extra_config: initial.extra_config || '',
            extra_headers: initial.extra_headers || '',
        },
        dynFields: {},

        applyPreset() {
            if (!this.selectedPreset) {
                this.docsUrl = '';
                this.dynFields = {};
                return;
            }
            const p = this.presets.find(x => x.key === this.selectedPreset);
            if (!p) return;

            this.form.name       = p.label || '';
            this.form.api_format = p.api_format || this.form.api_format;
            this.form.base_url   = this.form.is_sandbox
                ? (p.base_url_sandbox || p.base_url || '')
                : (p.base_url_live || p.base_url || '');

            if (p.callback_path) {
                this.form.callback_url = window.location.origin + p.callback_path;
            }
            if (p.priority !== undefined && p.priority < 99) {
                this.form.priority = p.priority;
            }
            this.docsUrl = p.docs_url || '';

            // Vendor preset has 'fields' schema with custom labels, format preset has extra_config
            this.dynFields = (p.fields && Object.keys(p.fields).length) ? p.fields : {};

            if (p.extra_config) {
                this.form.extra_config = JSON.stringify(p.extra_config, null, 2);
            }
            if (p.extra_headers) {
                this.form.extra_headers = JSON.stringify(p.extra_headers, null, 2);
            }
        },

        syncBaseUrl() {
            if (!this.selectedPreset) return;
            const p = this.presets.find(x => x.key === this.selectedPreset);
            if (!p) return;
            this.form.base_url = this.form.is_sandbox
                ? (p.base_url_sandbox || p.base_url || '')
                : (p.base_url_live || p.base_url || '');
        },
    };
}
</script>

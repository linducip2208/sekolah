@extends('super-admin.layout')
@section('title', 'Gateway Pembayaran')
@section('content')

<div class="mb-8">
    <div class="elite-kicker mb-2">Portae Solutionis · Platform-Level</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Gateway Pembayaran Platform</h1>
    <div class="elite-rule mb-3"></div>
    <p class="font-serif text-base text-gray-600 max-w-3xl">
        Gateway untuk <strong>menerima pembayaran langganan dari sekolah-sekolah</strong> yang mendaftar di
        platform Anda (whitelabel.co.id → sekolah). Uang masuk ke <strong>rekening Anda sebagai pemilik platform</strong>.
    </p>

    <div class="mt-5 grid md:grid-cols-2 gap-4">
        <div class="bg-white border-l-4 p-4" style="border-color: var(--c-accent);">
            <div class="elite-kicker text-[.6rem] mb-2" style="color: var(--c-accent);">Platform-Level (halaman ini)</div>
            <div class="font-serif text-sm text-gray-700">
                <strong>Untuk:</strong> Anda (pemilik platform)<br>
                <strong>Penerima uang:</strong> rekening Anda<br>
                <strong>Pembayar:</strong> sekolah yang baru daftar<br>
                <strong>Kepentingan:</strong> billing langganan SaaS
            </div>
        </div>
        <div class="bg-white border-l-4 border-gray-400 p-4 opacity-90">
            <div class="elite-kicker text-[.6rem] mb-2 text-gray-500">School-Level (di panel admin sekolah)</div>
            <div class="font-serif text-sm text-gray-600">
                <strong>Untuk:</strong> admin sekolah masing-masing<br>
                <strong>Penerima uang:</strong> rekening sekolah tsb<br>
                <strong>Pembayar:</strong> orang tua siswa<br>
                <strong>Kepentingan:</strong> SPP, uang gedung, dsb<br>
                <span class="text-xs italic text-gray-500">Diatur per sekolah di <code>/admin/payment/providers</code></span>
            </div>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Form tambah --}}
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6"
             x-data="gatewayForm({{ json_encode($presets) }})">
            <h3 class="elite-h3 text-lg ink-primary mb-4">Tambah Gateway</h3>

            @if($errors->any())
                <div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>
            @endif

            {{-- Preset selector --}}
            <div class="mb-4 p-4" style="background: rgba(184,134,11,.06); border-left: 3px solid var(--c-accent);">
                <label class="elite-kicker text-[.6rem] block mb-2" style="color: var(--c-accent);">Pilih Provider Cepat</label>
                <select x-model="selectedPreset" @change="applyPreset()"
                        class="w-full border-2 border-rule px-3 py-2 font-serif text-sm bg-white">
                    <option value="">— Custom (input manual) —</option>
                    @foreach($presets as $p)
                        <option value="{{ $p['key'] }}">{{ $p['logo'] ?? '•' }} {{ $p['label'] }} ({{ $p['country'] }})</option>
                    @endforeach
                </select>
                <div class="text-xs text-gray-600 mt-2" x-show="selectedPreset" x-cloak>
                    Form di bawah otomatis ter-isi sesuai provider yang dipilih.
                    Anda bisa edit lagi sebelum simpan.
                </div>
            </div>

            <form method="POST" action="{{ route('super.billing.gateways.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Nama Provider</label>
                    <input type="text" name="name" required maxlength="200" x-model="form.name"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"
                           placeholder="e.g. Midtrans Snap">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Slug (unik)</label>
                    <input type="text" name="slug" required maxlength="100" x-model="form.slug"
                           pattern="[a-z0-9\-_]+"
                           class="w-full border-2 border-rule px-3 py-2 font-mono text-sm"
                           placeholder="midtrans-snap">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Format API</label>
                    <select name="api_format" required x-model="form.api_format"
                            class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— Pilih format —</option>
                        @foreach($formats as $f)
                            <option value="{{ $f }}">
                                @switch($f)
                                    @case('redirect_checkout') Redirect Checkout (Snap, hosted) @break
                                    @case('virtual_account')   Virtual Account @break
                                    @case('ewallet_deeplink')  E-Wallet (deeplink) @break
                                    @case('qris_dynamic')      QRIS Dynamic @break
                                    @case('qris_static')       QRIS Static @break
                                    @case('bank_transfer_manual') Bank Transfer Manual @break
                                    @default {{ $f }}
                                @endswitch
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Sandbox toggle (auto-switch base URL) --}}
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_sandbox" value="1" x-model="form.is_sandbox" @change="syncBaseUrl()">
                    <span class="text-xs">Mode Sandbox / Testing</span>
                </label>

                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Base URL</label>
                    <input type="url" name="base_url" maxlength="500" x-model="form.base_url"
                           class="w-full border-2 border-rule px-3 py-2 font-mono text-xs"
                           placeholder="https://api.midtrans.com">
                </div>

                {{-- Dynamic secret fields --}}
                <template x-for="(field, key) in dynFields" :key="key">
                    <div>
                        <label class="elite-kicker text-[.6rem] block mb-1">
                            <span x-text="field.label"></span>
                            <span x-show="field.required" class="text-red-700">*</span>
                            <span x-show="!field.required" class="text-gray-400 normal-case">(opsional)</span>
                        </label>
                        <input type="text" :name="key" maxlength="500"
                               :placeholder="field.placeholder || ''"
                               class="w-full border-2 border-rule px-3 py-2 font-mono text-xs"
                               autocomplete="off">
                        <p x-show="field.help" x-text="field.help" class="text-xs text-gray-500 mt-1 italic"></p>
                    </div>
                </template>

                {{-- Hidden inputs for any fields not active in preset (still required by validation if user wants custom) --}}
                <template x-if="selectedPreset === ''">
                    <div class="space-y-3 p-3 bg-gray-50 border border-rule">
                        <div class="elite-kicker text-[.6rem]" style="color: var(--c-muted);">Custom Mode — semua kredensial</div>
                        <input type="text" name="api_key" maxlength="500" placeholder="API Key (opsional)" class="w-full border-2 border-rule px-3 py-2 font-mono text-xs" autocomplete="off">
                        <input type="text" name="secret_key" maxlength="500" placeholder="Secret Key (opsional)" class="w-full border-2 border-rule px-3 py-2 font-mono text-xs" autocomplete="off">
                        <input type="text" name="merchant_id" maxlength="500" placeholder="Merchant ID (opsional)" class="w-full border-2 border-rule px-3 py-2 font-mono text-xs" autocomplete="off">
                        <input type="text" name="webhook_secret" maxlength="500" placeholder="Webhook Secret (opsional)" class="w-full border-2 border-rule px-3 py-2 font-mono text-xs" autocomplete="off">
                    </div>
                </template>

                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Callback URL (opsional)</label>
                    <input type="url" name="callback_url" maxlength="500" x-model="form.callback_url"
                           class="w-full border-2 border-rule px-3 py-2 font-mono text-xs"
                           placeholder="https://eschool.test/payment/return">
                </div>

                <div class="flex gap-2 items-end">
                    <div class="flex-1">
                        <label class="elite-kicker text-[.6rem] block mb-1">Prioritas</label>
                        <input type="number" name="priority" min="0" max="100" x-model="form.priority"
                               class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
                    </div>
                </div>

                <p class="text-xs text-gray-500 italic" x-show="docsUrl" x-cloak>
                    📖 Dokumentasi: <a :href="docsUrl" target="_blank" class="underline ink-secondary" x-text="docsUrl"></a>
                </p>

                <button type="submit" class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan Gateway</button>
            </form>
        </div>
    </div>

    <script>
        function gatewayForm(presets) {
            return {
                presets: presets,
                selectedPreset: '',
                docsUrl: '',
                form: {
                    name: '', slug: '', api_format: '',
                    base_url: '', callback_url: '',
                    is_sandbox: true, priority: 0,
                },
                dynFields: {
                    api_key:        { label: 'API Key',        required: false, help: '', placeholder: '' },
                    secret_key:     { label: 'Secret Key',     required: false, help: '', placeholder: '' },
                    merchant_id:    { label: 'Merchant ID',    required: false, help: '', placeholder: '' },
                    webhook_secret: { label: 'Webhook Secret', required: false, help: '', placeholder: '' },
                },
                applyPreset() {
                    if (!this.selectedPreset) {
                        // Reset to defaults
                        this.form = { name:'', slug:'', api_format:'', base_url:'', callback_url:'', is_sandbox:true, priority:0 };
                        this.docsUrl = '';
                        this.dynFields = {
                            api_key:        { label: 'API Key',        required: false, help: '', placeholder: '' },
                            secret_key:     { label: 'Secret Key',     required: false, help: '', placeholder: '' },
                            merchant_id:    { label: 'Merchant ID',    required: false, help: '', placeholder: '' },
                            webhook_secret: { label: 'Webhook Secret', required: false, help: '', placeholder: '' },
                        };
                        return;
                    }
                    const p = this.presets.find(x => x.key === this.selectedPreset);
                    if (!p) return;

                    this.form.name = p.label;
                    this.form.slug = p.key + '-' + Date.now().toString().slice(-4);
                    this.form.api_format = p.suggested_format || '';
                    this.form.base_url = this.form.is_sandbox ? (p.base_url_sandbox || '') : (p.base_url_live || '');
                    this.form.callback_url = p.callback_path
                        ? (window.location.origin + p.callback_path)
                        : '';
                    this.form.priority = p.suggested_priority || 0;
                    this.docsUrl = p.docs_url || '';

                    this.dynFields = p.fields || {};
                },
                syncBaseUrl() {
                    if (!this.selectedPreset) return;
                    const p = this.presets.find(x => x.key === this.selectedPreset);
                    if (!p) return;
                    this.form.base_url = this.form.is_sandbox ? (p.base_url_sandbox || '') : (p.base_url_live || '');
                },
            };
        }
    </script>

    {{-- List --}}
    <div class="lg:col-span-2 space-y-3">
        @forelse($gateways as $g)
            <div class="bg-white border border-rule p-5 {{ !$g->is_active ? 'opacity-50' : '' }}">
                <div class="flex justify-between items-start gap-4">
                    <div class="flex-1">
                        <div class="flex items-baseline gap-3 mb-1">
                            <h4 class="elite-h3 text-lg ink-primary">{{ $g->name }}</h4>
                            @if($g->is_sandbox)
                                <span class="elite-kicker text-[.55rem] px-2 py-0.5 bg-yellow-100 text-yellow-800">SANDBOX</span>
                            @else
                                <span class="elite-kicker text-[.55rem] px-2 py-0.5 bg-green-100 text-green-800">LIVE</span>
                            @endif
                            @if($g->is_active)
                                <span class="text-xs text-green-700">● Aktif</span>
                            @else
                                <span class="text-xs text-red-700">● Off</span>
                            @endif
                        </div>
                        <div class="elite-kicker text-[.55rem] mb-2" style="color: var(--c-muted);">{{ $g->slug }} · Priority {{ $g->priority }}</div>
                        <div class="text-xs space-y-1">
                            <div><span class="font-semibold ink-secondary">Format:</span> {{ $g->formatLabel() }}</div>
                            @if($g->base_url)<div class="font-mono text-gray-600 truncate"><span class="font-semibold ink-secondary">URL:</span> {{ $g->base_url }}</div>@endif
                            <div class="text-gray-500">
                                Secrets:
                                <span class="font-mono">api_key {{ $g->api_key_encrypted ? '✓' : '×' }}</span> ·
                                <span class="font-mono">secret {{ $g->secret_key_encrypted ? '✓' : '×' }}</span> ·
                                <span class="font-mono">merchant {{ $g->merchant_id_encrypted ? '✓' : '×' }}</span> ·
                                <span class="font-mono">webhook {{ $g->webhook_secret_encrypted ? '✓' : '×' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 items-end">
                        <a href="{{ route('super.billing.gateways.edit', $g) }}" class="text-xs underline ink-secondary hover:ink-accent">Edit</a>
                        <form method="POST" action="{{ route('super.billing.gateways.toggle', $g) }}">
                            @csrf
                            <button class="text-xs underline">{{ $g->is_active ? 'Off' : 'On' }}</button>
                        </form>
                        <form method="POST" action="{{ route('super.billing.gateways.destroy', $g) }}"
                              onsubmit="return confirm('Hapus gateway ini?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-700 hover:underline">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white border border-rule p-10 text-center">
                <p class="font-serif text-base text-gray-600 italic mb-3">Belum ada gateway dikonfigurasi.</p>
                <p class="font-serif text-sm text-gray-500">
                    Tambahkan gateway dari form di samping. Sekolah baru akan otomatis melihat opsi gateway online di halaman pendaftaran selain transfer manual.
                </p>
            </div>
        @endforelse

        <div class="mt-6 deco-frame">
            <div class="bg-white p-6">
                <div class="elite-kicker mb-2" style="color: var(--c-accent);">Catatan</div>
                <h4 class="elite-h3 text-base ink-primary mb-3">Format-Agnostic Adapter</h4>
                <ul class="font-serif text-sm text-gray-700 space-y-2 list-disc list-inside">
                    <li><strong>Redirect Checkout</strong> — untuk Snap (Midtrans), Hosted Page (Xendit, DOKU). User di-redirect ke halaman provider, kembali via callback.</li>
                    <li><strong>Virtual Account</strong> — VA per transaksi (Midtrans, Xendit, OY!). User transfer ke nomor VA dynamic.</li>
                    <li><strong>E-Wallet (deeplink)</strong> — OVO, GoPay, Dana, ShopeePay. Buat deeplink → user buka app.</li>
                    <li><strong>QRIS Dynamic</strong> — generate QR per transaksi (auto-amount).</li>
                    <li><strong>QRIS Static</strong> — 1 QR statis untuk semua transaksi (cocok merchant kecil).</li>
                </ul>
                <p class="font-serif text-xs text-gray-500 mt-4 italic">
                    Semua secret keys disimpan terenkripsi (Laravel Crypt). Tidak pernah ditampilkan kembali setelah disimpan.
                </p>
            </div>
        </div>
    </div>
</div>

@endsection

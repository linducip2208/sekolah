@extends('layouts.school-admin')

@section('title', 'Provider Notifikasi')

@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Komunikasi</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Provider Notifikasi</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Konfigurasi Push, SMS, WhatsApp gateway. Pakai kredensial apa pun yang Anda miliki — sistem tidak terkunci ke vendor manapun.</p>
</div>

@if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border-l-4 border-green-700 text-green-800 text-sm">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-700 text-red-800 text-sm">{{ $errors->first() }}</div>
@endif

<div class="bg-white border border-rule p-6 mb-6">
    <h3 class="elite-h3 text-lg ink-primary mb-3">Tambah Provider</h3>

    @if(!empty($presets))
    <div class="mb-3 p-3 bg-amber-50 border border-amber-200 text-xs font-serif">
        <div class="elite-kicker text-[.6rem] mb-2" style="color: var(--c-accent);">Quick Autofill (opsional — kosongkan untuk input manual)</div>
        <div class="flex gap-2 flex-wrap">
            @foreach($presets as $slug => $p)
                <button type="button" onclick="loadPreset('{{ $slug }}')"
                        class="px-2 py-1 border border-rule bg-white hover:bg-gray-100 text-xs font-mono">
                    {{ strtoupper($p['transport']) }} · {{ $p['slug'] }}
                </button>
            @endforeach
        </div>
        <p class="text-[.65rem] text-gray-600 mt-2">Preset hanya untuk autofill template field. Anda bebas edit setelahnya. Code tidak pernah baca file ini saat runtime.</p>
    </div>
    <script>
    function loadPreset(slug) {
        fetch('{{ route('admin.notif.providers.preset', ['name' => 'PLACEHOLDER']) }}'.replace('PLACEHOLDER', slug))
            .then(r => r.json())
            .then(j => {
                const f = document.forms['provider-form'];
                if (j.transport)  f.elements['transport'].value = j.transport;
                if (j.api_format) f.elements['api_format'].value = j.api_format;
                if (j.base_url)   f.elements['base_url'].value = j.base_url;
                if (j.extra_headers) f.elements['extra_headers'].value = JSON.stringify(j.extra_headers, null, 2);
                if (j.extra_config)  f.elements['extra_config'].value  = JSON.stringify(j.extra_config, null, 2);
                if (j.label_hint) {
                    let hint = document.getElementById('preset-hint');
                    if (hint) hint.textContent = j.label_hint;
                }
            });
    }
    </script>
    <p id="preset-hint" class="mb-2 text-[.65rem] text-gray-600 font-serif italic"></p>
    @endif

    <form name="provider-form" method="POST" action="{{ route('admin.notif.providers.store') }}" class="grid md:grid-cols-2 gap-3 text-sm">
        @csrf
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Nama</label>
            <input name="name" required class="w-full border-2 border-rule px-3 py-2 font-serif">
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Transport</label>
            <select name="transport" class="w-full border-2 border-rule px-3 py-2 font-serif">
                <option value="push">Push (mobile)</option>
                <option value="sms">SMS</option>
                <option value="whatsapp">WhatsApp</option>
                <option value="email">Email (custom REST)</option>
            </select>
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">API Format</label>
            <input name="api_format" required placeholder="fcm_legacy | rest_generic | ..." class="w-full border-2 border-rule px-3 py-2 font-mono">
            <p class="text-[.65rem] text-gray-500 mt-1">Contoh: <code>fcm_legacy</code> untuk push, <code>rest_generic</code> untuk gateway REST apa pun.</p>
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Base URL</label>
            <input name="base_url" type="url" placeholder="https://api.example.com/send" class="w-full border-2 border-rule px-3 py-2 font-mono">
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">API Key</label>
            <input name="api_key" class="w-full border-2 border-rule px-3 py-2 font-mono">
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Secret / Token Tambahan</label>
            <input name="secret" class="w-full border-2 border-rule px-3 py-2 font-mono">
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Sender ID / Nama Pengirim</label>
            <input name="sender_id" class="w-full border-2 border-rule px-3 py-2 font-serif">
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Default untuk transport ini</label>
            <select name="is_default" class="w-full border-2 border-rule px-3 py-2 font-serif">
                <option value="0">Tidak</option>
                <option value="1">Ya</option>
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="elite-kicker text-[.6rem] block mb-1">Extra Headers (JSON, opsional)</label>
            <textarea name="extra_headers" rows="2" placeholder='{"X-Custom": "value"}' class="w-full border-2 border-rule px-3 py-2 font-mono"></textarea>
        </div>
        <div class="md:col-span-2">
            <label class="elite-kicker text-[.6rem] block mb-1">Extra Config (JSON, opsional)</label>
            <textarea name="extra_config" rows="3" placeholder='{"method":"POST","auth":"bearer","to_field":"to","message_field":"text"}' class="w-full border-2 border-rule px-3 py-2 font-mono"></textarea>
            <p class="text-[.65rem] text-gray-500 mt-1">Untuk <code>rest_generic</code>: tentukan field mapping. Bisa <code>to_field</code>, <code>message_field</code>, <code>title_field</code>, <code>sender_field</code>, <code>method</code>, <code>auth</code>, <code>auth_param</code>.</p>
        </div>
        <div class="md:col-span-2">
            <button class="btn-elite">Simpan Provider</button>
        </div>
    </form>
</div>

<div class="bg-white border border-rule">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-rule">
            <tr>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Nama</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Transport</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Format</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">URL</th>
                <th class="text-center px-3 py-2 elite-kicker text-[.6rem]">Default</th>
                <th class="text-center px-3 py-2 elite-kicker text-[.6rem]">Aktif</th>
                <th class="text-right px-3 py-2 elite-kicker text-[.6rem]">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($providers as $p)
                <tr class="border-b border-rule">
                    <td class="px-3 py-2 font-serif">{{ $p->name }}</td>
                    <td class="px-3 py-2"><span class="elite-kicker text-[.55rem]">{{ strtoupper($p->transport) }}</span></td>
                    <td class="px-3 py-2 font-mono text-xs">{{ $p->api_format }}</td>
                    <td class="px-3 py-2 font-mono text-xs truncate max-w-[260px]">{{ $p->base_url }}</td>
                    <td class="px-3 py-2 text-center">{!! $p->is_default ? '<span class="text-green-700">★</span>' : '—' !!}</td>
                    <td class="px-3 py-2 text-center">{!! $p->is_active ? '<span class="text-green-700">●</span>' : '<span class="text-gray-400">○</span>' !!}</td>
                    <td class="px-3 py-2 text-right flex justify-end gap-1">
                        <form method="POST" action="{{ route('admin.notif.providers.test', $p) }}" class="inline">@csrf
                            <input type="hidden" name="recipient" value="{{ old('recipient', auth()->user()->phone ?: auth()->user()->fcm_token ?: 'test') }}">
                            <button class="text-blue-700 text-xs underline">Test</button>
                        </form>
                        <form method="POST" action="{{ route('admin.notif.providers.toggle', $p) }}" class="inline">@csrf
                            <button class="text-gray-700 text-xs underline">{{ $p->is_active ? 'Disable' : 'Enable' }}</button>
                        </form>
                        @unless($p->is_default)
                        <form method="POST" action="{{ route('admin.notif.providers.default', $p) }}" class="inline">@csrf
                            <button class="text-amber-700 text-xs underline">Set default</button>
                        </form>
                        @endunless
                        <form method="POST" action="{{ route('admin.notif.providers.destroy', $p) }}" class="inline"
                              onsubmit="return confirm('Hapus provider ini?')">@csrf @method('DELETE')
                            <button class="text-red-700 text-xs underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-3 py-6 text-center text-gray-500 font-serif">Belum ada provider. Tambahkan di atas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

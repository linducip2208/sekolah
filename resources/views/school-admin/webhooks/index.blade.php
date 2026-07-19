@extends('layouts.school-admin')

@section('title', 'Webhooks Outbound')

@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Integrasi</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Webhooks Outbound</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Kirim event sistem ke URL eksternal (Slack, Zapier, n8n, sistem internal Anda). HMAC SHA-256 signed, retry otomatis dengan exponential backoff.</p>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border-l-4 border-green-700 text-green-800 text-sm">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-4 p-3 bg-red-50 border-l-4 border-red-700 text-red-800 text-sm">{{ $errors->first() }}</div>@endif

<div class="bg-white border border-rule p-6 mb-6">
    <h3 class="elite-h3 text-lg ink-primary mb-3">Tambah Webhook</h3>
    <form method="POST" action="{{ route('admin.webhooks.store') }}" class="space-y-3 text-sm">
        @csrf
        <div class="grid md:grid-cols-2 gap-3">
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Nama</label>
                <input name="name" required maxlength="200" class="w-full border-2 border-rule px-3 py-2 font-serif">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">URL Target</label>
                <input name="url" type="url" required class="w-full border-2 border-rule px-3 py-2 font-mono">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Secret (HMAC SHA-256, opsional)</label>
                <input name="secret" class="w-full border-2 border-rule px-3 py-2 font-mono">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Max Retries (0-10)</label>
                <input type="number" name="max_retries" value="3" min="0" max="10" class="w-full border-2 border-rule px-3 py-2 font-mono">
            </div>
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Subscribe Events</label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mt-1">
                @foreach($events as $ev)
                    <label class="flex items-center gap-2 text-xs font-mono">
                        <input type="checkbox" name="events[]" value="{{ $ev }}" class="border-rule">
                        <span>{{ $ev }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Extra Headers (JSON, opsional)</label>
            <textarea name="extra_headers" rows="2" placeholder='{"X-Source": "eschool"}' class="w-full border-2 border-rule px-3 py-2 font-mono"></textarea>
        </div>
        <button class="btn-elite">Simpan Webhook</button>
    </form>
</div>

<div class="bg-white border border-rule">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-rule">
            <tr>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Nama</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">URL</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Events</th>
                <th class="text-center px-3 py-2 elite-kicker text-[.6rem]">Aktif</th>
                <th class="text-right px-3 py-2 elite-kicker text-[.6rem]">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($webhooks as $w)
                <tr class="border-b border-rule">
                    <td class="px-3 py-2 font-serif">{{ $w->name }}</td>
                    <td class="px-3 py-2 font-mono text-xs truncate max-w-[320px]">{{ $w->url }}</td>
                    <td class="px-3 py-2 text-[.7rem] font-mono">{{ implode(', ', $w->events ?? []) }}</td>
                    <td class="px-3 py-2 text-center">{!! $w->is_active ? '<span class="text-green-700">●</span>' : '<span class="text-gray-400">○</span>' !!}</td>
                    <td class="px-3 py-2 text-right">
                        <a href="{{ route('admin.webhooks.deliveries', $w) }}" class="text-blue-700 text-xs underline">Deliveries</a>
                        <form method="POST" action="{{ route('admin.webhooks.toggle', $w) }}" class="inline">@csrf
                            <button class="text-gray-700 text-xs underline ml-2">{{ $w->is_active ? 'Disable' : 'Enable' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.webhooks.destroy', $w) }}" class="inline"
                              onsubmit="return confirm('Hapus webhook ini?')">@csrf @method('DELETE')
                            <button class="text-red-700 text-xs underline ml-2">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500 font-serif">Belum ada webhook.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

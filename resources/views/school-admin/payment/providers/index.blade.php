@extends('layouts.school-admin')
@section('title', 'Provider Pembayaran')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h2 class="text-xl font-bold">Provider Pembayaran SPP</h2>
        <p class="text-sm text-gray-600 max-w-3xl">
            Setup gateway pembayaran <strong>SPP/uang sekolah dari orang tua siswa</strong> ke <strong>rekening sekolah Anda</strong>.
            Tidak ada vendor di-hardcode — input kredensial Midtrans/Xendit/DOKU/dll. Anda sendiri.
        </p>
    </div>
    <a href="{{ route('admin.payment.providers.create') }}" class="btn-brand">+ Tambah Provider</a>
</div>

<div class="mb-4 inline-flex items-start gap-2 text-xs px-3 py-2 bg-blue-50 text-blue-800 rounded">
    <span>ⓘ</span>
    <span>
        Halaman ini <strong>khusus untuk SPP</strong> (orang tua → sekolah). Untuk pembayaran langganan SaaS (sekolah → platform), itu diatur oleh super admin platform di tempat berbeda.
    </span>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600 text-left">
            <tr>
                <th class="px-4 py-3">Nama</th>
                <th class="px-4 py-3">Format</th>
                <th class="px-4 py-3">Mode</th>
                <th class="px-4 py-3">Methods</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">API Key</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($providers as $p)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $p->name }}<br><span class="text-xs text-gray-500">{{ $p->slug }}</span></td>
                    <td class="px-4 py-3"><code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $p->api_format }}</code></td>
                    <td class="px-4 py-3">
                        @if($p->is_sandbox)
                            <span class="inline-block px-2 py-0.5 text-xs bg-yellow-100 text-yellow-800 rounded">Sandbox</span>
                        @else
                            <span class="inline-block px-2 py-0.5 text-xs bg-green-100 text-green-800 rounded">Production</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ $p->methods_count }}</td>
                    <td class="px-4 py-3">
                        @if($p->is_active)
                            <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span> Aktif
                        @else
                            <span class="inline-block w-2 h-2 rounded-full bg-gray-400"></span> Nonaktif
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs font-mono">{{ $p->maskedApiKey() ?? '—' }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('admin.payment.providers.edit', $p->id) }}" class="text-brand-primary hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.payment.providers.destroy', $p->id) }}" class="inline" onsubmit="return confirm('Hapus provider ini?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-gray-500">
                    Belum ada provider. Klik <strong>+ Tambah Provider</strong> untuk mulai.
                </td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

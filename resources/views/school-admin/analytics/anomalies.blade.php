@extends('layouts.school-admin')
@section('title', 'Deteksi Anomali')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Anomalia</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Deteksi Anomali</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">{{ $unresolvedCount }} anomali belum terselesaikan.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif

<div class="mb-6">
    <form method="POST" action="{{ route('admin.analytics.anomalies.run') }}" class="inline">@csrf
        <button class="btn-elite">Jalankan Deteksi Sekarang</button>
    </form>
    <span class="text-xs text-gray-400 ml-3">Berjalan otomatis tiap hari (scheduler).</span>
</div>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white"><tr>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Anomali</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Severity</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Metrik</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Terdeteksi</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Status</th>
            <th class="px-4 py-3"></th>
        </tr></thead>
        <tbody>
            @forelse($alerts as $a)
            <tr class="border-t border-rule hover:bg-gray-50">
                <td class="px-4 py-3">
                    <div class="font-serif font-semibold">{{ $a->title }}</div>
                    <div class="text-xs text-gray-500">{{ $a->description }}</div>
                </td>
                <td class="px-4 py-3 text-center">
                    @if($a->severity === 'high')<span class="text-xs px-2 py-0.5 rounded bg-red-100 text-red-800">Tinggi</span>
                    @elseif($a->severity === 'medium')<span class="text-xs px-2 py-0.5 rounded bg-amber-100 text-amber-800">Sedang</span>
                    @else<span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600">Rendah</span>@endif
                </td>
                <td class="px-4 py-3 text-center font-mono text-xs">{{ $a->metric_value }}{{ $a->reference_value !== null ? ' (ref ' . $a->reference_value . ')' : '' }}</td>
                <td class="px-4 py-3 text-xs">{{ $a->detected_at?->format('d M Y H:i') }}</td>
                <td class="px-4 py-3 text-center">
                    @if($a->resolved_at)<span class="text-xs text-green-700">✓ Selesai</span>
                    @else<span class="text-xs text-amber-700">Terbuka</span>@endif
                </td>
                <td class="px-4 py-3 text-right">
                    @if(!$a->resolved_at)
                    <form method="POST" action="{{ route('admin.analytics.anomalies.resolve', $a) }}" class="inline">@csrf<button class="text-xs underline ink-secondary">Tandai Selesai</button></form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Tidak ada anomali terdeteksi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $alerts->links() }}</div>

@endsection

@extends('layouts.school-admin')
@section('title', 'Pre-Registrasi Tamu')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="max-w-7xl mx-auto">
<div class="mb-7 flex justify-between items-end">
    <div>
        <div class="elite-kicker mb-2">Operasional</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Pre-Registrasi Tamu</h1>
        <div class="elite-rule"></div>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.visitor.pre-registration.export') }}" class="btn-elite-ghost text-xs">Ekspor CSV</a>
        <a href="{{ route('visitor.register') }}" class="btn-elite text-xs" target="_blank">Form Publik ↗</a>
    </div>
</div>

{{-- Tabs --}}
<div class="flex flex-wrap gap-1 mb-6 border-b border-rule">
    @php $tabs = ['pending' => 'Menunggu', 'upcoming' => 'Akan Datang', 'today' => 'Hari Ini', 'history' => 'Riwayat', 'cancelled' => 'Dibatalkan']; @endphp
    @foreach($tabs as $key => $label)
        <a href="?tab={{ $key }}" class="px-4 py-2.5 text-xs font-semibold transition {{ $tab === $key ? 'text-[var(--c-primary)] border-b-2 border-[var(--c-primary)]' : 'text-stone-500 hover:text-stone-700' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tamu</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tujuan</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Host</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kedatangan</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($visitors as $v)
            <tr class="border-t border-rule hover:bg-stone-50">
                <td class="px-4 py-3">
                    <div class="font-serif font-semibold">{{ $v->visitor_name }}</div>
                    <div class="text-xs text-stone-500">{{ $v->phone }}</div>
                </td>
                <td class="px-4 py-3 text-xs max-w-[200px]">{{ $v->purpose }}</td>
                <td class="px-4 py-3 text-xs">{{ $v->hostStaff?->user?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-xs font-mono whitespace-nowrap">{{ $v->expected_arrival?->format('d M Y H:i') ?? '—' }}</td>
                <td class="px-4 py-3">
                    @php
                        $statusColors = [
                            'pending' => 'bg-amber-100 text-amber-700',
                            'checked_in' => 'bg-emerald-100 text-emerald-700',
                            'checked_out' => 'bg-blue-100 text-blue-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                        ];
                    @endphp
                    <span class="text-xs px-2 py-1 rounded {{ $statusColors[$v->status] ?? 'bg-slate-100 text-slate-700' }}">
                        {{ match($v->status) { 'pending' => 'Menunggu', 'checked_in' => 'Check-in', 'checked_out' => 'Check-out', default => ucfirst($v->status) } }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        @if($v->status === 'pending')
                            <form method="POST" action="{{ route('admin.visitor.pre-registration.checkin', $v) }}" class="inline">@csrf<button class="text-xs underline text-emerald-600 hover:text-emerald-800 font-semibold">Check-in</button></form>
                            <form method="POST" action="{{ route('admin.visitor.pre-registration.cancel', $v) }}" class="inline" onsubmit="return confirm('Batalkan kunjungan ini?')">@csrf<button class="text-xs underline text-red-500 hover:text-red-700">Batal</button></form>
                        @elseif($v->status === 'checked_in')
                            <form method="POST" action="{{ route('admin.visitor.pre-registration.checkout', $v) }}" class="inline">@csrf<button class="text-xs underline text-blue-600 hover:text-blue-800 font-semibold">Check-out</button></form>
                        @endif
                        @if($v->qr_code)
                            <span class="text-[.6rem] font-mono text-stone-400">{{ substr($v->qr_code, 0, 8) }}...</span>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada data kunjungan di tab ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $visitors->links() }}</div>
</div>
@endsection

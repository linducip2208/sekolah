@extends('layouts.school-admin')
@section('title', 'Pengadaan Barang & Jasa')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

@php
    $statusColors = [
        'draft'     => 'bg-stone-100 text-stone-600',
        'submitted' => 'bg-blue-100 text-blue-700',
        'approved'  => 'bg-green-100 text-green-700',
        'rejected'  => 'bg-red-100 text-red-700',
        'ordered'   => 'bg-purple-100 text-purple-700',
        'received'  => 'bg-emerald-100 text-emerald-700',
    ];

    $urgencyColors = [
        'low'    => 'bg-stone-100 text-stone-500',
        'medium' => 'bg-sky-100 text-sky-700',
        'high'   => 'bg-orange-100 text-orange-700',
        'urgent' => 'bg-red-100 text-red-700',
    ];

    $statusLabels = [
        'draft' => 'Draft', 'submitted' => 'Diajukan', 'approved' => 'Disetujui',
        'rejected' => 'Ditolak', 'ordered' => 'Dipesan', 'received' => 'Diterima',
    ];
@endphp

<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <div class="elite-kicker mb-2">Keuangan SPP &mdash; Pengadaan</div>
        <h1 class="elite-h1 text-4xl ink-primary mb-1">Pengadaan Barang &amp; Jasa</h1>
        <p class="font-serif text-sm" style="color: var(--c-muted);">Manajemen permintaan pengadaan — dari draft hingga barang diterima.</p>
    </div>
    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('admin.procurement.approvals') }}" class="px-3 py-2 text-xs font-semibold uppercase tracking-wider flex items-center gap-2 bg-amber-50 text-amber-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Persetujuan
        </a>
        <a href="{{ route('admin.procurement.suppliers') }}" class="btn-elite-ghost text-xs">Supplier</a>
        <a href="{{ route('admin.procurement.create') }}" class="btn-elite text-xs">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Permintaan Baru
        </a>
    </div>
</div>

{{-- Status tabs --}}
<div class="flex flex-wrap gap-2 mb-6">
    <a href="{{ route('admin.procurement.index') }}"
       class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wider {{ empty($status) ? 'bg-[var(--c-primary)] text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }} transition">
        Semua
    </a>
    @foreach(['draft', 'submitted', 'approved', 'ordered', 'received', 'rejected'] as $s)
        <a href="{{ route('admin.procurement.index', ['status' => $s]) }}"
           class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wider {{ ($status ?? '') === $s ? 'bg-[var(--c-primary)] text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }} transition">
            {{ $statusLabels[$s] }}
            @if($statusCounts[$s] ?? 0)
                <span class="ml-1.5 px-1.5 py-0.5 text-[.6rem] font-bold rounded-full {{ ($status ?? '') === $s ? 'bg-white/20 text-white' : 'bg-stone-200 text-stone-600' }}">
                    {{ $statusCounts[$s] }}
                </span>
            @endif
        </a>
    @endforeach
</div>

{{-- Search --}}
<form method="GET" class="mb-6">
    <div class="relative max-w-sm">
        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nomor atau judul..."
               class="w-full pl-10 pr-4 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
    </div>
</form>

{{-- Card list --}}
<div class="space-y-4">
    @forelse($requests ?? [] as $pr)
    @php
        $itemCount = $pr->items_count ?? $pr->items->count();
        $totalEst = $pr->totalEstimated();
    @endphp
    <a href="{{ route('admin.procurement.show', $pr->id) }}" class="elite-card p-5 block group">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-2">
                    <span class="font-mono text-sm font-semibold text-[var(--c-primary)]">{{ $pr->request_number }}</span>
                    <span class="px-2 py-0.5 text-[.6rem] font-semibold uppercase tracking-wider {{ $statusColors[$pr->status] ?? 'bg-stone-100 text-stone-600' }}">{{ $statusLabels[$pr->status] ?? $pr->status }}</span>
                    <span class="px-2 py-0.5 text-[.6rem] font-semibold uppercase tracking-wider {{ $urgencyColors[$pr->urgency] ?? '' }}">{{ $pr->urgency }}</span>
                </div>
                <h3 class="font-serif font-semibold text-lg ink-primary mb-2 leading-tight">{{ $pr->title }}</h3>
                <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-stone-500 font-serif">
                    <span>{{ $pr->requester?->name ?? '-' }}</span>
                    @if($pr->department)
                        <span>{{ $pr->department }}</span>
                    @endif
                    <span>{{ $itemCount }} item</span>
                    <span>Rp {{ number_format($totalEst / 100, 0, ',', '.') }}</span>
                    <span>{{ $pr->created_at->translatedFormat('d M Y') }}</span>
                </div>
            </div>
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-stone-300 group-hover:text-[var(--c-primary)] transition" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </div>
        </div>
    </a>
    @empty
    <div class="elite-card p-10 text-center">
        <p class="font-serif text-stone-500">Belum ada permintaan pengadaan.</p>
    </div>
    @endforelse
</div>

{{ $requests->links() ?? '' }}

@endsection

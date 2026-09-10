@extends('layouts.school-admin')
@section('title', 'My Work')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
            <h1 class="page-title">My Work</h1>
            <p class="text-sm text-[var(--color-text-secondary)] mt-1">
                @if($total > 0)
                    Ada <strong>{{ $total }} hal</strong> yang membutuhkan aksi Anda — urut dari yang paling mendesak.
                @else
                    Semua pekerjaan selesai. Tidak ada yang menunggu aksi Anda.
                @endif
            </p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm self-start">← Dashboard</a>
    </div>

    @if($total === 0)
        <div class="card">
            <div class="empty-state">
                <div class="empty-icon">
                    <x-ui.icon name="check" class="w-7 h-7" />
                </div>
                <p class="empty-title">Inbox nol. Kerja bagus!</p>
                <p class="empty-desc">Tidak ada approval, verifikasi, atau input harian yang menunggu.
                    Item baru akan muncul di sini otomatis saat ada yang perlu ditangani.</p>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm mt-4">Kembali ke Dashboard</a>
            </div>
        </div>
    @endif

    @foreach($grouped as $priority => $group)
        @if(empty($group['items']))
            @continue
        @endif
        <section aria-labelledby="mw-{{ $priority }}">
            <div class="flex items-center gap-2 mb-2">
                <span class="badge badge-{{ $group['tone'] }}">{{ $group['label'] }}</span>
                <span id="mw-{{ $priority }}" class="text-xs text-[var(--color-text-muted)]">{{ $group['desc'] }}</span>
                <span class="text-xs font-semibold text-[var(--color-text-muted)]">{{ count($group['items']) }}</span>
            </div>
            <div class="card divide-y divide-[var(--color-border)]">
                @foreach($group['items'] as $item)
                    <a href="{{ $item['href'] }}"
                       class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 px-4 sm:px-5 py-3.5 hover:bg-[var(--color-surface-hover)] transition group">
                        <div class="h-9 w-9 rounded-lg flex items-center justify-center flex-shrink-0"
                             style="background: var(--color-{{ $group['tone'] }}-soft); color: var(--color-{{ $group['tone'] }});">
                            <x-ui.icon name="{{ $group['icon'] }}" class="w-5 h-5" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold flex items-center gap-2">
                                {{ $item['label'] }}
                                <span class="badge badge-{{ $group['tone'] }}">{{ $item['count'] }}</span>
                            </div>
                            <div class="text-xs text-[var(--color-text-secondary)] mt-0.5">{{ $item['desc'] }}</div>
                        </div>
                        <span class="inline-flex items-center gap-1 text-sm font-semibold text-[var(--color-primary)] whitespace-nowrap">
                            {{ $item['cta'] }}
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach

</div>

@endsection

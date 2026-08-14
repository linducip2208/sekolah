@extends('layouts.school-admin')
@section('title', 'Workflow & Persetujuan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

@php
    $statusTone = fn ($st) => match ($st) { 'approved' => 'success', 'rejected' => 'danger', 'under_review' => 'info', 'submitted' => 'warning', default => 'default' };
    $currentStatus = request('status');
@endphp

<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
            <h1 class="page-title">Workflow &amp; Persetujuan</h1>
            <p class="text-sm text-[var(--color-text-secondary)] mt-1">Ajukan dan proses permintaan persetujuan dalam satu antrian terpadu.</p>
        </div>
        <div class="flex gap-2">
            @if($pendingCount > 0)<span class="badge badge-warning self-center">{{ $pendingCount }} menunggu</span>@endif
            <x-ui.button href="{{ route('admin.workflow.create') }}" icon="plus">Ajukan Permintaan</x-ui.button>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap items-center gap-2">
        @foreach(['' => 'Semua', 'submitted' => 'Diajukan', 'under_review' => 'Dalam Review', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $val => $label)
            <a href="{{ route('admin.workflow.index', ['status' => $val, 'type' => request('type')]) }}"
               class="px-3 py-1.5 text-sm rounded-full border transition {{ $currentStatus === $val ? 'text-white' : 'text-[var(--color-text-secondary)] hover:text-[var(--color-text)]' }}"
               style="{{ $currentStatus === $val ? 'background: var(--color-primary); border-color: var(--color-primary);' : 'border-color: var(--color-border);' }}">
                {{ $label }}
            </a>
        @endforeach
        <select name="type" onchange="this.form.submit()" class="select ml-auto" style="width: auto; min-width: 12rem;">
            <option value="">— Semua tipe —</option>
            @foreach($types as $key => $label)
                <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <input type="hidden" name="status" value="{{ $currentStatus }}">
    </form>

    <div class="card">
        @if($items->isEmpty())
            <div class="p-6"><x-feedback.empty-state icon="inbox" title="Belum ada permintaan" description="Tidak ada permintaan persetujuan untuk filter ini." :actionHref="route('admin.workflow.create')" action="Ajukan Permintaan" /></div>
        @else
            <div class="table-scroll">
                <table class="table-elite">
                    <thead>
                        <tr><th>Tipe</th><th>Judul</th><th>Pengaju</th><th>Status</th><th>Diajukan</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td class="text-xs">{{ $types[$item->type] ?? $item->type }}</td>
                                <td class="font-medium">{{ $item->title }}</td>
                                <td>{{ $item->requester?->name ?? '—' }}</td>
                                <td><x-ui.badge :variant="$statusTone($item->status)">{{ $statuses[$item->status] ?? $item->status }}</x-ui.badge></td>
                                <td class="text-xs text-[var(--color-text-muted)]">{{ $item->submitted_at?->format('d M Y') }}</td>
                                <td class="text-right"><a href="{{ route('admin.workflow.show', $item) }}" class="text-sm text-[var(--color-primary)] hover:underline">Detail</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4">{{ $items->links() }}</div>
        @endif
    </div>

</div>
@endsection

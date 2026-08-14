@extends('layouts.school-admin')
@section('title', 'Detail Permintaan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

@php
    $statusTone = fn ($st) => match ($st) { 'approved' => 'success', 'rejected' => 'danger', 'under_review' => 'info', 'submitted' => 'warning', default => 'default' };
    $statusLabel = \App\Models\Workflow\WorkflowRequest::STATUSES[$item->status] ?? $item->status;
    $isOpen = in_array($item->status, ['submitted', 'under_review'], true);
@endphp

<div class="max-w-3xl space-y-6">
    <div class="flex items-start justify-between gap-3">
        <div>
            <a href="{{ route('admin.workflow.index') }}" class="text-sm text-[var(--color-text-muted)] hover:text-[var(--color-primary)]">← Kembali</a>
            <h1 class="page-title mt-1">{{ $item->title }}</h1>
        </div>
        <x-ui.badge :variant="$statusTone($item->status)">{{ $statusLabel }}</x-ui.badge>
    </div>

    <div class="card card-pad">
        <div class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <div>
                <div class="text-xs uppercase tracking-wide text-[var(--color-text-muted)]">Tipe</div>
                <div class="font-medium mt-0.5">{{ $types[$item->type] ?? $item->type }}</div>
            </div>
            <div>
                <div class="text-xs uppercase tracking-wide text-[var(--color-text-muted)]">Pengaju</div>
                <div class="font-medium mt-0.5">{{ $item->requester?->name ?? '—' }}</div>
            </div>
            <div>
                <div class="text-xs uppercase tracking-wide text-[var(--color-text-muted)]">Diajukan</div>
                <div class="font-medium mt-0.5">{{ $item->submitted_at?->format('d M Y H:i') ?? '—' }}</div>
            </div>
            <div>
                <div class="text-xs uppercase tracking-wide text-[var(--color-text-muted)]">Diputuskan</div>
                <div class="font-medium mt-0.5">{{ $item->decided_at?->format('d M Y H:i') ?? '—' }}</div>
            </div>
            @if($item->approver_id)
                <div>
                    <div class="text-xs uppercase tracking-wide text-[var(--color-text-muted)]">Pemberi persetujuan</div>
                    <div class="font-medium mt-0.5">{{ $item->approver?->name ?? '—' }}</div>
                </div>
            @endif
        </div>

        @if($item->description)
            <div class="mt-5 pt-5 border-t border-[var(--color-border)]">
                <div class="text-xs uppercase tracking-wide text-[var(--color-text-muted)] mb-1">Deskripsi</div>
                <p class="text-sm leading-relaxed whitespace-pre-line">{{ $item->description }}</p>
            </div>
        @endif

        @if($item->decision_note)
            <div class="mt-5 p-4 rounded-lg" style="background: var(--color-surface-hover);">
                <div class="text-xs uppercase tracking-wide text-[var(--color-text-muted)] mb-1">Catatan Keputusan</div>
                <p class="text-sm whitespace-pre-line">{{ $item->decision_note }}</p>
            </div>
        @endif
    </div>

    @if($isOpen)
        <div class="grid md:grid-cols-2 gap-4">
            <div class="card card-pad">
                <h3 class="font-semibold mb-3" style="color: var(--color-success);">Setujui</h3>
                <form method="POST" action="{{ route('admin.workflow.approve', $item) }}" class="space-y-3">
                    @csrf
                    <x-ui.textarea name="note" label="Catatan (opsional)" rows="2" />
                    <x-ui.button type="submit" variant="success" class="w-full">Setujui</x-ui.button>
                </form>
            </div>
            <div class="card card-pad">
                <h3 class="font-semibold mb-3" style="color: var(--color-danger);">Tolak</h3>
                <form method="POST" action="{{ route('admin.workflow.reject', $item) }}" class="space-y-3">
                    @csrf
                    <x-ui.textarea name="note" label="Alasan penolakan" :required="true" :error="$errors->first('note')" rows="2" />
                    <x-ui.button type="submit" variant="danger" class="w-full">Tolak</x-ui.button>
                </form>
            </div>
        </div>
    @endif
</div>

@endsection

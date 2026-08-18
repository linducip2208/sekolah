@extends('layouts.school-admin')
@section('title', 'Verifikasi Tanda Tangan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="space-y-6 max-w-2xl mx-auto">
    <div>
        <div class="text-sm text-[var(--color-text-muted)]">Digital Signature</div>
        <h1 class="page-title mt-1">Verifikasi Tanda Tangan</h1>
    </div>

    <div class="card card-pad">
        @if($isValid)
            <div class="flex items-center gap-3 p-4 rounded-lg mb-4" style="background: var(--color-success-soft);">
                <svg class="w-6 h-6 text-[var(--color-success)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-semibold text-[var(--color-success)]">Dokumen Tervalidasi — Integritas Terjamin</span>
            </div>
        @else
            <div class="flex items-center gap-3 p-4 rounded-lg mb-4" style="background: var(--color-danger-soft);">
                <svg class="w-6 h-6 text-[var(--color-danger)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-semibold text-[var(--color-danger)]">TIDAK VALID — Integritas Dokumen Terancam</span>
            </div>
        @endif

        <div class="space-y-3 text-sm">
            <div class="flex justify-between py-2 border-b border-[var(--color-border)]">
                <span class="text-[var(--color-text-muted)]">Tipe Dokumen</span>
                <span class="font-semibold capitalize">{{ str_replace('_', ' ', $signed->document_type) }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-[var(--color-border)]">
                <span class="text-[var(--color-text-muted)]">ID Dokumen</span>
                <span class="font-semibold">#{{ $signed->document_id }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-[var(--color-border)]">
                <span class="text-[var(--color-text-muted)]">Ditandatangani oleh</span>
                <span class="font-semibold">{{ $signed->signature?->user?->name ?? '-' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-[var(--color-border)]">
                <span class="text-[var(--color-text-muted)]">Waktu</span>
                <span>{{ $signed->signed_at?->format('d M Y H:i:s') }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-[var(--color-border)]">
                <span class="text-[var(--color-text-muted)]">IP Address</span>
                <span>{{ $signed->ip_address ?? '-' }}</span>
            </div>
            <div class="flex justify-between py-2">
                <span class="text-[var(--color-text-muted)]">Hash</span>
                <span class="font-mono text-xs break-all text-right max-w-xs">{{ $signed->hash_value }}</span>
            </div>
        </div>
    </div>
</div>
@endsection

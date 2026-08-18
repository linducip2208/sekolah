@extends('layouts.school-admin')
@section('title', 'Verifikasi Rapor QR')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
            <div class="text-sm text-[var(--color-text-muted)]">Academic</div>
            <h1 class="page-title mt-1">QR Rapor — Admin Panel</h1>
            <p class="text-sm text-[var(--color-text-secondary)] mt-1">Generate QR token untuk verifikasi rapor oleh orang tua.</p>
        </div>
        <a href="{{ route('admin.raport-interaktif.index') }}" class="btn-elite">Kembali ke Report Cards</a>
    </div>
    <div class="card card-pad">
        <p class="text-sm text-[var(--color-text-secondary)]">Buka halaman Rapor Interaktif, lalu klik tombol "Generate QR" pada rapor yang ingin diverifikasi. Token QR akan ditampilkan untuk dibagikan ke orang tua.</p>
    </div>
</div>
@endsection

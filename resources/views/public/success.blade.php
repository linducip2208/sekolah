@extends('landing.layout')
@section('title', 'Pendaftaran Berhasil')
@section('description', 'Pendaftaran sekolah Anda telah diterima dan sedang diverifikasi.')

@section('content')
<section class="lp-section" style="background: var(--lp-background);">
    <div class="lp-container max-w-2xl text-center">
        <div class="h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-6" style="background: var(--lp-accent-soft); color: var(--lp-primary);">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h1 class="lp-title text-4xl sm:text-5xl">Pendaftaran Diterima!</h1>

        <div class="lp-card lp-card-shadow p-6 text-left mt-8">
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <dt style="color: var(--lp-muted);">Sekolah</dt>
                <dd class="font-semibold text-right" style="color: var(--lp-ink);">{{ $registration->school_name }}</dd>
                <dt style="color: var(--lp-muted);">Subdomain</dt>
                <dd class="font-mono text-right" style="color: var(--lp-ink);">{{ $registration->subdomain }}</dd>
                <dt style="color: var(--lp-muted);">Email Admin</dt>
                <dd class="text-right" style="color: var(--lp-ink);">{{ $registration->admin_email }}</dd>
                <dt style="color: var(--lp-muted);">Status</dt>
                <dd class="text-right"><span class="badge badge-accent">{{ strtoupper($registration->status) }}</span></dd>
            </dl>
        </div>

        <p class="lp-lead mt-6">
            Bukti pembayaran Anda sedang diverifikasi tim platform. Verifikasi paling lama <strong>1 × 24 jam</strong>.
            Kami akan kirim email notifikasi ke <strong>{{ $registration->admin_email }}</strong> begitu sekolah Anda aktif beserta password admin pertama.
        </p>

        <div class="flex flex-wrap gap-3 justify-center mt-8">
            <a href="{{ route('home') }}" class="lp-btn lp-btn-secondary">Kembali ke Beranda</a>
            <a href="/docs" class="lp-btn">Buka Buku Panduan</a>
        </div>

        <div class="mt-12 text-sm" style="color: var(--lp-muted);">
            ID Pendaftaran: <code class="font-mono">REG-{{ str_pad($registration->id, 6, '0', STR_PAD_LEFT) }}</code>
        </div>
    </div>
</section>
@endsection

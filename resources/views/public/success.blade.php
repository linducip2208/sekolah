@extends('elite.layout')
@section('title', 'Pendaftaran Berhasil')

@section('header')@include('elite.partials.header')@endsection

@section('content')

<section class="paper">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-20 text-center">
        <div class="ornament-center"></div>
        <div class="elite-kicker mb-3" style="color: var(--c-accent);">Inscriptio Recepta</div>
        <h1 class="elite-h1 text-4xl ink-primary mb-5">Pendaftaran Diterima!</h1>
        <div class="elite-rule mx-auto mb-7"></div>

        <div class="bg-white border border-rule p-7 text-left mb-7">
            <div class="grid grid-cols-2 gap-3 text-sm font-serif">
                <div class="text-gray-500">Sekolah</div>
                <div class="font-semibold ink-primary text-right">{{ $registration->school_name }}</div>
                <div class="text-gray-500">Subdomain</div>
                <div class="font-mono text-right">{{ $registration->subdomain }}</div>
                <div class="text-gray-500">Email Admin</div>
                <div class="text-right">{{ $registration->admin_email }}</div>
                <div class="text-gray-500">Status</div>
                <div class="text-right">
                    <span class="elite-kicker text-[.6rem] px-2 py-1" style="background: var(--c-accent); color: var(--c-primary);">{{ strtoupper($registration->status) }}</span>
                </div>
            </div>
        </div>

        <p class="font-serif text-lg text-gray-700 leading-relaxed mb-7">
            Bukti pembayaran Anda sedang diverifikasi tim platform.
            Verifikasi paling lama <strong>1 × 24 jam</strong>. Kami akan kirim email notifikasi
            ke <strong>{{ $registration->admin_email }}</strong> begitu sekolah Anda aktif beserta password admin pertama.
        </p>

        <div class="flex gap-3 justify-center">
            <a href="/" class="btn-elite-ghost">Kembali ke Beranda</a>
            <a href="/docs" class="btn-elite">Buka Buku Panduan</a>
        </div>

        <div class="mt-12 elite-kicker" style="color: var(--c-muted);">
            ID Pendaftaran: <code class="font-mono">REG-{{ str_pad($registration->id, 6, '0', STR_PAD_LEFT) }}</code>
        </div>
    </div>
</section>

@endsection

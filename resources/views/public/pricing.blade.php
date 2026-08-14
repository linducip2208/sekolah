@extends('landing.layout')
@section('title', 'Berlangganan — Pilih Paket')
@section('description', 'Pilih paket berlangganan Sikad Pro sesuai kebutuhan sekolah Anda.')

@section('content')
<section class="lp-section" style="background: var(--lp-bg);">
    <div class="lp-container">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <p class="lp-kicker mb-3">Berlangganan</p>
            <h1 class="lp-title text-4xl sm:text-5xl">Pilih Paket Sekolah Anda</h1>
            <p class="lp-lead mt-4">Mulai dari paket gratis hingga enterprise — pilih sesuai jumlah siswa, fitur, dan kebutuhan operasional sekolah.</p>
        </div>

        <x-landing.pricing :theme="$theme" :plans="$plans" :heading="false" />
    </div>
</section>

<section class="lp-section" style="background: var(--lp-surface);">
    <div class="lp-container max-w-3xl text-center">
        <h2 class="lp-title text-2xl sm:text-3xl">Butuh konsultasi?</h2>
        <p class="lp-lead mt-3">Tim kami siap membantu memilih paket yang tepat dan migrasi data dari sistem lama Anda.</p>
        @if(!empty($platform['whatsapp_link']))
            <a href="{{ $platform['whatsapp_link'] }}" target="_blank" rel="noopener" class="lp-btn lp-btn-accent mt-6">Konsultasi via WhatsApp</a>
        @endif
    </div>
</section>
@endsection

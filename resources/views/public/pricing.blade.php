@extends('elite.layout')
@section('title', 'Berlangganan — Pilih Paket')
@section('description', 'Pilih paket berlangganan Sikad Pro sesuai kebutuhan sekolah Anda.')

@section('header')@include('elite.partials.header')@endsection

@section('content')

<section class="paper">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-20 text-center">
        <div class="ornament-center"></div>
        <div class="elite-kicker mb-3">Pretia & Subscriptiones</div>
        <h1 class="elite-h1 text-5xl ink-primary mb-5">Pilih Paket Sekolah Anda</h1>
        <div class="elite-rule mx-auto mb-7"></div>
        <p class="elite-lead max-w-2xl mx-auto">Mulai dari paket gratis hingga enterprise — pilih sesuai jumlah siswa, fitur, dan kebutuhan operasional sekolah.</p>
    </div>
</section>

<section class="paper border-t border-rule">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
        <div class="grid md:grid-cols-3 gap-6">
            @foreach($plans as $idx => $plan)
                @php
                    $isPopular = $plan->slug === 'basic';
                    $monthly = number_format($plan->price / 100, 0, ',', '.');
                    $features = $plan->features ?? [];
                @endphp
                <div class="elite-card p-8 flex flex-col {{ $isPopular ? 'border-2' : '' }}" @if($isPopular) style="border-color: var(--c-accent);" @endif>
                    @if($isPopular)
                        <div class="absolute top-0 right-7 -translate-y-1/2 px-3 py-1 elite-kicker text-[.6rem]" style="background: var(--c-accent); color: var(--c-primary);">Paling Populer</div>
                    @endif

                    <div class="elite-kicker mb-2">Capitulum {{ ['I','II','III'][$idx] ?? $idx+1 }}</div>
                    <h3 class="elite-h2 text-3xl ink-primary mb-3">{{ $plan->name }}</h3>
                    <div class="elite-rule mb-5"></div>

                    <div class="mb-6">
                        @if($plan->price === 0)
                            <span class="font-display text-5xl ink-primary">Gratis</span>
                            <div class="text-sm text-gray-500 mt-1">selamanya</div>
                        @else
                            <div class="flex items-baseline gap-2">
                                <span class="font-display text-5xl ink-primary">Rp{{ $monthly }}</span>
                                <span class="text-sm text-gray-500">/bulan</span>
                            </div>
                            <div class="text-xs text-gray-400 mt-1">Bisa berlangganan 1, 3, 6, atau 12 bulan</div>
                        @endif
                    </div>

                    <ul class="space-y-3 mb-7 flex-grow font-serif text-base text-gray-700">
                        <li class="flex gap-2">
                            <span class="ink-accent">❦</span>
                            Maks {{ $plan->max_students ?: 'tak terbatas' }} siswa
                        </li>
                        <li class="flex gap-2">
                            <span class="ink-accent">❦</span>
                            Maks {{ $plan->max_teachers ?: 'tak terbatas' }} guru
                        </li>
                        @foreach($features as $feature)
                            <li class="flex gap-2">
                                <span class="ink-accent">❦</span>
                                @if($feature === '*')
                                    Semua fitur platform
                                @else
                                    {{ ucfirst($feature) }}
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    <a href="{{ route('public.subscription.register', ['plan' => $plan->slug]) }}"
                       class="{{ $isPopular ? 'btn-elite-gold' : 'btn-elite' }} text-center block">
                        {{ $plan->price === 0 ? 'Mulai Gratis' : 'Daftar Sekarang' }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="bg-[var(--c-primary)] text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-16 text-center">
        <div class="ornament-center" style="color: var(--c-accent);"></div>
        <h2 class="elite-h2 text-3xl text-white mb-3">Butuh Konsultasi?</h2>
        <div class="elite-rule mx-auto mb-5" style="color: var(--c-accent);"></div>
        <p class="font-serif text-lg text-white/85 mb-7">Tim kami siap membantu memilih paket yang tepat dan migrasi data dari sistem lama Anda.</p>
        @if(!empty($platform['whatsapp_link']))
            <a href="{{ $platform['whatsapp_link'] }}" target="_blank" class="btn-elite-gold">Konsultasi via WhatsApp</a>
        @endif
    </div>
</section>

@endsection

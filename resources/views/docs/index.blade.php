@extends('elite.layout')

@section('title', 'Panduan Penggunaan')
@section('description', 'Buku panduan resmi ' . ($platform['app_name'] ?? 'Sikad Pro') . ' — disusun untuk setiap peran: administrator, orang tua, guru, siswa, dan operator platform.')

@section('header')
@include('elite.partials.header')
@endsection

@section('content')

<section class="paper">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-20 lg:py-28 text-center">
        <div class="ornament-center"></div>
        <div class="elite-kicker mb-3">Liber Manualis</div>
        <h1 class="elite-h1 text-5xl sm:text-6xl ink-primary mb-6">Buku Panduan Resmi</h1>
        <div class="elite-rule mx-auto mb-7"></div>
        <p class="elite-lead max-w-2xl mx-auto">
            Setiap peran di institusi memiliki bab tersendiri. Pilih bab Anda untuk mempelajari cara kerja sistem dari fondasi hingga penggunaan harian.
        </p>
    </div>
</section>

<section class="paper border-t border-rule">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @php $romans = ['I','II','III','IV','V','VI','VII','VIII','IX','X']; @endphp
            @foreach($roles as $i => $r)
                <a href="{{ url('/docs/' . $r['slug']) }}" class="elite-card p-8 group flex flex-col">
                    <div class="flex items-baseline justify-between mb-4">
                        <span class="font-display text-3xl ink-accent">{{ $romans[$i] ?? ($i + 1) }}</span>
                        <span class="elite-kicker" style="color: var(--c-muted);">Capitulum</span>
                    </div>
                    <div class="elite-kicker mb-2">{{ $r['kicker'] }}</div>
                    <h3 class="elite-h3 text-2xl ink-primary mb-3">{!! $r['title'] !!}</h3>
                    <div class="w-10 h-px bg-[var(--c-accent)] mb-3 group-hover:w-20 transition-all"></div>
                    <p class="font-serif text-base leading-relaxed text-gray-700 flex-grow">{{ $r['lead'] }}</p>
                    <div class="mt-5 flex items-center text-sm uppercase font-semibold ink-primary group-hover:ink-accent transition" style="letter-spacing:.18em;font-size:.7rem;">
                        Baca Bab
                        <span class="ml-2 transition group-hover:translate-x-1">→</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="bg-[var(--c-primary)] text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-20 text-center">
        <div class="ornament-center"></div>
        <div class="elite-kicker mb-3" style="color: var(--c-accent);">Auxilium</div>
        <h2 class="elite-h2 text-4xl text-white mb-5">Memerlukan Bantuan Lebih Lanjut?</h2>
        <div class="elite-rule mx-auto mb-7" style="color: var(--c-accent);"></div>
        <p class="font-serif text-xl leading-relaxed text-white/85 max-w-xl mx-auto mb-10">
            Tim kami siap memandu instalasi, migrasi data, dan pelatihan staf — secara langsung maupun jarak jauh.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            @if(!empty($platform['contact_phone']))
                <a href="tel:{{ $platform['contact_phone'] }}" class="btn-elite-gold">Telepon: {{ $platform['contact_phone'] }}</a>
            @endif
            @if(!empty($platform['whatsapp_link']))
                <a href="{{ $platform['whatsapp_link'] }}" target="_blank" rel="noopener" class="btn-elite-ghost" style="border-color: var(--c-accent); color: var(--c-accent);">WhatsApp</a>
            @endif
        </div>
    </div>
</section>

@endsection

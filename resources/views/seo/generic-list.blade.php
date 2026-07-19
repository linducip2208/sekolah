@extends('seo.layout')

@push('jsonld')
@if(!empty($faq))
@php
    $__faqJsonLd = json_encode([
        '@' . 'context' => 'https://schema.org',
        '@' . 'type'    => 'FAQPage',
        'mainEntity'    => collect($faq)->map(fn($f) => [
            '@' . 'type'     => 'Question',
            'name'           => $f[0],
            'acceptedAnswer' => ['@' . 'type' => 'Answer', 'text' => $f[1]],
        ])->toArray(),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<script type="application/ld+json">{!! $__faqJsonLd !!}</script>
@endif
@endpush

@section('seo_content')
<section class="max-w-5xl mx-auto px-4 sm:px-6 py-16 lg:py-20">
    <nav class="elite-kicker mb-6" style="color: var(--c-muted);">
        <a href="/" class="hover:ink-primary">Beranda</a>
        <span class="mx-2 ink-accent">/</span>
        <span>{{ $pageKicker ?? 'Direktori' }}</span>
    </nav>

    <header class="mb-10 pb-8 border-b border-rule">
        <div class="elite-kicker mb-3">{{ $pageKicker ?? '' }}</div>
        <h1 class="elite-h1 text-4xl sm:text-5xl ink-primary mb-5 leading-tight">{{ $pageTitle }}</h1>
        <div class="elite-rule mb-5"></div>
        <p class="elite-lead">{{ $pageLead }}</p>
    </header>

    @if(!empty($items))
        <div class="grid sm:grid-cols-2 gap-5 mb-14">
            @foreach($items as $idx => $item)
                <a @if(!empty($item['url']))href="{{ $item['url'] }}"@endif
                   class="elite-card p-6 group block">
                    <div class="flex items-baseline justify-between mb-2">
                        <span class="font-display text-2xl ink-accent">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        @if(!empty($item['meta']))
                            <span class="elite-kicker" style="color: var(--c-muted);">{{ $item['meta'] }}</span>
                        @endif
                    </div>
                    <h2 class="elite-h3 text-xl ink-primary mb-1">{{ $item['title'] }}</h2>
                    @if(!empty($item['desc']))
                        <p class="font-serif text-base text-gray-700 leading-relaxed">{{ $item['desc'] }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    @else
        <div class="elite-card p-10 mb-14 text-center">
            <div class="ornament-center"></div>
            <p class="font-serif text-xl text-gray-700 leading-relaxed">Belum ada institusi terdaftar untuk kategori ini. <a href="{{ route('admin.login') }}" class="ink-secondary underline">Daftarkan sekolah Anda</a> untuk tampil di sini.</p>
        </div>
    @endif

    @if(!empty($narrative))
        <article class="prose prose-lg max-w-none mb-14 font-serif text-lg leading-relaxed text-gray-800">
            <h2 class="elite-h2 text-3xl ink-primary mb-6 mt-2">Tentang Kategori Ini</h2>
            <div class="elite-rule mb-6"></div>
            {!! $narrative !!}
        </article>
    @endif

    @if(!empty($faq))
        <section class="border-t border-rule pt-10">
            <h2 class="elite-h2 text-3xl ink-primary mb-6">Pertanyaan yang Sering Diajukan</h2>
            <div class="elite-rule mb-7"></div>
            <div class="space-y-3">
                @foreach($faq as [$q, $a])
                    <details class="bg-white border border-rule group">
                        <summary class="flex items-center justify-between cursor-pointer py-4 px-5 list-none">
                            <span class="elite-h3 text-lg ink-primary">{{ $q }}</span>
                            <span class="ink-accent text-2xl leading-none transition group-open:rotate-45" style="color: var(--c-accent);">+</span>
                        </summary>
                        <div class="px-5 pb-5 font-serif text-lg leading-relaxed text-gray-700">{{ $a }}</div>
                    </details>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mt-16 deco-frame">
        <div class="bg-[var(--c-primary)] text-white p-10 text-center">
            <div class="elite-kicker mb-3" style="color: var(--c-accent);">Apakah Anda Pemilik Sekolah?</div>
            <h3 class="elite-h2 text-3xl text-white mb-3">Daftarkan Institusi Anda di Direktori Ini</h3>
            <p class="font-serif text-lg text-white/80 max-w-xl mx-auto mb-6">Tingkatkan visibilitas — sekolah Anda akan muncul di pencarian Google untuk calon orang tua di area ini.</p>
            <a href="{{ route('admin.login') }}" class="btn-elite-gold">Mulai Pendaftaran</a>
        </div>
    </section>
</section>
@endsection

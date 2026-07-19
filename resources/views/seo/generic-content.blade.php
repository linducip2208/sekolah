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
<section class="max-w-4xl mx-auto px-4 sm:px-6 py-16 lg:py-20">
    <nav class="elite-kicker mb-6" style="color: var(--c-muted);">
        <a href="/" class="hover:ink-primary">Beranda</a>
        <span class="mx-2 ink-accent">/</span>
        <span>{{ $pageKicker ?? 'Artikel' }}</span>
    </nav>

    <header class="mb-12 pb-8 border-b border-rule text-center">
        <div class="ornament-center"></div>
        <div class="elite-kicker mb-3">{{ $pageKicker ?? '' }}</div>
        <h1 class="elite-h1 text-4xl sm:text-5xl ink-primary mb-5 leading-tight">{{ $pageTitle }}</h1>
        <div class="elite-rule mx-auto mb-5"></div>
        <p class="elite-lead max-w-2xl mx-auto">{{ $pageLead }}</p>
    </header>

    @if(!empty($narrative))
        <article class="prose prose-lg max-w-none mb-14 font-serif text-lg leading-relaxed text-gray-800">
            {!! $narrative !!}
        </article>
    @endif

    @if(!empty($faq))
        <section class="border-t border-rule pt-10 mb-14">
            <h2 class="elite-h2 text-3xl ink-primary mb-6 text-center">Pertanyaan Lazim</h2>
            <div class="elite-rule mx-auto mb-7"></div>
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
            <div class="elite-kicker mb-3" style="color: var(--c-accent);">Cari Sekolah?</div>
            <h3 class="elite-h2 text-3xl text-white mb-3">Temukan Sekolah Terbaik untuk Anak Anda</h3>
            <p class="font-serif text-lg text-white/80 max-w-xl mx-auto mb-6">Bandingkan ratusan sekolah dengan filter akreditasi, biaya, kurikulum, dan lokasi.</p>
            <a href="/" class="btn-elite-gold">Mulai Pencarian</a>
        </div>
    </section>
</section>
@endsection

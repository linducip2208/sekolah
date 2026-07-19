@extends('elite.layout')

@php $p = $platform ?? app(\App\Services\PlatformSettingsService::class)->all(); @endphp

@section('title', $meta['title'] ?? $post->title)
@section('description', $meta['description'] ?? $post->excerpt)

@push('jsonld')
<script type="application/ld+json">
{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('header')
@include('elite.partials.header')
@endsection

@section('content')

<div class="max-w-7xl mx-auto px-3 sm:px-5 lg:px-6 py-10 sm:py-16 lg:py-20">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">

        <article class="lg:col-span-8">

            <nav class="mb-6 text-xs" style="font-family:'Inter',sans-serif;">
                <a href="/" class="text-gray-500 hover:ink-accent transition">Beranda</a>
                <span class="mx-2 text-gray-400">/</span>
                <a href="{{ route('blog.index') }}" class="text-gray-500 hover:ink-accent transition">Blog</a>
                @if($post->category)
                    <span class="mx-2 text-gray-400">/</span>
                    <a href="{{ route('blog.category', $post->category->slug) }}" class="text-gray-500 hover:ink-accent transition">{{ $post->category->name }}</a>
                @endif
                <span class="mx-2 text-gray-400">/</span>
                <span class="ink-primary font-semibold truncate">{{ Str::limit($post->title, 50) }}</span>
            </nav>

            <div class="mb-8 sm:mb-10">
                @if($post->category)
                    <a href="{{ route('blog.category', $post->category->slug) }}"
                       class="elite-kicker text-[.6rem] inline-block mb-3 hover:underline"
                       style="color: var(--c-accent);">{{ $post->category->name }}</a>
                @endif

                <h1 class="elite-h1 text-3xl sm:text-4xl lg:text-5xl ink-primary leading-tight mb-4">{{ $post->title }}</h1>

                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 mb-6" style="font-family:'Inter',sans-serif;">
                    @if($post->author)
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            {{ $post->author->name }}
                        </span>
                    @endif
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ $post->published_at?->format('d M Y') ?? '' }}
                    </span>
                </div>

                @if($post->excerpt)
                    <p class="elite-lead text-lg sm:text-xl mb-6">{{ $post->excerpt }}</p>
                @endif
                <div class="elite-rule mb-6"></div>
            </div>

            @if($post->featured_image)
                <div class="mb-8 sm:mb-10">
                    <img src="{{ asset($post->featured_image) }}"
                         alt="{{ $post->title }}"
                         class="w-full object-cover border border-rule"
                         style="max-height: 480px;"
                         loading="lazy">
                </div>
            @endif

            <div class="prose prose-base sm:prose-lg max-w-none font-serif leading-relaxed text-gray-800
                        prose-headings:font-display prose-headings:ink-primary prose-headings:mt-8 prose-headings:mb-4
                        prose-h2:text-2xl sm:prose-h2:text-3xl
                        prose-h3:text-xl sm:prose-h3:text-2xl
                        prose-p:mb-4 prose-p:leading-7
                        prose-a:text-[var(--c-accent)] prose-a:underline
                        prose-strong:font-semibold prose-strong:ink-primary
                        prose-ul:list-disc prose-ol:list-decimal prose-li:mb-1
                        prose-blockquote:border-l-4 prose-blockquote:border-[var(--c-accent)] prose-blockquote:pl-4 prose-blockquote:italic prose-blockquote:bg-white prose-blockquote:py-2 prose-blockquote:px-4
                        prose-img:border prose-img:border-rule prose-img:shadow-sm
                        [&_table]:table-elite [&_table]:w-full [&_table]:mb-6
                        [&_table_th]:font-mono [&_table_td]:font-serif [&_table_td]:text-base">
                {!! $post->content !!}
            </div>

            <div class="mt-8 sm:mt-10 pt-6 border-t border-rule flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-500 uppercase tracking-widest" style="font-family:'Inter',sans-serif;">Bagikan:</span>
                    @php
                        $shareUrl = urlencode(route('blog.show', $post->slug));
                        $shareTitle = urlencode($post->title);
                    @endphp
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" class="w-8 h-8 flex items-center justify-center border border-rule rounded-full hover:bg-[var(--c-accent)] hover:text-white hover:border-[var(--c-accent)] transition text-xs">FB</a>
                    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}" target="_blank" rel="noopener" class="w-8 h-8 flex items-center justify-center border border-rule rounded-full hover:bg-[var(--c-accent)] hover:text-white hover:border-[var(--c-accent)] transition text-xs">TW</a>
                    <a href="https://wa.me/?text={{ $shareTitle }}%20{{ $shareUrl }}" target="_blank" rel="noopener" class="w-8 h-8 flex items-center justify-center border border-rule rounded-full hover:bg-[var(--c-accent)] hover:text-white hover:border-[var(--c-accent)] transition text-xs">WA</a>
                    <a href="https://t.me/share/url?url={{ $shareUrl }}&text={{ $shareTitle }}" target="_blank" rel="noopener" class="w-8 h-8 flex items-center justify-center border border-rule rounded-full hover:bg-[var(--c-accent)] hover:text-white hover:border-[var(--c-accent)] transition text-xs">TG</a>
                </div>
                @if($post->category)
                    <a href="{{ route('blog.category', $post->category->slug) }}"
                       class="elite-kicker text-[.6rem] hover:underline"
                       style="color: var(--c-accent);">← Kembali ke {{ $post->category->name }}</a>
                @endif
            </div>

            @if($relatedPosts->isNotEmpty())
                <div class="mt-10 sm:mt-12">
                    <div class="elite-rule mb-6"></div>
                    <h2 class="elite-h2 text-2xl ink-primary mb-6">Artikel Terkait</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        @foreach($relatedPosts as $related)
                            <article class="elite-card group">
                                <a href="{{ route('blog.show', $related->slug) }}" class="block">
                                    @if($related->featured_image)
                                        <div class="aspect-[16/10] overflow-hidden">
                                            <img src="{{ asset($related->featured_image) }}"
                                                 alt="{{ $related->title }}"
                                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                                 loading="lazy">
                                        </div>
                                    @else
                                        <div class="aspect-[16/10] bg-primary flex items-center justify-center" style="background: var(--c-primary); opacity:.7;"></div>
                                    @endif
                                </a>
                                <div class="p-4">
                                    <a href="{{ route('blog.show', $related->slug) }}" class="block group-hover:opacity-80 transition">
                                        <h3 class="elite-h3 text-base ink-primary leading-tight">{{ $related->title }}</h3>
                                    </a>
                                    <span class="text-xs text-gray-500 mt-2 block">{{ $related->published_at?->format('d M Y') }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif

        </article>

        <aside class="lg:col-span-4 space-y-8">
            <div class="bg-white border border-rule p-5 sm:p-6">
                <h3 class="elite-kicker mb-4" style="color: var(--c-accent);">Cari Artikel</h3>
                <form action="{{ route('blog.index') }}" method="GET" class="flex gap-2">
                    <input type="text" name="search" placeholder="Ketik kata kunci..."
                           class="flex-1 px-4 py-2.5 border border-rule text-sm font-serif focus:outline-none"
                           style="border-color: var(--c-rule);">
                    <button type="submit" class="btn-elite-gold" style="padding:.6rem 1rem;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </form>
            </div>

            <div class="bg-white border border-rule p-5 sm:p-6">
                <h3 class="elite-kicker mb-4" style="color: var(--c-accent);">Kategori</h3>
                <ul class="space-y-3 font-serif text-base">
                    @foreach($categories as $cat)
                        <li>
                            <a href="{{ route('blog.category', $cat->slug) }}"
                               class="flex justify-between items-center py-1.5 hover:ink-accent transition {{ request()->is('blog/category/'.$cat->slug) ? 'ink-accent font-semibold' : 'ink-primary' }}">
                                <span>{{ $cat->name }}</span>
                                <span class="text-xs text-gray-400 font-mono">{{ $cat->posts_count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="bg-white border border-rule p-5 sm:p-6">
                <h3 class="elite-kicker mb-4" style="color: var(--c-accent);">Artikel Terbaru</h3>
                <ul class="space-y-4">
                    @foreach($recentPosts as $recent)
                        <li>
                            <a href="{{ route('blog.show', $recent->slug) }}" class="block group">
                                <h4 class="font-serif font-semibold text-base ink-primary leading-snug group-hover:ink-accent transition">{{ $recent->title }}</h4>
                                <span class="text-xs text-gray-500 mt-1 block" style="font-family:'Inter',sans-serif;">{{ $recent->published_at?->format('d M Y') }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="deco-frame" style="background: linear-gradient(135deg, var(--c-primary), var(--c-secondary)); color: white;">
                <div class="text-center">
                    <div class="text-3xl mb-2" style="color: var(--c-accent);">🛡️</div>
                    <h3 class="font-display text-xl mb-2" style="font-weight:600;">Punya Sekolah Sendiri?</h3>
                    <p class="font-serif text-base text-white/80 mb-4 leading-relaxed">Jalankan sistem ERP sekolah modern yang sama — lengkap dengan brand sendiri, domain sendiri, dan database sendiri.</p>
                    <a href="{{ route('public.pricing') }}" class="btn-elite-gold block text-center" style="background: var(--c-accent); border-color: var(--c-accent);">Lihat Paket Source Code</a>
                </div>
            </div>
        </aside>

    </div>
</div>

@endsection

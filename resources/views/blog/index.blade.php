@extends('elite.layout')

@php $p = $platform ?? app(\App\Services\PlatformSettingsService::class)->all(); @endphp

@section('title', $meta['title'] ?? 'Blog — Informasi & Wawasan Pendidikan')
@section('description', $meta['description'] ?? 'Artikel terbaru seputar pendidikan, manajemen sekolah, tips mengajar, teknologi pendidikan, dan wawasan untuk kepala sekolah, guru, dan orang tua.')

@push('jsonld')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'Blog',
    'name'     => 'Blog ' . ($p['app_name'] ?? 'Sikad Pro'),
    'url'      => route('blog.index'),
    'description' => $meta['description'] ?? 'Artikel terbaru seputar pendidikan.',
    'blogPost' => $posts->map(fn($post) => [
        '@type' => 'BlogPosting',
        'headline' => $post->title,
        'url'      => route('blog.show', $post->slug),
        'datePublished' => $post->published_at?->toIso8601String(),
        'author' => ['@type' => 'Person', 'name' => $post->author?->name ?? 'Sikad Pro'],
    ])->toArray(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('header')
@include('elite.partials.header')
@endsection

@section('content')

<div class="max-w-7xl mx-auto px-3 sm:px-5 lg:px-6 py-10 sm:py-16 lg:py-20">

    <div class="text-center mb-12 sm:mb-16">
        <div class="ornament-center"></div>
        <div class="elite-kicker mb-3">Schola Scripta</div>
        <h1 class="elite-h1 text-4xl sm:text-5xl lg:text-6xl ink-primary mb-4">Blog</h1>
        <p class="elite-lead max-w-2xl mx-auto">Wawasan & inspirasi untuk memajukan pendidikan Indonesia. Artikel untuk kepala sekolah, guru, staf TU, dan orang tua.</p>
        <div class="elite-rule mt-4"></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">

        <div class="lg:col-span-8">
            @if($posts->isEmpty())
                <div class="bg-white border border-rule p-10 text-center font-serif italic text-gray-500">
                    Belum ada artikel. Kunjungi lagi nanti.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    @foreach($posts as $post)
                        <article class="elite-card group">
                            <a href="{{ route('blog.show', $post->slug) }}" class="block">
                                @if($post->featured_image)
                                    <div class="aspect-[16/10] overflow-hidden">
                                        <img src="{{ asset($post->featured_image) }}"
                                             alt="{{ $post->title }}"
                                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                             loading="lazy">
                                    </div>
                                @else
                                    <div class="aspect-[16/10] bg-primary flex items-center justify-center" style="background: var(--c-primary);">
                                        <div class="ornament" style="opacity:.45;"></div>
                                    </div>
                                @endif
                            </a>
                            <div class="p-5 sm:p-6">
                                @if($post->category)
                                    <a href="{{ route('blog.category', $post->category->slug) }}"
                                       class="elite-kicker text-[.55rem] mb-2 inline-block hover:underline"
                                       style="color: var(--c-accent);">{{ $post->category->name }}</a>
                                @endif
                                <a href="{{ route('blog.show', $post->slug) }}" class="block group-hover:opacity-80 transition">
                                    <h2 class="elite-h2 text-xl lg:text-2xl ink-primary leading-tight mb-2">{{ $post->title }}</h2>
                                </a>
                                @if($post->excerpt)
                                    <p class="font-serif text-base text-gray-600 leading-relaxed mb-4">{{ Str::limit($post->excerpt, 160) }}</p>
                                @endif
                                <div class="flex items-center justify-between text-xs text-gray-500" style="font-family:'Inter',sans-serif;">
                                    <span>{{ $post->published_at?->format('d M Y') ?? '' }}</span>
                                    <span class="font-script italic text-sm">{{ $post->author?->name ?? 'Redaksi' }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>

        <aside class="lg:col-span-4 space-y-8">
            <div class="bg-white border border-rule p-5 sm:p-6">
                <h3 class="elite-kicker mb-4" style="color: var(--c-accent);">Cari Artikel</h3>
                <form action="{{ route('blog.index') }}" method="GET" class="flex gap-2">
                    <input type="text" name="search" placeholder="Ketik kata kunci..."
                           value="{{ request('search') }}"
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

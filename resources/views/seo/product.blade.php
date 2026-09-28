@extends('seo.layout')

@section('seo_content')
<section class="max-w-4xl mx-auto px-4 sm:px-6 py-16 lg:py-20">
    <nav class="elite-kicker mb-6" style="color: var(--c-muted);" aria-label="Breadcrumb">
        <a href="/" class="hover:ink-primary">Beranda</a>
        <span class="mx-2 ink-accent">/</span>
        <span>{{ $page['kicker'] }}</span>
    </nav>

    <header class="mb-12 pb-8 border-b border-rule text-center">
        <div class="ornament-center"></div>
        <div class="elite-kicker mb-3">{{ $page['kicker'] }}</div>
        <h1 class="elite-h1 text-4xl sm:text-5xl ink-primary mb-5 leading-tight">{{ $page['title'] }}</h1>
        <div class="elite-rule mx-auto mb-5"></div>
        <p class="elite-lead max-w-2xl mx-auto">{{ $page['lead'] }}</p>
    </header>

    <section class="bg-white border border-rule p-6 sm:p-8 mb-12" aria-label="Ringkasan">
        <h2 class="elite-h3 text-xl ink-primary mb-3">Jawaban singkat</h2>
        <p class="font-serif text-lg leading-relaxed text-gray-800">{{ $page['answer'] }}</p>
    </section>

    @foreach($page['sections'] as $s)
        <section class="mb-10">
            <h2 class="elite-h2 text-2xl sm:text-3xl ink-primary mb-4">{{ $s['h'] }}</h2>
            <p class="font-serif text-lg leading-relaxed text-gray-800">{{ $s['p'] }}</p>
        </section>
    @endforeach

    @if(!empty($page['features']))
        <section class="mb-12">
            <h2 class="elite-h2 text-2xl sm:text-3xl ink-primary mb-6 text-center">Fitur utama</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($page['features'] as $group => $items)
                    <div class="elite-card p-5">
                        <h3 class="elite-h3 text-lg ink-accent mb-3">{{ $group }}</h3>
                        <ul class="space-y-2 font-serif text-gray-700">
                            @foreach($items as $it)
                                <li class="flex gap-2"><span aria-hidden="true">✓</span><span>{{ $it }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if(!empty($page['usecases']))
        <section class="mb-12">
            <h2 class="elite-h2 text-2xl sm:text-3xl ink-primary mb-6 text-center">Cocok untuk</h2>
            <div class="space-y-3">
                @foreach($page['usecases'] as $u)
                    <div class="bg-white border border-rule p-5">
                        <h3 class="elite-h3 text-lg ink-primary mb-1">{{ $u['t'] }}</h3>
                        <p class="font-serif text-gray-700">{{ $u['d'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if(!empty($page['faq']))
        <section class="border-t border-rule pt-10 mb-14" aria-label="FAQ">
            <h2 class="elite-h2 text-3xl ink-primary mb-6 text-center">Pertanyaan Lazim</h2>
            <div class="elite-rule mx-auto mb-7"></div>
            <div class="space-y-3">
                @foreach($page['faq'] as $f)
                    <details class="bg-white border border-rule group">
                        <summary class="flex items-center justify-between cursor-pointer py-4 px-5 list-none">
                            <span class="elite-h3 text-lg ink-primary">{{ $f[0] }}</span>
                            <span class="text-2xl leading-none transition group-open:rotate-45" style="color: var(--c-accent);" aria-hidden="true">+</span>
                        </summary>
                        <div class="px-5 pb-5 font-serif text-lg leading-relaxed text-gray-700">{{ $f[1] }}</div>
                    </details>
                @endforeach
            </div>
        </section>
    @endif

    @if(!empty($page['related']))
        <nav class="border-t border-rule pt-8 mb-10" aria-label="Artikel terkait">
            <h2 class="elite-h3 text-xl ink-primary mb-4">Pelajari juga</h2>
            <ul class="grid sm:grid-cols-2 gap-3">
                @foreach($page['related'] as $r)
                    <li><a href="{{ $r['u'] }}" class="elite-card p-4 block hover:shadow font-serif text-ink-primary">{{ $r['t'] }} →</a></li>
                @endforeach
            </ul>
        </nav>
    @endif

    <section class="mt-8 deco-frame">
        <div class="bg-[var(--c-primary)] text-white p-10 text-center">
            <div class="elite-kicker mb-3" style="color: var(--c-accent);">SIKAD PRO</div>
            <h2 class="elite-h2 text-3xl text-white mb-3">Lihat Cara Kerjanya di Sekolah Anda</h2>
            <p class="font-serif text-lg text-white/80 max-w-xl mx-auto mb-6">Jelajahi demo, dokumentasi, dan paket berlangganan yang sesuai kebutuhan.</p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="/pricing" class="btn-elite-gold">Lihat Paket</a>
                <a href="/docs" class="btn-elite-ghost" style="border-color:#fff;color:#fff;">Dokumentasi</a>
            </div>
        </div>
    </section>
</section>
@endsection

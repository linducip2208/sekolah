@props(['theme' => [], 'landing' => []])
<section class="lp-section lp-bg-brand">
    <div class="lp-container">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <p class="lp-kicker mb-3">Pratinjau Produk</p>
            <h2 class="lp-title text-3xl sm:text-4xl">Semua kebutuhan sekolah, dalam satu tempat.</h2>
            <p class="lp-lead mt-4">Jelajahi antarmuka utama platform — dari dashboard hingga keuangan.</p>
        </div>

        <div x-data="{ tab: 0 }" class="max-w-5xl mx-auto">
            <div class="flex flex-wrap justify-center gap-2 mb-6" role="tablist" aria-label="Pratinjau produk">
                @foreach($landing['preview'] as $i => $p)
                    <button type="button" role="tab" :aria-selected="(tab === {{ $i }}).toString()" aria-controls="panel-{{ $i }}"
                            @click="tab = {{ $i }}"
                            class="px-4 py-2 text-sm font-semibold rounded-full transition"
                            :class="tab === {{ $i }} ? '' : ''"
                            :style="tab === {{ $i }} ? 'background: var(--lp-primary); color: #fff;' : 'background: var(--lp-accent-soft); color: var(--lp-muted);'">
                        {{ $p['tab'] }}
                    </button>
                @endforeach
            </div>

            <div class="reveal visible">
                @foreach($landing['preview'] as $i => $p)
                    <div x-show="tab === {{ $i }}" x-cloak role="tabpanel" id="panel-{{ $i }}" aria-label="{{ $p['tab'] }}">
                        <div class="rounded-xl overflow-hidden shadow-xl border" style="border-color: var(--lp-border);">
                            <div class="flex items-center gap-1.5 px-4 py-3" style="background: var(--lp-surface); border-bottom: 1px solid var(--lp-border);">
                                <span class="w-3 h-3 rounded-full bg-[#f87171]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#fbbf24]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#34d399]"></span>
                                <span class="ml-3 flex-1 text-xs truncate px-3 py-1 rounded-md" style="background: var(--lp-surface-subtle); color: var(--lp-muted);">{{ parse_url(url('/'), PHP_URL_HOST) }}</span>
                            </div>
                            <img src="{{ $p['img'] }}" alt="{{ $p['alt'] }}" loading="lazy" class="w-full h-auto">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

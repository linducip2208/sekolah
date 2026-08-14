@props(['theme' => [], 'landing' => [], 'plans' => [], 'heading' => true])
<section class="lp-section" id="harga" style="background: var(--lp-bg);">
    <div class="lp-container">
        @if($heading)
        <div class="text-center max-w-2xl mx-auto mb-14">
            <p class="lp-kicker mb-3">Harga</p>
            <h2 class="lp-title text-3xl sm:text-4xl">Pilih paket sesuai kebutuhan sekolah.</h2>
            <p class="lp-lead mt-4">Mulai dari paket terjangkau hingga enterprise — dengan dukungan migrasi data.</p>
        </div>
        @endif

        @if($plans->isNotEmpty())
            <div class="grid md:grid-cols-3 gap-6 max-w-6xl mx-auto items-start">
                @foreach($plans as $plan)
                    @php $popular = $plan->slug === 'basic'; @endphp
                    <div class="lp-card lp-card-{{ $theme['style']['card'] }} p-7 flex flex-col relative {{ $popular ? 'border-2' : '' }}"
                         @if($popular) style="border-color: var(--lp-accent);" @endif>
                        @if($popular)
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 text-xs font-bold rounded-full" style="background: var(--lp-accent); color: #fff;">Paling Populer</span>
                        @endif
                        <h3 class="font-display font-bold text-xl" style="color: var(--lp-ink);">{{ $plan->name }}</h3>
                        <div class="mt-4 mb-5">
                            @if($plan->price === 0)
                                <span class="font-display text-4xl font-bold" style="color: var(--lp-ink);">Gratis</span>
                            @else
                                <span class="font-display text-4xl font-bold" style="color: var(--lp-ink);">{{ $plan->price_rupiah }}</span>
                                <span class="text-sm" style="color: var(--lp-muted);">/bulan</span>
                            @endif
                        </div>
                        <ul class="space-y-2.5 mb-7 flex-1 text-sm" style="color: var(--lp-muted);">
                            <li class="flex items-start gap-2"><span class="text-[var(--lp-accent)] mt-0.5">✓</span> Hingga {{ $plan->max_students ?: 'tak terbatas' }} siswa</li>
                            <li class="flex items-start gap-2"><span class="text-[var(--lp-accent)] mt-0.5">✓</span> Hingga {{ $plan->max_teachers ?: 'tak terbatas' }} guru</li>
                            @foreach(($plan->features ?? []) as $f)
                                <li class="flex items-start gap-2"><span class="text-[var(--lp-accent)] mt-0.5">✓</span> {{ $f === '*' ? 'Semua fitur platform' : ucfirst($f) }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ route('public.subscription.register', ['plan' => $plan->slug]) }}" class="lp-btn w-full {{ $popular ? '' : 'lp-btn-secondary' }}">
                            {{ $plan->price === 0 ? 'Mulai Gratis' : 'Daftar Sekarang' }}
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center">
                <p class="lp-lead">Paket berlangganan sedang disiapkan. Hubungi kami untuk penawaran khusus institusi Anda.</p>
                <a href="{{ $platform['whatsapp_link'] ?? route('public.pricing') }}" class="lp-btn mt-6">Bicara dengan Tim</a>
            </div>
        @endif
    </div>
</section>

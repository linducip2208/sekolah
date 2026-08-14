@props(['theme' => []])
@php $demoHref = $platform['whatsapp_link'] ?? route('public.pricing'); @endphp
<section class="lp-section" style="background: var(--lp-primary);">
    <div class="lp-container text-center max-w-3xl mx-auto">
        <h2 class="font-display text-3xl sm:text-4xl font-bold" style="color: #fff;">Siap memodernisasi sekolah Anda?</h2>
        <p class="mt-4 text-lg leading-relaxed" style="color: rgba(255,255,255,.82);">
            Kelola sekolah Anda. Hubungkan guru, siswa, dan orang tua. Ambil keputusan dengan data.
        </p>
        <div class="flex flex-wrap justify-center gap-3 mt-8">
            <a href="{{ $demoHref }}" class="lp-btn lp-btn-accent">Request Demo</a>
            <a href="{{ route('admin.login') }}" class="lp-btn" style="background: transparent; border: 1px solid rgba(255,255,255,.4);">Lihat Akun Demo</a>
        </div>
    </div>
</section>

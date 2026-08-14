@props(['theme' => [], 'landing' => []])
@php
    $steps = [
        ['no' => '01', 'title' => 'Daftarkan sekolah Anda', 'desc' => 'Pilih paket, lengkapi data sekolah, dan tentukan subdomain Anda.'],
        ['no' => '02', 'title' => 'Konfigurasi modul', 'desc' => 'Atur tahun ajaran, kelas, struktur biaya, dan integrasi pembayaran atau AI.'],
        ['no' => '03', 'title' => 'Mulai kelola sekolah', 'desc' => 'Undang guru dan staf, input siswa, lalu kelola operasional harian dari satu platform.'],
    ];
@endphp
<section class="lp-section" style="background: var(--lp-bg);">
    <div class="lp-container">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <p class="lp-kicker mb-3">Cara Kerja</p>
            <h2 class="lp-title text-3xl sm:text-4xl">Mulai dalam tiga langkah sederhana.</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            @foreach($steps as $s)
                <div class="reveal">
                    <div class="font-display text-4xl font-bold" style="color: var(--lp-accent);">{{ $s['no'] }}</div>
                    <h3 class="font-semibold text-lg mt-3" style="color: var(--lp-ink);">{{ $s['title'] }}</h3>
                    <p class="text-sm mt-2 leading-relaxed" style="color: var(--lp-muted);">{{ $s['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

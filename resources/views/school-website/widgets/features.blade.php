{{-- Widget: Features Section --}}
@php
    $features = $section->config['features'] ?? [];
@endphp
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        @if($section->title)
            <h2 class="text-3xl font-display font-bold text-gray-900 mb-2 text-center">{{ $section->title }}</h2>
        @endif
        @if($section->subtitle)
            <p class="text-lg text-gray-600 mb-10 text-center">{{ $section->subtitle }}</p>
        @endif
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @if(!empty($features))
                @foreach($features as $feature)
                <div class="text-center p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition">
                    <div class="text-4xl mb-4">{{ $feature['icon'] ?? '⭐' }}</div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $feature['title'] ?? '' }}</h3>
                    <p class="text-gray-600 text-sm">{{ $feature['description'] ?? '' }}</p>
                </div>
                @endforeach
            @else
                @foreach([
                    ['icon' => '📚', 'title' => 'Kurikulum Unggulan', 'desc' => 'Kurikulum nasional dengan penguatan karakter dan kompetensi abad 21.'],
                    ['icon' => '👨‍🏫', 'title' => 'Guru Profesional', 'desc' => 'Tenaga pendidik berpengalaman dan tersertifikasi.'],
                    ['icon' => '🏫', 'title' => 'Fasilitas Lengkap', 'desc' => 'Ruang kelas modern, lab, perpustakaan, dan sarana olahraga.'],
                    ['icon' => '💻', 'title' => 'Teknologi Digital', 'desc' => 'Sistem pembelajaran berbasis teknologi dan manajemen digital.'],
                    ['icon' => '🏆', 'title' => 'Prestasi Gemilang', 'desc' => 'Ratusan prestasi akademik dan non-akademik tingkat nasional.'],
                    ['icon' => '🤝', 'title' => 'Kemitraan Luas', 'desc' => 'Kerjasama dengan industri dan perguruan tinggi terkemuka.'],
                ] as $f)
                <div class="text-center p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition">
                    <div class="text-4xl mb-4">{{ $f['icon'] }}</div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $f['title'] }}</h3>
                    <p class="text-gray-600 text-sm">{{ $f['desc'] }}</p>
                </div>
                @endforeach
            @endif
        </div>
    </div>
</section>

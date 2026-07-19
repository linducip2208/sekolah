{{-- Widget: Stats Section --}}
@php
    $stats = $section->stats ?? [
        ['label' => 'Siswa', 'value' => 0, 'icon' => 'graduation-cap'],
        ['label' => 'Guru', 'value' => 0, 'icon' => 'users'],
        ['label' => 'Jurusan', 'value' => 0, 'icon' => 'book-open'],
        ['label' => 'Lulusan', 'value' => 0, 'icon' => 'trophy'],
    ];
@endphp
<section class="py-16" style="background: linear-gradient(135deg, {{ $branding['colors']['primary'] ?? '#2563EB' }}15, {{ $branding['colors']['secondary'] ?? '#64748B' }}08)">
    <div class="container mx-auto px-4">
        @if($section->title)
            <h2 class="text-3xl font-display font-bold text-gray-900 mb-2 text-center">{{ $section->title }}</h2>
        @endif
        @if($section->subtitle)
            <p class="text-lg text-gray-600 mb-10 text-center">{{ $section->subtitle }}</p>
        @endif
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            @foreach($stats as $stat)
            <div class="bg-white rounded-xl shadow p-6 text-center">
                <div class="text-4xl font-display font-bold" style="color: {{ $branding['colors']['primary'] ?? '#2563EB' }}">{{ $stat['value'] }}+</div>
                <div class="text-sm text-gray-500 mt-2">{{ $stat['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Widget: Hero Section --}}
<section class="hero-section" style="background: linear-gradient(135deg, {{ $branding['colors']['primary'] ?? '#2563EB' }}, {{ $branding['colors']['secondary'] ?? '#64748B' }})">
    <div class="container mx-auto px-4 py-20 lg:py-28">
        <div class="max-w-3xl mx-auto text-center text-white">
            @if($section->image_path)
                <img src="{{ Storage::disk('public')->url($section->image_path) }}" alt="{{ $section->title }}" class="mx-auto mb-8 max-h-24 rounded-xl shadow-lg">
            @endif
            @if($section->title)
                <h1 class="text-4xl lg:text-5xl font-display font-bold mb-4 leading-tight">{{ $section->title }}</h1>
            @endif
            @if($section->subtitle)
                <p class="text-lg lg:text-xl opacity-90 leading-relaxed">{{ $section->subtitle }}</p>
            @endif
            @if($section->content)
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="#contact" class="inline-block bg-white text-gray-900 font-semibold px-6 py-3 rounded-lg shadow hover:shadow-lg transition transform hover:-translate-y-0.5">Hubungi Kami</a>
                </div>
            @endif
        </div>
    </div>
</section>

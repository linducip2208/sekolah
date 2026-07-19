{{-- Widget: CTA Section --}}
<section class="py-20" style="background: linear-gradient(135deg, {{ $branding['colors']['primary'] ?? '#2563EB' }}, {{ $branding['colors']['secondary'] ?? '#64748B' }})">
    <div class="container mx-auto px-4 text-center text-white">
        @if($section->title)
            <h2 class="text-3xl lg:text-4xl font-display font-bold mb-4">{{ $section->title }}</h2>
        @endif
        @if($section->subtitle)
            <p class="text-lg opacity-90 mb-8 max-w-2xl mx-auto">{{ $section->subtitle }}</p>
        @endif
        @if($section->content)
            <div class="text-white/90 max-w-2xl mx-auto mb-8">{!! $section->content !!}</div>
        @endif
        <a href="#contact" class="inline-block bg-white text-gray-900 font-semibold px-8 py-3 rounded-lg shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5">
            Daftar Sekarang
        </a>
    </div>
</section>

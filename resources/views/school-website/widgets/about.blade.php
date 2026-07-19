{{-- Widget: About Section --}}
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            @if($section->title)
                <h2 class="text-3xl font-display font-bold text-gray-900 mb-4 text-center">{{ $section->title }}</h2>
            @endif
            @if($section->subtitle)
                <p class="text-lg text-gray-600 mb-8 text-center">{{ $section->subtitle }}</p>
            @endif
            @if($section->image_path)
                <div class="mb-8 rounded-xl overflow-hidden shadow-lg">
                    <img src="{{ Storage::disk('public')->url($section->image_path) }}" alt="{{ $section->title }}" class="w-full h-auto">
                </div>
            @endif
            @if($section->content)
                <div class="prose max-w-none text-gray-700 leading-relaxed">
                    {!! $section->content !!}
                </div>
            @endif
        </div>
    </div>
</section>

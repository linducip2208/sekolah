{{-- Widget: Custom HTML Section --}}
<section class="py-16">
    <div class="container mx-auto px-4">
        @if($section->title)
            <h2 class="text-3xl font-display font-bold text-gray-900 mb-2 text-center">{{ $section->title }}</h2>
        @endif
        @if($section->subtitle)
            <p class="text-lg text-gray-600 mb-8 text-center">{{ $section->subtitle }}</p>
        @endif
        @if($section->content)
            <div class="max-w-4xl mx-auto">
                {!! $section->content !!}
            </div>
        @endif
    </div>
</section>

{{-- Widget: Gallery Section --}}
@php $images = $section->gallery_images ?? []; @endphp
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        @if($section->title)
            <h2 class="text-3xl font-display font-bold text-gray-900 mb-2 text-center">{{ $section->title }}</h2>
        @endif
        @if($section->subtitle)
            <p class="text-lg text-gray-600 mb-10 text-center">{{ $section->subtitle }}</p>
        @endif
        @if(!empty($images))
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($images as $img)
                <div class="rounded-xl overflow-hidden shadow hover:shadow-lg transition transform hover:scale-[1.02]">
                    <img src="{{ Storage::disk('public')->url($img['file_path']) }}" alt="{{ $img['caption'] ?? $img['title'] ?? '' }}" class="w-full h-48 object-cover">
                    @if($img['caption'])
                        <div class="p-2 text-xs text-gray-600 bg-white">{{ $img['caption'] }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 text-gray-400">
                <p class="text-4xl mb-2">🖼️</p>
                <p>Belum ada foto galeri.</p>
            </div>
        @endif
    </div>
</section>

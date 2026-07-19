{{-- Widget: News Section --}}
@php $posts = $section->news_posts ?? []; @endphp
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        @if($section->title)
            <h2 class="text-3xl font-display font-bold text-gray-900 mb-2 text-center">{{ $section->title }}</h2>
        @endif
        @if($section->subtitle)
            <p class="text-lg text-gray-600 mb-10 text-center">{{ $section->subtitle }}</p>
        @endif
        @if(!empty($posts))
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($posts as $post)
                <div class="bg-white rounded-xl shadow overflow-hidden hover:shadow-lg transition">
                    @if(!empty($post['featured_image'] ?? ''))
                        <img src="{{ Storage::disk('public')->url($post['featured_image']) }}" class="w-full h-48 object-cover">
                    @endif
                    <div class="p-5">
                        <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">{{ $post['title'] ?? '' }}</h3>
                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ $post['excerpt'] ?? \Illuminate\Support\Str::limit(strip_tags($post['content'] ?? ''), 100) }}</p>
                        <div class="flex items-center justify-between text-xs text-gray-400">
                            <span>{{ !empty($post['published_at']) ? \Carbon\Carbon::parse($post['published_at'])->format('d M Y') : '' }}</span>
                            <span class="text-blue-600 font-semibold">Baca →</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 text-gray-400">
                <p class="text-4xl mb-2">📰</p>
                <p>Belum ada artikel.</p>
            </div>
        @endif
    </div>
</section>

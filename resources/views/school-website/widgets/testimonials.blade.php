{{-- Widget: Testimonials Section --}}
@php $testimonials = $section->testimonial_items ?? []; @endphp
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        @if($section->title)
            <h2 class="text-3xl font-display font-bold text-gray-900 mb-2 text-center">{{ $section->title }}</h2>
        @endif
        @if($section->subtitle)
            <p class="text-lg text-gray-600 mb-10 text-center">{{ $section->subtitle }}</p>
        @endif
        @if(!empty($testimonials))
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($testimonials as $t)
                <div class="bg-gray-50 rounded-xl p-6 relative">
                    <div class="flex items-center gap-0.5 mb-3">
                        @for($i=1; $i<=5; $i++)
                            <svg class="w-4 h-4 {{ $i <= ($t['rating'] ?? 5) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        @endfor
                    </div>
                    <p class="text-gray-700 mb-4 leading-relaxed">"{{ $t['testimonial_text'] ?? '' }}"</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center overflow-hidden">
                            @if(!empty($t['photo_path']))
                                <img src="{{ Storage::disk('public')->url($t['photo_path']) }}" class="w-full h-full object-cover">
                            @else
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            @endif
                        </div>
                        <div>
                            <div class="font-semibold text-sm">{{ $t['name'] ?? '' }}</div>
                            <div class="text-xs text-gray-500">
                                @if(($t['role'] ?? '') === 'alumni') Alumni
                                @elseif(($t['role'] ?? '') === 'parent') Orang Tua
                                @else Siswa
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 text-gray-400">
                <p class="text-4xl mb-2">💬</p>
                <p>Belum ada testimoni.</p>
            </div>
        @endif
    </div>
</section>

@extends('layouts.parent')
@section('title', 'Komunitas')
@section('content')

<div class="mb-8">
    <div class="elite-kicker mb-2">Communitas</div>
    <h1 class="elite-h1 text-4xl ink-primary mb-2">Forum Komunitas</h1>
    <div class="elite-rule mb-3"></div>
    <p class="font-serif text-lg" style="color: var(--c-muted);">Diskusi antar orang tua, guru, dan komunitas sekolah.</p>
</div>

<div class="flex justify-between items-center mb-6">
    <div></div>
    <a href="{{ route('forum.create') }}" class="btn-elite-gold text-sm">+ Buat Topik Baru</a>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Categories --}}
    <div class="lg:col-span-2 space-y-4">
        @foreach($categories as $cat)
            <div class="bg-white border border-rule p-5">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h2 class="elite-h3 text-lg ink-primary">{{ $cat->name }}</h2>
                        <p class="text-xs text-gray-500">{{ $cat->description }}</p>
                    </div>
                    <a href="{{ route('forum.category', $cat) }}" class="text-xs underline ink-secondary hover:ink-accent">Lihat Semua →</a>
                </div>
                @if($cat->topics->isEmpty())
                    <p class="text-sm text-gray-400 italic font-serif">Belum ada topik.</p>
                @else
                    <div class="space-y-2">
                        @foreach($cat->topics->take(5) as $t)
                            <a href="{{ route('forum.topic', $t) }}" class="flex justify-between items-start gap-3 py-2 border-b border-rule/30 last:border-0 hover:text-[var(--c-accent)] transition">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-baseline gap-2">
                                        @if($t->is_pinned) <span class="text-xs text-blue-600">📌</span> @endif
                                        @if($t->is_locked) <span class="text-xs text-red-600">🔒</span> @endif
                                        <span class="font-serif text-sm font-semibold truncate">{{ $t->title }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        {{ $t->user->name }} · {{ $t->replies_count }} balasan · {{ $t->last_reply_at?->diffForHumans() }}
                                    </div>
                                </div>
                                <div class="text-xs text-gray-400 flex-shrink-0 text-right mt-1">
                                    <div>{{ $t->view_count }} 👁</div>
                                    <div>{{ $t->replies_count }} 💬</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white border border-rule p-5">
            <h3 class="elite-h3 text-base ink-primary mb-3">Diskusi Terbaru</h3>
            <div class="space-y-2">
                @foreach($recentTopics as $t)
                    <a href="{{ route('forum.topic', $t) }}" class="block py-2 border-b border-rule/30 last:border-0">
                        <div class="font-serif text-sm font-semibold truncate hover:text-[var(--c-accent)]">{{ $t->title }}</div>
                        <div class="text-xs text-gray-500">{{ $t->user->name }} · {{ $t->category?->name }}</div>
                    </a>
                @endforeach
            </div>
        </div>

        <a href="{{ route('forum.create') }}" class="elite-card block p-5 text-center hover:border-[var(--c-accent)] transition">
            <div class="text-2xl mb-2">💡</div>
            <div class="font-serif text-base ink-primary">Punya Pertanyaan?</div>
            <div class="text-xs text-gray-500 mt-1">Buat topik diskusi baru</div>
        </a>
    </div>
</div>

@endsection

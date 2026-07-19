@extends('layouts.parent')
@section('title', $category->name)
@section('content')

<a href="{{ route('forum.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Forum</a>

<div class="mb-8">
    <div class="elite-kicker mb-2">Categoria</div>
    <h1 class="elite-h1 text-4xl ink-primary mb-2">{{ $category->name }}</h1>
    <div class="elite-rule mb-3"></div>
    <p class="font-serif text-lg" style="color: var(--c-muted);">{{ $category->description }}</p>
</div>

<div class="flex justify-between items-center mb-5">
    <div class="text-sm text-gray-500">{{ $topics->total() }} topik</div>
    <a href="{{ route('forum.create') }}?category={{ $category->id }}" class="btn-elite-gold text-xs">+ Topik Baru</a>
</div>

@forelse($topics as $t)
    <a href="{{ route('forum.topic', $t) }}" class="block bg-white border border-rule p-5 mb-3 hover:border-[var(--c-accent)] transition">
        <div class="flex justify-between items-start gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-baseline gap-2">
                    @if($t->is_pinned) <span class="text-xs text-blue-600">📌</span> @endif
                    @if($t->is_locked) <span class="text-xs text-red-600">🔒</span> @endif
                    <h3 class="elite-h3 text-base ink-primary truncate">{{ $t->title }}</h3>
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    Oleh <strong>{{ $t->user->name }}</strong> · {{ $t->created_at->diffForHumans() }}
                </div>
            </div>
            <div class="text-right flex-shrink-0 flex gap-4 text-xs text-gray-400">
                <div>👁 {{ $t->view_count }}</div>
                <div>💬 {{ $t->replies_count }}</div>
            </div>
        </div>
    </a>
@empty
    <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">
        Belum ada topik di kategori ini.
    </div>
@endforelse

<div class="mt-5">{{ $topics->links() }}</div>

@endsection

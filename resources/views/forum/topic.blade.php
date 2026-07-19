@extends('layouts.parent')
@section('title', $topic->title)
@section('content')

<a href="{{ route('forum.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Forum</a>
<a href="{{ route('forum.category', $topic->category) }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 ml-4 inline-block">← {{ $topic->category->name }}</a>

<div class="bg-white border border-rule p-6 mb-6">
    <div class="flex items-start gap-3 mb-3">
        @if($topic->is_pinned) <span class="text-blue-600">📌</span> @endif
        @if($topic->is_locked) <span class="text-red-600">🔒</span> @endif
        <div class="flex-1">
            <h1 class="elite-h1 text-2xl ink-primary">{{ $topic->title }}</h1>
            <div class="text-xs text-gray-500 mt-1">
                Oleh <strong>{{ $topic->user->name }}</strong> · {{ $topic->created_at->diffForHumans() }} · 👁 {{ $topic->view_count }} · 💬 {{ $topic->replyCount() }}
            </div>
        </div>
    </div>

    <div class="font-serif text-base text-gray-800 leading-relaxed mt-4">
        {!! nl2br(e($topic->content)) !!}
    </div>

    <div class="flex gap-3 mt-4 pt-4 border-t border-rule">
        @if($isSubscribed)
            <form method="POST" action="{{ route('forum.unsubscribe', $topic) }}">
                @csrf
                <button class="text-xs underline text-gray-500 hover:text-red-700">🔕 Unsubscribe</button>
            </form>
        @else
            <form method="POST" action="{{ route('forum.subscribe', $topic) }}">
                @csrf
                <button class="text-xs underline text-blue-700 hover:text-blue-900">🔔 Subscribe Notifikasi</button>
            </form>
        @endif
    </div>
</div>

{{-- Replies --}}
@if($replies->isEmpty())
    <div class="bg-white border border-rule p-8 text-center">
        <p class="font-serif text-base text-gray-500 italic">Belum ada balasan. Jadilah yang pertama!</p>
    </div>
@else
    <div class="space-y-3 mb-6">
        @foreach($replies as $reply)
            <div class="bg-white border border-rule p-5 {{ $reply->parent_id ? 'ml-8 border-l-4 border-l-[var(--c-accent)]' : '' }}">
                <div class="flex items-baseline gap-3 mb-2">
                    <span class="font-serif font-semibold text-sm ink-primary">{{ $reply->user->name }}</span>
                    <span class="text-xs text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                </div>
                <div class="font-serif text-sm text-gray-700 leading-relaxed">
                    {!! nl2br(e($reply->content)) !!}
                </div>
                @if(!$topic->is_locked)
                    <button onclick="toggleReplyForm({{ $reply->id }})" class="text-xs underline text-gray-400 hover:text-gray-700 mt-2">Balas</button>
                    <div id="reply-form-{{ $reply->id }}" style="display:none;" class="mt-3 ml-4">
                        <form method="POST" action="{{ route('forum.reply', $topic) }}">
                            @csrf
                            <input type="hidden" name="parent_id" value="{{ $reply->id }}">
                            <textarea name="content" rows="3" required maxlength="5000"
                                      class="w-full border-2 border-rule px-3 py-2 font-serif text-sm mb-2" placeholder="Tulis balasan..."></textarea>
                            <button class="btn-elite text-xs">Balas</button>
                        </form>
                    </div>
                @endif

                {{-- Nested children --}}
                @foreach($reply->children as $child)
                    <div class="mt-3 ml-8 border-l-2 border-rule/40 pl-4">
                        <div class="flex items-baseline gap-3 mb-1">
                            <span class="font-serif font-semibold text-sm ink-primary">{{ $child->user->name }}</span>
                            <span class="text-xs text-gray-400">{{ $child->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="font-serif text-sm text-gray-700">{!! nl2br(e($child->content)) !!}</div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
    {{ $replies->links() }}
@endif

{{-- Reply Form --}}
@if(!$topic->is_locked)
    <div class="bg-white border border-rule p-6">
        <h3 class="elite-h3 text-base ink-primary mb-3">Tulis Balasan</h3>
        <form method="POST" action="{{ route('forum.reply', $topic) }}">
            @csrf
            <textarea name="content" rows="4" required maxlength="5000"
                      class="w-full border-2 border-rule px-3 py-2 font-serif text-sm mb-3" placeholder="Bagikan pendapat Anda..."></textarea>
            <div class="flex gap-3">
                <button class="btn-elite">Kirim Balasan</button>
            </div>
        </form>
    </div>
@else
    <div class="bg-yellow-50 border border-yellow-200 p-5 text-center">
        <p class="font-serif text-sm text-yellow-800">🔒 Topik ini sudah dikunci. Tidak dapat menambah balasan baru.</p>
    </div>
@endif

@push('scripts')
<script>
function toggleReplyForm(id) {
    const el = document.getElementById('reply-form-' + id);
    el.style.display = el.style.display === 'none' ? '' : 'none';
}
</script>
@endpush

@endsection

@extends('layouts.school-admin')
@section('title', 'Percakapan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
@php $other = $conversation->user_one === auth()->id() ? $conversation->userTwo : $conversation->userOne; @endphp
<a href="{{ route('admin.chat.inbox') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Inbox</a>
<div class="mb-7"><h1 class="elite-h1 text-2xl ink-primary mb-2">Chat dengan {{ $other?->name }}</h1>
<div class="elite-rule"></div></div>

<div class="bg-white border border-rule p-5 space-y-3 max-h-[60vh] overflow-y-auto mb-4">
@forelse($messages as $m)
@php $isMe = $m->sender_id === auth()->id(); @endphp
<div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }}">
<div class="max-w-md {{ $isMe ? 'bg-[var(--c-primary)] text-white' : 'bg-gray-100' }} px-4 py-2 rounded">
<div class="text-xs opacity-70 mb-1">{{ $m->sender?->name }} · {{ $m->created_at?->format('H:i') }}</div>
<div class="font-serif text-sm">{{ $m->body }}</div>
</div></div>
@empty<div class="text-center text-gray-500 italic font-serif">Belum ada pesan.</div>
@endforelse
</div>

<form method="POST" action="{{ route('admin.chat.send', $conversation) }}" class="flex gap-2">@csrf
<input name="body" required maxlength="5000" placeholder="Ketik pesan..." class="flex-1 border-2 border-rule px-3 py-2 font-serif text-sm" autofocus>
<button class="btn-elite">Kirim</button>
</form>
@endsection

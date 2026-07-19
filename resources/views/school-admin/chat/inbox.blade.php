@extends('layouts.school-admin')
@section('title', 'Inbox')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Buncha</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Inbox / Percakapan</h1><div class="elite-rule"></div></div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Percakapan Baru</summary>
<form method="POST" action="{{ route('admin.chat.start') }}" class="px-5 py-5 border-t border-rule space-y-3">@csrf
<select name="user_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— Kirim ke siapa —</option>
@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
</select>
<textarea name="message" rows="3" required maxlength="5000" placeholder="Pesan pertama" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
<button class="btn-elite">Kirim</button>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><div class="divide-y">
@forelse($conversations as $c)
@php
    $other = $c->user_one === auth()->id() ? $c->userTwo : $c->userOne;
@endphp
<a href="{{ route('admin.chat.show', $c) }}" class="block px-5 py-4 hover:bg-gray-50">
<div class="flex justify-between items-center">
<div class="font-serif font-semibold ink-primary">{{ $other?->name ?? 'User' }}</div>
<div class="text-xs text-gray-500">{{ $c->last_message_at?->diffForHumans() }}</div>
</div>
</a>
@empty<div class="p-10 text-center text-gray-500 italic font-serif">Belum ada percakapan.</div>
@endforelse
</div></div>
<div class="mt-4">{{ $conversations->links() }}</div>
@endsection

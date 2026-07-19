@extends('layouts.school-admin')
@section('title', 'Notifikasi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Notificationes</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Notifikasi</h1><div class="elite-rule"></div></div>

<div class="space-y-2">
@forelse($notifications as $n)
<div class="bg-white border-l-4 {{ $n->is_read ? 'border-gray-300' : 'border-[var(--c-accent)]' }} p-4">
<div class="flex justify-between items-start">
<div class="flex-1">
<div class="flex items-baseline gap-2 mb-1">
<h4 class="font-serif font-semibold ink-primary">{{ $n->title }}</h4>
<span class="elite-kicker text-[.55rem]" style="color:var(--c-muted);">{{ $n->type }}</span>
@if(!$n->is_read)<span class="text-xs text-orange-700">● Baru</span>@endif
</div>
<p class="font-serif text-sm text-gray-700">{{ $n->body }}</p>
<div class="text-xs text-gray-400 mt-1">{{ $n->created_at?->diffForHumans() }}</div>
</div>
@if(!$n->is_read)
<form method="POST" action="{{ route('admin.notifications.read', $n) }}">@csrf<button class="text-xs underline ink-secondary hover:ink-accent">Tandai dibaca</button></form>
@endif
</div></div>
@empty<div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada notifikasi.</div>
@endforelse
</div>
<div class="mt-4">{{ $notifications->links() }}</div>
@endsection

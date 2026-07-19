@extends('layouts.parent')
@section('title', 'Prestasi - '.$student->user?->name)
@section('content')
<a href="{{ route('portal.child', $student) }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← {{ $student->user?->name }}</a>
@include('parent-portal._child_tabs')

<h2 class="elite-h2 text-2xl ink-primary mb-4">Prestasi yang Diraih</h2>
<div class="grid md:grid-cols-2 gap-4">
@forelse($achievements as $a)
<div class="bg-white border border-rule p-5">
<div class="flex justify-between items-start mb-2">
<h3 class="elite-h3 text-base ink-primary">{{ $a->title }}</h3>
@if($a->verified)<span class="text-xs text-green-700">✓ Verified</span>@endif
</div>
<div class="elite-kicker text-[.55rem] mb-2" style="color: var(--c-accent);">{{ $a->category?->scope ?? '—' }} · {{ $a->achieved_at?->format('d M Y') }}</div>
<p class="font-serif text-sm text-gray-700">{{ $a->description }}</p>
@if($a->issuer)<div class="text-xs text-gray-500 mt-2">Penyelenggara: {{ $a->issuer }}</div>@endif
</div>
@empty<div class="md:col-span-2 bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada prestasi tercatat.</div>
@endforelse
</div>
<div class="mt-4">{{ $achievements->links() }}</div>
@endsection

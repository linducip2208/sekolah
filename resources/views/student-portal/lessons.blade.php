@extends('layouts.parent')
@section('title', 'Materi Pelajaran')
@section('content')
@include('student-portal._nav')
<div class="mb-6"><h1 class="elite-h1 text-2xl ink-primary mb-2">Materi Pelajaran</h1><div class="elite-rule"></div></div>

<div class="grid md:grid-cols-2 gap-4">
@forelse($lessons as $l)
<div class="bg-white border border-rule p-5">
<div class="elite-kicker text-[.6rem] mb-1" style="color: var(--c-accent);">{{ $l->subject?->name }}</div>
<h3 class="elite-h3 text-base ink-primary mb-2">{{ $l->title }}</h3>
<p class="font-serif text-sm text-gray-700">{{ Str::limit($l->description, 150) }}</p>
<div class="mt-3 text-xs text-gray-500">Oleh {{ $l->teacher?->name }} · {{ $l->created_at?->diffForHumans() }}</div>
</div>
@empty<div class="md:col-span-2 bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada materi.</div>
@endforelse
</div>
<div class="mt-4">{{ $lessons->links() }}</div>
@endsection

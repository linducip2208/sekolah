@extends('layouts.school-admin')
@section('title', 'Digital Badges')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><h1 class="elite-h1 text-3xl ink-primary mb-2">Digital Badges</h1><div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">Lencana digital untuk gamification prestasi siswa.</p></div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<form method="POST" action="{{ route('admin.misc.badges.store') }}" class="space-y-3">@csrf
<input name="name" required maxlength="200" placeholder="Nama badge" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<textarea name="description" rows="2" placeholder="Deskripsi" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
<textarea name="award_criteria" rows="2" placeholder="Kriteria pemberian" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Tambah Badge</button>
</form></div></div>

<div class="lg:col-span-2"><div class="grid grid-cols-2 gap-3">
@forelse($badges as $b)
<div class="bg-white border border-rule p-4 text-center">
<div class="text-4xl mb-2">🏅</div>
<div class="font-serif font-semibold ink-primary mb-1">{{ $b->name }}</div>
<div class="text-xs text-gray-500">{{ Str::limit($b->description, 60) }}</div>
</div>
@empty<div class="col-span-2 bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada badge.</div>
@endforelse
</div></div></div>
@endsection

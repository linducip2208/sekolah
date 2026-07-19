@extends('layouts.parent')
@section('title', 'Jadwal Pelajaran')
@section('content')
@include('student-portal._nav')

<div class="mb-6"><h1 class="elite-h1 text-2xl ink-primary mb-2">Jadwal Pelajaran Mingguan</h1>
<div class="elite-rule"></div></div>

@php $days = [1=>'Senin', 2=>'Selasa', 3=>'Rabu', 4=>'Kamis', 5=>'Jumat', 6=>'Sabtu', 7=>'Minggu']; @endphp
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-3">
@foreach($days as $num => $name)
<div class="bg-white border border-rule">
<div class="bg-[var(--c-primary)] text-white px-3 py-2 elite-kicker text-[.65rem] text-center">{{ $name }}</div>
<div class="p-2 space-y-2">
@forelse($slots[$num] ?? [] as $sl)
<div class="border border-rule p-2 text-xs">
<div class="font-mono ink-secondary">{{ \Carbon\Carbon::parse($sl->start_time)->format('H:i') }}–{{ \Carbon\Carbon::parse($sl->end_time)->format('H:i') }}</div>
<div class="font-serif font-semibold mt-1 ink-primary">{{ $sl->subject?->name }}</div>
<div class="text-gray-500">{{ $sl->teacher?->name }}</div>
</div>
@empty<div class="text-center text-xs text-gray-400 italic py-3">—</div>@endforelse
</div></div>
@endforeach
</div>
@endsection

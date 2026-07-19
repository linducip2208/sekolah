@extends('layouts.parent')
@section('title', 'Dashboard Guru')
@section('content')

<div class="mb-7">
<div class="elite-kicker mb-2">{{ now()->translatedFormat('l, d F Y') }}</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Selamat datang, {{ auth()->user()->name }}</h1>
<div class="elite-rule"></div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-7">
<div class="bg-white border-l-4 border-blue-600 p-5">
<div class="elite-kicker text-[.6rem]">Rombel Saya (Wali)</div>
<div class="font-display text-2xl ink-primary mt-2">{{ $myClasses->count() }}</div>
</div>
<div class="bg-white border-l-4 border-green-600 p-5">
<div class="elite-kicker text-[.6rem]">Mengajar Hari Ini</div>
<div class="font-display text-2xl ink-primary mt-2">{{ $todaySchedule->count() }}</div>
</div>
<div class="bg-white border-l-4 border-purple-600 p-5">
<div class="elite-kicker text-[.6rem]">Live Class Akan Datang</div>
<div class="font-display text-2xl ink-primary mt-2">{{ $upcomingLive->count() }}</div>
</div>
<div class="bg-white border-l-4 border-yellow-600 p-5">
<div class="elite-kicker text-[.6rem]">RPP Terbaru</div>
<div class="font-display text-2xl ink-primary mt-2">{{ $recentRpp->count() }}</div>
</div>
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-6">
<div class="bg-white border border-rule p-7">
<h3 class="elite-h3 text-lg ink-primary mb-4">📅 Jadwal Mengajar Hari Ini</h3>
@if($todaySchedule->isEmpty())
<p class="font-serif text-sm text-gray-500 italic">Tidak ada jadwal mengajar hari ini.</p>
@else
<div class="space-y-2">
@foreach($todaySchedule as $sl)
<div class="flex justify-between items-center p-3 border border-rule">
<div>
<div class="font-serif font-semibold ink-primary">{{ $sl->subject?->name }}</div>
<div class="text-xs text-gray-500">{{ $sl->classSection?->classRoom?->name }} {{ $sl->classSection?->section?->name }} {{ $sl->room ? '· '.$sl->room : '' }}</div>
</div>
<div class="font-mono text-sm ink-secondary">{{ \Carbon\Carbon::parse($sl->start_time)->format('H:i') }}–{{ \Carbon\Carbon::parse($sl->end_time)->format('H:i') }}</div>
</div>
@endforeach
</div>
@endif
</div>

<div class="bg-white border border-rule p-7">
<h3 class="elite-h3 text-lg ink-primary mb-4">🎓 Rombel Sebagai Wali Kelas</h3>
@if($myClasses->isEmpty())
<p class="font-serif text-sm text-gray-500 italic">Anda belum di-assign sebagai wali kelas.</p>
@else
<div class="space-y-2">
@foreach($myClasses as $c)
<a href="{{ route('teacher.my-class', $c) }}" class="flex justify-between items-center p-3 border border-rule hover:border-[var(--c-accent)]">
<div>
<div class="font-serif font-semibold">{{ $c->classRoom?->name }} {{ $c->section?->name }}</div>
<div class="text-xs text-gray-500">{{ $c->students_count }} siswa</div>
</div>
<span class="text-xs ink-secondary">→</span>
</a>
@endforeach
</div>
@endif
</div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
<div class="bg-white border border-rule p-7">
<h3 class="elite-h3 text-lg ink-primary mb-4">📝 Aksi Cepat</h3>
<div class="grid grid-cols-2 gap-3">
<a href="{{ route('admin.attendance.index') }}" class="bg-gray-50 hover:bg-gray-100 p-4 text-center border border-rule">
<div class="elite-kicker text-[.6rem] mb-1">Input</div><div class="font-serif text-sm">Absensi</div>
</a>
<a href="{{ route('admin.exams.index') }}" class="bg-gray-50 hover:bg-gray-100 p-4 text-center border border-rule">
<div class="elite-kicker text-[.6rem] mb-1">Input</div><div class="font-serif text-sm">Nilai Ujian</div>
</a>
<a href="{{ route('admin.lesson-plan.index') }}" class="bg-gray-50 hover:bg-gray-100 p-4 text-center border border-rule">
<div class="elite-kicker text-[.6rem] mb-1">Buat</div><div class="font-serif text-sm">RPP</div>
</a>
<a href="{{ route('admin.live-class.index') }}" class="bg-gray-50 hover:bg-gray-100 p-4 text-center border border-rule">
<div class="elite-kicker text-[.6rem] mb-1">Jadwalkan</div><div class="font-serif text-sm">Live Class</div>
</a>
</div>
</div>

<div class="bg-white border border-rule p-7">
<h3 class="elite-h3 text-lg ink-primary mb-4">📚 RPP Terbaru</h3>
@if($recentRpp->isEmpty())
<p class="font-serif text-sm text-gray-500 italic">Belum ada RPP.</p>
@else
@foreach($recentRpp as $r)
<div class="p-3 border-b border-rule last:border-0">
<div class="flex justify-between">
<div class="flex-1">
<div class="font-serif font-semibold ink-primary">{{ $r->title }}</div>
<div class="text-xs text-gray-500">{{ $r->subject?->name }} · {{ $r->classSection?->classRoom?->name }}</div>
</div>
<span class="elite-kicker text-[.55rem]">{{ $r->status }}</span>
</div>
</div>
@endforeach
@endif
</div>
</div>

@endsection

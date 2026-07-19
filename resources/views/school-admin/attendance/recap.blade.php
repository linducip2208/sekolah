@extends('layouts.school-admin')
@section('title', 'Rekap Absensi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Recapitulatio Mensilis</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Rekap Bulanan Absensi</h1>
    <div class="elite-rule"></div>
    <a href="{{ route('admin.attendance.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mt-3 inline-block">← Input Absensi Harian</a>
</div>

<form method="GET" class="bg-white border border-rule p-5 mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Bulan</label>
        <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
    </div>
    <div class="md:col-span-2">
        <label class="elite-kicker text-[.6rem] block mb-1">Rombel</label>
        <select name="class_section_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— pilih rombel —</option>
            @foreach($classSections as $cs)
                <option value="{{ $cs->id }}" @selected($classSectionId == $cs->id)>
                    {{ $cs->classRoom?->name }} {{ $cs->section?->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="flex items-end">
        <button class="btn-elite w-full" style="padding:.6rem 1rem;font-size:.65rem;">Tampilkan</button>
    </div>
</form>

@if($recap->count() > 0)
    <div class="bg-white border border-rule overflow-hidden">
        <div class="bg-[var(--c-primary)] text-white px-4 py-3">
            <span class="elite-kicker text-[.6rem]">{{ $month->translatedFormat('F Y') }} · {{ $recap->count() }} siswa</span>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">NIS</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
                    <th class="text-center px-3 py-3 elite-kicker text-[.6rem] text-green-700">Hadir</th>
                    <th class="text-center px-3 py-3 elite-kicker text-[.6rem] text-yellow-700">Telat</th>
                    <th class="text-center px-3 py-3 elite-kicker text-[.6rem] text-blue-700">Izin</th>
                    <th class="text-center px-3 py-3 elite-kicker text-[.6rem] text-red-700">Absen</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recap as $s)
                    <tr class="border-t border-rule">
                        <td class="px-4 py-3 font-mono text-xs">{{ $s->admission_no }}</td>
                        <td class="px-4 py-3 font-serif">{{ $s->user?->name }}</td>
                        <td class="text-center px-3 py-3 font-mono">{{ $s->present_count }}</td>
                        <td class="text-center px-3 py-3 font-mono">{{ $s->late_count }}</td>
                        <td class="text-center px-3 py-3 font-mono">{{ $s->on_leave_count }}</td>
                        <td class="text-center px-3 py-3 font-mono font-bold {{ $s->absent_count > 0 ? 'text-red-700' : '' }}">{{ $s->absent_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@elseif($classSectionId)
    <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">
        Belum ada data absensi atau siswa di rombel ini bulan {{ $month->translatedFormat('F Y') }}.
    </div>
@else
    <div class="bg-white border border-rule p-10 text-center text-gray-500 font-serif">
        Pilih rombel & bulan untuk melihat rekap.
    </div>
@endif

@endsection

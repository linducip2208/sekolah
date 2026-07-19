@extends('layouts.school-admin')
@section('title', 'Absensi Harian')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Praesentia Quotidiana</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Absensi Harian</h1>
    <div class="elite-rule"></div>
    <a href="{{ route('admin.attendance.recap') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mt-3 inline-block">Lihat Rekap Bulanan →</a>
</div>

<form method="GET" class="bg-white border border-rule p-5 mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Tanggal</label>
        <input type="date" name="date" value="{{ $date }}" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
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

@if($classSectionId && $students->count() > 0)
    <form method="POST" action="{{ route('admin.attendance.save') }}">
        @csrf
        <input type="hidden" name="class_section_id" value="{{ $classSectionId }}">
        <input type="hidden" name="date" value="{{ $date }}">

        <div class="bg-white border border-rule overflow-hidden">
            <div class="bg-[var(--c-primary)] text-white px-4 py-3 flex justify-between items-center">
                <span class="elite-kicker text-[.6rem]">{{ $students->count() }} siswa · {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</span>
                <div class="flex gap-2 text-xs">
                    <button type="button" onclick="setAll('present')" class="px-2 py-1 bg-green-700 hover:bg-green-600">All Hadir</button>
                    <button type="button" onclick="setAll('absent')" class="px-2 py-1 bg-red-700 hover:bg-red-600">All Absen</button>
                </div>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">NIS</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $s)
                        @php
                            $rec = $existing->get($s->id);
                            $current = $rec?->status ?? 'present';
                        @endphp
                        <tr class="border-t border-rule">
                            <td class="px-4 py-3 font-mono text-xs">{{ $s->admission_no }}</td>
                            <td class="px-4 py-3 font-serif font-semibold">{{ $s->user?->name }}</td>
                            <td class="px-4 py-3">
                                <select name="attendance[{{ $s->id }}]" data-attendance class="border-2 border-rule px-2 py-1.5 text-sm font-serif">
                                    <option value="present" @selected($current==='present')>Hadir</option>
                                    <option value="absent" @selected($current==='absent')>Absen</option>
                                    <option value="late" @selected($current==='late')>Terlambat</option>
                                    <option value="half_day" @selected($current==='half_day')>Setengah Hari</option>
                                    <option value="on_leave" @selected($current==='on_leave')>Izin</option>
                                </select>
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="notes[{{ $s->id }}]" maxlength="255"
                                       value="{{ $rec?->note }}"
                                       class="w-full border border-rule px-2 py-1 text-xs font-serif"
                                       placeholder="opsional">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-4 bg-gray-50 text-right">
                <button type="submit" class="btn-elite">Simpan Absensi</button>
            </div>
        </div>
    </form>

    <script>
    function setAll(status) {
        document.querySelectorAll('select[data-attendance]').forEach(s => s.value = status);
    }
    </script>
@elseif($classSectionId)
    <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">
        Belum ada siswa di rombel ini.
    </div>
@else
    <div class="bg-white border border-rule p-10 text-center text-gray-500 font-serif">
        Pilih rombel & tanggal untuk menginput absensi.
    </div>
@endif

@endsection

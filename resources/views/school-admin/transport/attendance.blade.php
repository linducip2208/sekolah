@extends('layouts.school-admin')
@section('title', 'Absensi Transportasi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Adventus</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Absensi Transportasi</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Hari ini: {{ $summary['present'] }} hadir · {{ $summary['absent'] }} tidak hadir dari {{ $summary['total'] }} catatan.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif

<form method="GET" class="bg-white border border-rule p-4 mb-6 grid md:grid-cols-4 gap-2 items-end">
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Rute</label>
        <select name="route_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— pilih rute —</option>
            @foreach($routes as $r)<option value="{{ $r->id }}" @selected($routeId == $r->id)>{{ $r->name }}</option>@endforeach
        </select>
    </div>
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Tanggal</label>
        <input type="date" name="date" value="{{ $date }}" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
    </div>
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Arah</label>
        <select name="direction" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="to_school" @selected($direction === 'to_school')>Berangkat (ke sekolah)</option>
            <option value="from_school" @selected($direction === 'from_school')>Pulang (dari sekolah)</option>
        </select>
    </div>
    <button class="btn-elite" style="padding:.5rem 1rem;font-size:.7rem;">Muat</button>
</form>

@if($routeId && $students->isNotEmpty())
<form method="POST" action="{{ route('admin.transport.attendance.store') }}">@csrf
    <input type="hidden" name="transport_route_id" value="{{ $routeId }}">
    <input type="hidden" name="date" value="{{ $date }}">
    <input type="hidden" name="direction" value="{{ $direction }}">

    <div class="bg-white border border-rule overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-[var(--c-primary)] text-white"><tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">NIS</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Halte</th>
                <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Kehadiran</th>
            </tr></thead>
            <tbody>
                @foreach($students as $st)
                @php $rec = $existing->get($st->student_id); @endphp
                <tr class="border-t border-rule">
                    <td class="px-4 py-3 font-mono text-xs">{{ $st->student?->admission_no }}</td>
                    <td class="px-4 py-3 font-serif">{{ $st->student?->user?->name }}</td>
                    <td class="px-4 py-3 text-xs">{{ $st->stop?->stop_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <label class="inline-flex items-center gap-1 mr-3"><input type="radio" name="attendance[{{ $st->student_id }}]" value="present" @checked(!$rec || $rec->status === 'present') class="w-4 h-4"> <span class="text-xs text-green-700">Hadir</span></label>
                        <label class="inline-flex items-center gap-1"><input type="radio" name="attendance[{{ $st->student_id }}]" value="absent" @checked($rec && $rec->status === 'absent') class="w-4 h-4"> <span class="text-xs text-red-700">Tidak</span></label>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-4 bg-gray-50 text-right">
            <button class="btn-elite">Simpan Kehadiran</button>
        </div>
    </div>
</form>
@elseif($routeId)
    <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Tidak ada siswa terdaftar di rute ini.</div>
@else
    <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Pilih rute untuk melihat siswa.</div>
@endif

@endsection

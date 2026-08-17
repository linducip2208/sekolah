@extends('layouts.school-admin')
@section('title', 'Jadwal Sopir')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Schedula</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Jadwal Sopir</h1>
    <div class="elite-rule"></div>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif

<form method="GET" class="bg-white border border-rule p-4 mb-4 flex gap-2 items-end">
    <div><label class="elite-kicker text-[.6rem] block mb-1">Tanggal</label><input type="date" name="date" value="{{ $date }}" class="border-2 border-rule px-3 py-2 font-mono text-sm"></div>
    <button class="btn-elite" style="padding:.5rem 1rem;font-size:.7rem;">Lihat</button>
</form>

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Jadwal</summary>
    <form method="POST" action="{{ route('admin.transport.driver-schedule.store') }}" class="px-5 py-4 border-t border-rule grid md:grid-cols-3 gap-2">@csrf
        <select name="transport_route_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— rute —</option>
            @foreach($routes as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach
        </select>
        <select name="vehicle_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— kendaraan —</option>
            @foreach($vehicles as $v)<option value="{{ $v->id }}">{{ $v->registration_no }}</option>@endforeach
        </select>
        <input name="driver_name" maxlength="200" placeholder="Nama sopir" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <input type="date" name="date" required value="{{ $date }}" class="border-2 border-rule px-3 py-2 font-mono text-sm">
        <select name="shift" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="morning">Pagi (berangkat)</option>
            <option value="afternoon">Sore (pulang)</option>
        </select>
        <input name="note" maxlength="255" placeholder="Catatan" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <div class="md:col-span-3"><button class="btn-elite">Simpan</button></div>
    </form>
</details>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white"><tr>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Rute</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kendaraan</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Sopir</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Shift</th>
            <th class="px-4 py-3"></th>
        </tr></thead>
        <tbody>
            @forelse($schedules as $s)
            <tr class="border-t border-rule">
                <td class="px-4 py-3 font-serif">{{ $s->route?->name }}</td>
                <td class="px-4 py-3 font-mono text-xs">{{ $s->vehicle?->registration_no ?? '—' }}</td>
                <td class="px-4 py-3">{{ $s->driver_name ?? $s->vehicle?->driver_name ?? '—' }}</td>
                <td class="px-4 py-3 text-center text-xs">{{ $s->shift === 'morning' ? 'Pagi' : 'Sore' }}</td>
                <td class="px-4 py-3 text-right">
                    <form method="POST" action="{{ route('admin.transport.driver-schedule.destroy', $s) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada jadwal sopir untuk tanggal ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection

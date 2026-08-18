@extends('layouts.school-admin')
@section('title', 'Absensi Asrama')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Asrama</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Absensi Asrama</h1><div class="elite-rule"></div></div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<form method="POST" action="{{ route('admin.hostel.attendances.store') }}" class="space-y-3">@csrf
<select name="hostel_room_id" required class="w-full border-2 border-rule px-3 py-2 text-sm"><option value="">— Kamar —</option>
@foreach($rooms as $r)<option value="{{ $r->id }}">{{ $r->hostel->name }} — {{ $r->room_no }}</option>@endforeach</select>
<select name="student_id" required class="w-full border-2 border-rule px-3 py-2 text-sm"><option value="">— Siswa —</option>
@foreach(\App\Models\Academic\Student::where('school_id', auth()->user()->school_id)->with('user:id,name')->get() as $s)<option value="{{ $s->id }}">{{ $s->user->name }}</option>@endforeach</select>
<input type="date" name="date" required value="{{ now()->format('Y-m-d') }}" class="w-full border-2 border-rule px-3 py-2 text-sm">
<select name="status" required class="w-full border-2 border-rule px-3 py-2 text-sm">
<option value="present">Hadir</option><option value="absent">Tidak Hadir</option><option value="late">Terlambat</option><option value="permission">Izin</option></select>
<input type="time" name="check_in_time" class="w-full border-2 border-rule px-3 py-2 text-sm">
<input type="time" name="check_out_time" class="w-full border-2 border-rule px-3 py-2 text-sm">
<textarea name="note" maxlength="1000" rows="2" placeholder="Catatan (opsional)" class="w-full border-2 border-rule px-3 py-2 text-sm"></textarea>
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Catat Absensi</button>
</form></div></div>

<div class="lg:col-span-2"><div class="bg-white border border-rule overflow-hidden">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kamar</th>
<th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Status</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Waktu</th>
</tr></thead><tbody>
@forelse($attendances as $a)<tr class="border-t border-rule">
<td class="px-4 py-3 font-mono text-sm">{{ $a->date->format('d M Y') }}</td>
<td class="px-4 py-3 text-sm font-medium">{{ $a->student->user->name }}</td>
<td class="px-4 py-3 text-sm">{{ $a->room->room_no ?? '—' }}</td>
<td class="px-4 py-3 text-center">
    @if($a->status==='present')<span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700">Hadir</span>
    @elseif($a->status==='absent')<span class="text-xs px-2 py-0.5 rounded bg-red-100 text-red-700">Tidak Hadir</span>
    @elseif($a->status==='late')<span class="text-xs px-2 py-0.5 rounded bg-yellow-100 text-yellow-700">Terlambat</span>
    @else<span class="text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-700">Izin</span>@endif
</td>
<td class="px-4 py-3 text-xs text-gray-500 font-mono">
    @if($a->check_in_time)In: {{ $a->check_in_time }}@endif
    @if($a->check_out_time) · Out: {{ $a->check_out_time }}@endif
</td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada absensi.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection

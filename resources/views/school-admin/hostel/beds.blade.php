@extends('layouts.school-admin')
@section('title', 'Tempat Tidur — ' . $room->room_no)
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<a href="{{ route('admin.hostel.rooms.index', $room->hostel) }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kamar {{ $room->hostel->name }}</a>
<div class="mb-7"><div class="elite-kicker mb-2">{{ $room->room_no }}</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Tempat Tidur</h1><div class="elite-rule"></div></div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1">
<div class="bg-white border border-rule p-6 sticky top-6 mb-4">
<form method="POST" action="{{ route('admin.hostel.beds.store', $room) }}" class="space-y-3">@csrf
<input name="bed_no" required maxlength="20" placeholder="No Tempat Tidur (101-01)" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Tambah Tempat Tidur</button>
</form></div>

<div class="bg-white border border-rule p-6">
<h3 class="font-semibold text-sm mb-3">Checkout Siswa</h3>
<form method="POST" action="{{ route('admin.hostel.checkout') }}" class="space-y-3" onsubmit="return confirm('Checkout siswa dari asrama?')">@csrf
<select name="student_id" required class="w-full border-2 border-rule px-3 py-2 text-sm">
<option value="">— Pilih Siswa —</option>
@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->user->name }}</option>@endforeach
</select>
<button class="btn-elite w-full bg-red-600 hover:bg-red-700" style="padding:.6rem;font-size:.65rem;">Checkout</button>
</form></div>
</div>

<div class="lg:col-span-2"><div class="bg-white border border-rule overflow-hidden">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">No Tempat Tidur</th>
<th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Status</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Aksi</th>
</tr></thead><tbody>
@forelse($beds as $b)<tr class="border-t border-rule">
<td class="px-4 py-3 font-mono font-semibold">{{ $b->bed_no }}</td>
<td class="px-4 py-3 text-center">
    @if($b->status==='available')<span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700">Tersedia</span>
    @elseif($b->status==='occupied')<span class="text-xs px-2 py-0.5 rounded bg-red-100 text-red-700">Terisi</span>
    @else<span class="text-xs px-2 py-0.5 rounded bg-yellow-100 text-yellow-700">Maintenance</span>@endif
</td>
<td class="px-4 py-3 text-sm">{{ $b->student->user->name ?? '—' }}</td>
<td class="px-4 py-3 text-right">
    @if($b->status==='available')
    <form method="POST" action="{{ route('admin.hostel.beds.allocate') }}" class="inline-flex items-center gap-1">@csrf
        <input type="hidden" name="hostel_bed_id" value="{{ $b->id }}">
        <select name="student_id" required class="border border-rule rounded px-2 py-1 text-xs">
            <option value="">Siswa</option>
            @foreach($students as $s)<option value="{{ $s->id }}">{{ $s->user->name }}</option>@endforeach
        </select>
        <button class="text-xs text-green-700 hover:underline">Alokasi</button>
    </form>
    @elseif($b->status==='occupied')
    <form method="POST" action="{{ route('admin.hostel.beds.deallocate', $b) }}" class="inline" onsubmit="return confirm('Lepas aloksi?')">@csrf
        <button class="text-xs text-red-700 hover:underline">Lepas</button>
    </form>
    @endif
</td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada tempat tidur.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection

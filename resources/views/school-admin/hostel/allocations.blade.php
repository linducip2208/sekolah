@extends('layouts.school-admin')
@section('title', 'Alokasi Kamar')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<a href="{{ route('admin.hostel.list.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Asrama</a>
<div class="mb-7"><div class="elite-kicker mb-2">Allocationes</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Alokasi Kamar Asrama</h1><div class="elite-rule"></div></div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Alokasikan Siswa</summary>
<form method="POST" action="{{ route('admin.hostel.allocations.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-2 gap-3">@csrf
<select name="student_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— siswa (yg has_hostel) —</option>
@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->user?->name }}</option>@endforeach
</select>
<select name="hostel_room_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— kamar tersedia —</option>
@foreach($rooms as $r)<option value="{{ $r->id }}">{{ $r->hostel?->name }} — Kamar {{ $r->room_no }} ({{ $r->occupied }}/{{ $r->capacity }})</option>@endforeach
</select>
<input type="date" name="from_date" required value="{{ now()->toDateString() }}" class="border-2 border-rule px-3 py-2 text-sm">
<input type="date" name="to_date" placeholder="Sampai (opsional)" class="border-2 border-rule px-3 py-2 text-sm">
<div class="md:col-span-2"><button class="btn-elite">Alokasikan</button></div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Asrama / Kamar</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Periode</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
</tr></thead><tbody>
@forelse($allocations as $a)<tr class="border-t border-rule">
<td class="px-3 py-3 font-serif">{{ $a->student?->user?->name }}</td>
<td class="px-3 py-3 text-xs">{{ $a->room?->hostel?->name }} — Kamar {{ $a->room?->room_no }}</td>
<td class="px-3 py-3 text-xs">{{ $a->from_date?->format('d M Y') }} → {{ $a->to_date?->format('d M Y') ?? 'aktif' }}</td>
<td class="px-3 py-3"><span class="text-xs {{ $a->is_active ? 'text-green-700' : 'text-gray-500' }}">{{ $a->is_active ? 'Aktif' : 'Off' }}</span></td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada alokasi.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $allocations->links() }}</div>
@endsection

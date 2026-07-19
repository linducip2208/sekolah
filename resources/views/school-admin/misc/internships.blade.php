@extends('layouts.school-admin')
@section('title', 'Magang / Internship')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><h1 class="elite-h1 text-3xl ink-primary mb-2">Penempatan Magang</h1><div class="elite-rule"></div></div>

<details class="mb-6 bg-white border border-rule"><summary class="px-5 py-4 cursor-pointer elite-kicker">+ Catat Penempatan</summary>
<form method="POST" action="{{ route('admin.misc.internships.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-2 gap-3">@csrf
<select name="student_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— siswa —</option>
@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->user?->name }}</option>@endforeach
</select>
<input name="company_name" required maxlength="200" placeholder="Nama Perusahaan" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<input name="position" maxlength="200" placeholder="Posisi/Divisi" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<input name="mentor_name" maxlength="200" placeholder="Nama Mentor" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<input name="mentor_phone" maxlength="30" placeholder="HP Mentor" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<div class="grid grid-cols-2 gap-2">
<input type="date" name="start_date" required class="border-2 border-rule px-3 py-2 text-sm">
<input type="date" name="end_date" required class="border-2 border-rule px-3 py-2 text-sm">
</div>
<div class="md:col-span-2"><button class="btn-elite">Simpan</button></div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Perusahaan</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Posisi</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Periode</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
</tr></thead><tbody>
@forelse($internships as $i)<tr class="border-t border-rule">
<td class="px-3 py-3 font-serif">{{ $i->student_name }}</td>
<td class="px-3 py-3 text-xs">{{ $i->company_name }}</td>
<td class="px-3 py-3 text-xs">{{ $i->position ?? '—' }}</td>
<td class="px-3 py-3 text-xs">{{ \Carbon\Carbon::parse($i->start_date)->format('d M') }} → {{ \Carbon\Carbon::parse($i->end_date)->format('d M Y') }}</td>
<td class="px-3 py-3"><span class="elite-kicker text-[.55rem]">{{ $i->status }}</span></td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada penempatan magang.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $internships->links() }}</div>
@endsection

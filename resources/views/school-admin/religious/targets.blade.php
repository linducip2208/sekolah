@extends('layouts.school-admin')
@section('title', 'Target Hafalan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7 flex justify-between items-end"><div>
<div class="elite-kicker mb-2">Memoria Quran</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Target Hafalan</h1><div class="elite-rule"></div></div>
<a href="{{ route('admin.religious.progress.index') }}" class="btn-elite-ghost">Progress Hafalan →</a></div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<form method="POST" action="{{ route('admin.religious.targets.store') }}" class="space-y-3">@csrf
<input name="name" required maxlength="200" placeholder="Juz 30 / Yasin / dll." class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<input type="date" name="start_date" required class="w-full border-2 border-rule px-3 py-2 text-sm">
<input type="date" name="deadline" required class="w-full border-2 border-rule px-3 py-2 text-sm">
<input name="target_ranges" placeholder="Surah & Ayat (mis. An-Naba 1-40)" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan Target</button>
</form></div></div>

<div class="lg:col-span-2"><div class="bg-white border border-rule overflow-hidden">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Mulai</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Deadline</th>
<th></th></tr></thead><tbody>
@forelse($targets as $t)<tr class="border-t border-rule">
<td class="px-4 py-3 font-serif font-semibold">{{ $t->name }}</td>
<td class="px-4 py-3 text-xs">{{ $t->start_date?->format('d M Y') }}</td>
<td class="px-4 py-3 text-xs">{{ $t->deadline?->format('d M Y') }}</td>
<td class="px-4 py-3 text-right"><form method="POST" action="{{ route('admin.religious.targets.destroy', $t) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form></td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada target.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection

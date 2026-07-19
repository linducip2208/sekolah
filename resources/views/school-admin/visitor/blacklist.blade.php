@extends('layouts.school-admin')
@section('title', 'Blacklist Tamu')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<a href="{{ route('admin.visitor.logs.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Log Tamu</a>
<div class="mb-7"><div class="elite-kicker mb-2">Index Vetatorum</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Blacklist Pengunjung</h1><div class="elite-rule"></div></div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<form method="POST" action="{{ route('admin.visitor.blacklist.store') }}" class="space-y-3">@csrf
<input name="full_name" required maxlength="200" placeholder="Nama lengkap" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<input name="id_number" required maxlength="50" placeholder="No. KTP" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
<textarea name="reason" rows="3" required maxlength="500" placeholder="Alasan blacklist" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Tambahkan</button>
</form></div></div>

<div class="lg:col-span-2"><div class="bg-white border border-rule overflow-hidden">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">ID</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Alasan</th>
<th></th></tr></thead><tbody>
@forelse($list as $b)<tr class="border-t border-rule">
<td class="px-4 py-3 font-serif font-semibold">{{ $b->full_name }}</td>
<td class="px-4 py-3 font-mono text-xs">{{ $b->id_number }}</td>
<td class="px-4 py-3 text-xs">{{ $b->reason }}</td>
<td class="px-4 py-3 text-right"><form method="POST" action="{{ route('admin.visitor.blacklist.destroy', $b) }}" class="inline" onsubmit="return confirm('Hapus dari blacklist?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form></td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada blacklist.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection

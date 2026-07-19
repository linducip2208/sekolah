@extends('super-admin.layout')
@section('title', 'Admin Yayasan')
@section('content')
<a href="{{ route('super.foundations.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Yayasan</a>
<div class="mb-7"><div class="elite-kicker mb-2">{{ $foundation->name }}</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Admin Yayasan</h1><div class="elite-rule"></div></div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<h3 class="elite-h3 text-base ink-primary mb-3">Tambah Admin</h3>
<form method="POST" action="{{ route('super.foundations.admins.store', $foundation) }}" class="space-y-3">@csrf
<select name="user_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— pilih user —</option>
@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>@endforeach
</select>
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Tambahkan</button>
</form></div></div>

<div class="lg:col-span-2"><div class="bg-white border border-rule overflow-hidden">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Email</th>
<th></th></tr></thead><tbody>
@forelse($admins as $a)<tr class="border-t border-rule">
<td class="px-4 py-3 font-serif font-semibold">{{ $a->user?->name }}</td>
<td class="px-4 py-3 text-xs font-mono">{{ $a->user?->email }}</td>
<td class="px-4 py-3 text-right"><form method="POST" action="{{ route('super.foundations.admins.destroy', $a) }}" class="inline" onsubmit="return confirm('Hapus admin?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form></td>
</tr>@empty<tr><td colspan="3" class="p-10 text-center text-gray-500 italic font-serif">Belum ada admin yayasan.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection

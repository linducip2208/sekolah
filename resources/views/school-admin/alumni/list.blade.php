@extends('layouts.school-admin')
@section('title', 'Alumni')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Alumni</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Jaringan Alumni</h1><div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">{{ $alumni->total() }} alumni terdaftar.</p></div>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tahun Lulus</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Nama</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Posisi</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Perusahaan</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Lokasi</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Status</th>
<th></th></tr></thead><tbody>
@forelse($alumni as $a)<tr class="border-t border-rule">
<td class="px-3 py-3 font-mono">{{ $a->graduation_year }}</td>
<td class="px-3 py-3"><div class="font-serif font-semibold">{{ $a->user?->name }}</div><div class="text-xs text-gray-500">{{ $a->user?->email }}</div></td>
<td class="px-3 py-3 text-xs">{{ $a->current_position ?? '—' }}</td>
<td class="px-3 py-3 text-xs">{{ $a->current_company ?? '—' }}</td>
<td class="px-3 py-3 text-xs">{{ $a->city ?? '' }}{{ $a->country ? ', '.$a->country : '' }}</td>
<td class="px-3 py-3 text-center">
@if($a->verified)<span class="text-xs text-green-700">✓ Verified</span>@else<span class="text-xs text-yellow-700">Pending</span>@endif
</td>
<td class="px-3 py-3 text-right"><form method="POST" action="{{ route('admin.alumni.verify', $a) }}" class="inline">@csrf<button class="text-xs underline ink-secondary hover:ink-accent">Toggle</button></form></td>
</tr>@empty<tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada alumni terdaftar.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $alumni->links() }}</div>
@endsection

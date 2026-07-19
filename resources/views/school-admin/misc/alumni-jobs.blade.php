@extends('layouts.school-admin')
@section('title', 'Lowongan Alumni')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><h1 class="elite-h1 text-3xl ink-primary mb-2">Lowongan Kerja Alumni</h1><div class="elite-rule"></div></div>
<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Posisi</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Perusahaan</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Lokasi</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tipe</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Berakhir</th>
</tr></thead><tbody>
@forelse($jobs as $j)<tr class="border-t border-rule">
<td class="px-3 py-3 font-serif font-semibold">{{ $j->title }}</td>
<td class="px-3 py-3 text-xs">{{ $j->company }}</td>
<td class="px-3 py-3 text-xs">{{ $j->location }}</td>
<td class="px-3 py-3"><span class="elite-kicker text-[.55rem]">{{ $j->type }}</span></td>
<td class="px-3 py-3 text-xs">{{ \Carbon\Carbon::parse($j->expires_at)->format('d M Y') }}</td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada lowongan.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $jobs->links() }}</div>
@endsection

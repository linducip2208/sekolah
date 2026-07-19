@extends('layouts.school-admin')
@section('title', 'Trip Bus')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Itinera Vehicularum</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Trip Bus / Antar Jemput</h1><div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">Riwayat trip kendaraan sekolah dengan tracking GPS.</p></div>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Kendaraan</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Rute</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
</tr></thead><tbody>
@forelse($trips as $t)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs">{{ $t->created_at?->format('d M Y H:i') }}</td>
<td class="px-3 py-3 text-xs">Vehicle #{{ $t->vehicle_id ?? '—' }}</td>
<td class="px-3 py-3 text-xs">Route #{{ $t->transport_route_id ?? '—' }}</td>
<td class="px-3 py-3"><span class="elite-kicker text-[.55rem]">{{ $t->status ?? '—' }}</span></td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada trip.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $trips->links() }}</div>
@endsection

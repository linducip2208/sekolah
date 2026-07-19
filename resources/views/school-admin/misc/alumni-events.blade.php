@extends('layouts.school-admin')
@section('title', 'Acara Alumni')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><h1 class="elite-h1 text-3xl ink-primary mb-2">Acara Alumni</h1><div class="elite-rule"></div></div>
<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Judul</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Lokasi</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Tiket</th>
</tr></thead><tbody>
@forelse($events as $e)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs">{{ \Carbon\Carbon::parse($e->starts_at)->format('d M Y') }}</td>
<td class="px-3 py-3 font-serif font-semibold">{{ $e->title }}</td>
<td class="px-3 py-3 text-xs">{{ $e->venue }}, {{ $e->city }}</td>
<td class="px-3 py-3 text-right font-mono">{{ $e->ticket_price ? 'Rp '.number_format($e->ticket_price/100, 0, ',', '.') : 'Free' }}</td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada acara alumni.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $events->links() }}</div>
@endsection

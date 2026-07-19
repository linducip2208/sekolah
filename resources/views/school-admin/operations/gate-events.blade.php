@extends('layouts.school-admin')
@section('title', 'Log Scan Gerbang')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<a href="{{ route('admin.operations.gate-devices.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Devices</a>
<div class="mb-7"><div class="elite-kicker mb-2">Acta Portarum</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Log Scan Gerbang</h1><div class="elite-rule"></div></div>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Waktu</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Device</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">User</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Arah</th>
</tr></thead><tbody>
@forelse($events as $e)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs font-mono">{{ $e->scanned_at?->format('d M Y H:i:s') }}</td>
<td class="px-3 py-3 text-xs">{{ $e->device?->name }}</td>
<td class="px-3 py-3 font-serif">{{ $e->user?->name }}</td>
<td class="px-3 py-3"><span class="text-xs px-2 py-0.5 rounded {{ $e->direction==='in' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $e->direction === 'in' ? '↓ Masuk' : '↑ Keluar' }}</span></td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada event scan.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $events->links() }}</div>
@endsection

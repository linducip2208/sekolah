@extends('layouts.parent')
@section('title', 'Absensi Saya')
@section('content')
@include('student-portal._nav')
<div class="mb-6"><h1 class="elite-h1 text-2xl ink-primary mb-2">Riwayat Absensi</h1><div class="elite-rule"></div></div>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Catatan</th>
</tr></thead><tbody>
@forelse($records as $r)<tr class="border-t border-rule">
<td class="px-4 py-3 font-mono text-xs">{{ $r->date->format('d M Y') }} ({{ $r->date->translatedFormat('l') }})</td>
<td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded
{{ $r->status === 'present' ? 'bg-green-100 text-green-700' : '' }}
{{ $r->status === 'late' ? 'bg-yellow-100 text-yellow-700' : '' }}
{{ $r->status === 'absent' ? 'bg-red-100 text-red-700' : '' }}
{{ $r->status === 'on_leave' ? 'bg-blue-100 text-blue-700' : '' }}">{{ $r->status }}</span></td>
<td class="px-4 py-3 text-xs">{{ $r->note ?? '—' }}</td>
</tr>@empty<tr><td colspan="3" class="p-10 text-center text-gray-500 italic font-serif">Belum ada catatan absensi.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $records->links() }}</div>
@endsection

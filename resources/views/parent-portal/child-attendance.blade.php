@extends('layouts.parent')
@section('title', 'Absensi - '.$student->user?->name)
@section('content')
<a href="{{ route('portal.child', $student) }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← {{ $student->user?->name }}</a>
@include('parent-portal._child_tabs')

<h2 class="elite-h2 text-2xl ink-primary mb-4">Riwayat Absensi</h2>
<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Hari</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Catatan</th>
</tr></thead><tbody>
@forelse($records as $r)<tr class="border-t border-rule">
<td class="px-4 py-3 font-mono text-xs">{{ $r->date->format('d M Y') }}</td>
<td class="px-4 py-3 text-xs">{{ $r->date->translatedFormat('l') }}</td>
<td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded
{{ $r->status === 'present' ? 'bg-green-100 text-green-700' : '' }}
{{ $r->status === 'late' ? 'bg-yellow-100 text-yellow-700' : '' }}
{{ $r->status === 'absent' ? 'bg-red-100 text-red-700' : '' }}
{{ $r->status === 'on_leave' ? 'bg-blue-100 text-blue-700' : '' }}
{{ $r->status === 'half_day' ? 'bg-orange-100 text-orange-700' : '' }}">{{ $r->status }}</span></td>
<td class="px-4 py-3 text-xs text-gray-700">{{ $r->note ?? '—' }}</td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada catatan absensi.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $records->links() }}</div>
@endsection

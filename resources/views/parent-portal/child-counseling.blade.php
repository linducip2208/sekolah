@extends('layouts.parent')
@section('title', 'Konseling - '.$student->user?->name)
@section('content')
<a href="{{ route('portal.child', $student) }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← {{ $student->user?->name }}</a>
@include('parent-portal._child_tabs')

<h2 class="elite-h2 text-2xl ink-primary mb-4">Sesi Konseling BP/BK</h2>
<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Jadwal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tipe</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Konselor</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
</tr></thead><tbody>
@forelse($sessions as $s)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs font-mono">{{ $s->scheduled_at?->format('d M Y H:i') }}</td>
<td class="px-3 py-3 text-xs"><span class="elite-kicker text-[.55rem]">{{ $s->type }}</span></td>
<td class="px-3 py-3 text-xs">{{ $s->counselor?->name }}</td>
<td class="px-3 py-3"><span class="text-xs">{{ $s->status }}</span></td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada sesi konseling.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $sessions->links() }}</div>
<p class="text-xs text-gray-500 italic mt-3">Catatan konseling bersifat rahasia dan hanya bisa diakses konselor.</p>
@endsection

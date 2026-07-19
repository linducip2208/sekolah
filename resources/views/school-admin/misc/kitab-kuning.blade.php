@extends('layouts.school-admin')
@section('title', 'Kitab Kuning Progress')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><h1 class="elite-h1 text-3xl ink-primary mb-2">Progress Kitab Kuning</h1><div class="elite-rule"></div></div>
<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Kitab</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Bab Sekarang</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Hal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Sesi Terakhir</th>
</tr></thead><tbody>
@forelse($progress as $p)<tr class="border-t border-rule">
<td class="px-3 py-3 font-serif">{{ $p->student_name }}</td>
<td class="px-3 py-3 text-xs">{{ $p->kitab_name }}</td>
<td class="px-3 py-3 text-xs">{{ $p->current_bab ?? '—' }}</td>
<td class="px-3 py-3 text-right font-mono">{{ $p->halaman_terakhir }}</td>
<td class="px-3 py-3 text-xs">{{ \Carbon\Carbon::parse($p->last_session)->format('d M Y') }}</td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada progress kitab kuning.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $progress->links() }}</div>
@endsection

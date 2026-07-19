@extends('layouts.school-admin')
@section('title', 'College Database')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><h1 class="elite-h1 text-3xl ink-primary mb-2">Database Universitas</h1><div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">Referensi universitas untuk siswa SMA kelas akhir.</p></div>
<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Nama</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tipe</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Lokasi</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Passing Grade</th>
</tr></thead><tbody>
@forelse($colleges as $c)<tr class="border-t border-rule">
<td class="px-3 py-3 font-serif font-semibold">{{ $c->name }}</td>
<td class="px-3 py-3 text-xs">{{ $c->type }}</td>
<td class="px-3 py-3 text-xs">{{ $c->city }}, {{ $c->country }}</td>
<td class="px-3 py-3 text-right font-mono">{{ $c->passing_grade_avg ?? '—' }}</td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada data universitas (di-seed dari pemerintah).</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $colleges->links() }}</div>
@endsection

@extends('layouts.school-admin')
@section('title', 'Kompetensi Kurikulum')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><h1 class="elite-h1 text-3xl ink-primary mb-2">Kompetensi Kurikulum</h1><div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">Capaian pembelajaran (KD/CP) untuk pemetaan ke materi.</p></div>
<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Kode</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Framework</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Level</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Deskripsi</th>
</tr></thead><tbody>
@forelse($competencies as $c)<tr class="border-t border-rule">
<td class="px-3 py-3 font-mono text-xs">{{ $c->code }}</td>
<td class="px-3 py-3 text-xs">{{ $c->framework_name ?? '—' }}</td>
<td class="px-3 py-3"><span class="elite-kicker text-[.55rem]">{{ $c->level_type }}</span></td>
<td class="px-3 py-3 text-xs">{{ Str::limit($c->description, 100) }}</td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada kompetensi (di-import dari pemerintah/admin).</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $competencies->links() }}</div>
@endsection

@extends('layouts.school-admin')
@section('title', 'Pendaftar Beasiswa')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<a href="{{ route('admin.scholarship.programs.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Program</a>
<div class="mb-7"><div class="elite-kicker mb-2">Petentes</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Pendaftar Beasiswa</h1><div class="elite-rule"></div></div>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tgl</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Program</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
</tr></thead><tbody>
@forelse($applications as $a)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs">{{ $a->created_at?->format('d M Y') }}</td>
<td class="px-3 py-3 font-serif">{{ $a->student?->user?->name }}</td>
<td class="px-3 py-3 text-xs">{{ $a->program?->name }}</td>
<td class="px-3 py-3">
<form method="POST" action="{{ route('admin.scholarship.applications.review', $a) }}" class="inline">@csrf
<select name="status" onchange="this.form.submit()" class="text-xs border border-rule px-2 py-1">
@foreach(['submitted','review','approved','rejected','active','completed'] as $s)
<option value="{{ $s }}" @selected($a->status===$s)>{{ $s }}</option>
@endforeach
</select></form>
</td></tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada pendaftar.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $applications->links() }}</div>
@endsection

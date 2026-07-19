@extends('layouts.school-admin')
@section('title', 'Pendaftar PPDB')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7"><div class="elite-kicker mb-2">Candidati</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Pendaftar PPDB</h1>
<div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">{{ $applications->total() }} pendaftar terdaftar.</p></div>

<form method="GET" class="bg-white border border-rule p-5 mb-6 grid grid-cols-1 md:grid-cols-3 gap-3">
<select name="period_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— Semua Periode —</option>
@foreach($periods as $p)<option value="{{ $p->id }}" @selected(request('period_id') == $p->id)>{{ $p->name }}</option>@endforeach
</select>
<select name="status" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— Semua Status —</option>
@foreach(['submitted','review','accepted','waitlist','rejected','enrolled'] as $s)
<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
@endforeach
</select>
<button class="btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Filter</button>
</form>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">No. Reg</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Nama</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Periode</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Jalur</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
<th></th></tr></thead><tbody>
@forelse($applications as $a)<tr class="border-t border-rule hover:bg-gray-50">
<td class="px-3 py-3 font-mono text-xs">{{ $a->registration_no }}</td>
<td class="px-3 py-3"><div class="font-serif font-semibold">{{ $a->student_name }}</div><div class="text-xs text-gray-500">{{ $a->parent_phone }}</div></td>
<td class="px-3 py-3 text-xs">{{ $a->period?->name }}</td>
<td class="px-3 py-3"><span class="elite-kicker text-[.55rem]">{{ $a->jalur }}</span></td>
<td class="px-3 py-3">
<form method="POST" action="{{ route('admin.ppdb.applications.review', $a) }}" class="inline">@csrf
<select name="status" onchange="this.form.submit()" class="text-xs border border-rule px-2 py-1">
@foreach(['submitted','review','accepted','waitlist','rejected','enrolled'] as $s)
<option value="{{ $s }}" @selected($a->status === $s)>{{ ucfirst($s) }}</option>
@endforeach
</select></form>
</td><td></td></tr>
@empty<tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada pendaftar.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $applications->links() }}</div>
@endsection

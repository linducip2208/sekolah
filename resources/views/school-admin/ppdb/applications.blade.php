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
@if($a->ranking_score)<div class="text-xs text-gray-500 mt-1">Skor: {{ $a->ranking_score }} · Rank {{ $a->rank_position }}</div>@endif
@if($a->status === 'waitlist' && $a->waiting_list_position)<div class="text-xs text-amber-600 mt-1 font-semibold">Waiting #{{ $a->waiting_list_position }}</div>@endif
</td>
<td class="px-3 py-3 text-right whitespace-nowrap">
    @if(in_array($a->status, ['verified','accepted']))
    <details class="inline-block text-left"><summary class="text-xs underline ink-secondary cursor-pointer">Tes/Wawancara</summary>
        <form method="POST" action="{{ route('admin.ppdb.applications.score', $a) }}" class="mt-2 grid gap-1">@csrf
            <input type="number" step="0.01" name="entrance_test_score" min="0" max="100" value="{{ $a->entrance_test_score }}" placeholder="Tes masuk" class="border-2 border-rule px-2 py-1 font-mono text-xs w-28">
            <input type="number" step="0.01" name="interview_score" min="0" max="100" value="{{ $a->interview_score }}" placeholder="Wawancara" class="border-2 border-rule px-2 py-1 font-mono text-xs w-28">
            <button class="text-xs text-left ink-accent">Simpan</button>
        </form></details>
    @endif

    @if($a->status === 'accepted' && !$a->enrolled_student_id)
    <details class="inline-block text-left ml-2"><summary class="text-xs underline text-green-700 cursor-pointer">Jadikan Siswa</summary>
        <form method="POST" action="{{ route('admin.ppdb.applications.enroll', $a) }}" class="mt-2 grid gap-1">@csrf
            <select name="class_section_id" required class="border-2 border-rule px-2 py-1 font-serif text-xs">
                <option value="">— rombel —</option>
                @foreach($classSections as $cs)<option value="{{ $cs->id }}">{{ $cs->classRoom?->name }} {{ $cs->section?->name }}</option>@endforeach
            </select>
            <button class="text-xs text-left text-green-700">Konversi → Siswa</button>
        </form></details>
    @elseif($a->enrolled_student_id)
        <span class="text-xs text-green-700">✓ Siswa</span>
    @endif
</td></tr>
@empty<tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada pendaftar.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $applications->links() }}</div>
@endsection

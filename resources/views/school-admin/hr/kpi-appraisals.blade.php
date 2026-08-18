@extends('layouts.school-admin')
@section('title', 'KPI Appraisals')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="flex justify-between items-end">
        <div>
            <div class="elite-kicker mb-2">Evaluatio Praestantiae</div>
            <h1 class="elite-h1 text-3xl ink-primary mb-2">KPI Appraisals</h1>
            <div class="elite-rule"></div>
            <p class="font-serif text-sm text-gray-600 mt-3">Penilaian kinerja staff per periode.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.hr.kpi.templates') }}" class="btn-elite-ghost" style="font-size:.65rem;">Template</a>
            <a href="{{ route('admin.hr.kpi.goals') }}" class="btn-elite-ghost" style="font-size:.65rem;">Goals</a>
        </div>
    </div>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<div class="bg-white border border-rule p-5 mb-6">
    <form method="POST" action="{{ route('admin.hr.kpi.store') }}" class="flex gap-3 items-end flex-wrap">
        @csrf
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Staff</label>
            <select name="staff_id" required class="border-2 border-rule px-2 py-1.5 font-serif text-xs">
                <option value="">— pilih —</option>
                @foreach($staffs as $s)<option value="{{ $s->id }}">{{ $s->user?->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Template</label>
            <select name="template_id" required class="border-2 border-rule px-2 py-1.5 font-serif text-xs">
                @foreach($templates as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Periode</label>
            <input name="period" required placeholder="2025-Genap" class="border-2 border-rule px-2 py-1.5 font-serif text-xs">
        </div>
        <button class="btn-elite" style="padding:.4rem 1rem;font-size:.65rem;">Buat Appraisal</button>
    </form>
</div>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Staff</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Template</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Periode</th>
                <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Skor</th>
                <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Grade</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Reviewer</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($appraisals as $a)
            <tr class="border-t border-rule hover:bg-gray-50">
                <td class="px-4 py-3 font-serif">{{ $a->staff?->user?->name }}</td>
                <td class="px-4 py-3 text-xs">{{ $a->template?->name }}</td>
                <td class="px-4 py-3 text-xs font-mono">{{ $a->period }}</td>
                <td class="px-4 py-3 text-center font-display ink-primary">{{ $a->total_score ?? '—' }}</td>
                <td class="px-4 py-3 text-center">
                    @if($a->total_score)
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded text-xs font-bold
                            {{ match($a->grade) { 'A'=>'bg-green-100 text-green-700','B'=>'bg-blue-100 text-blue-700','C'=>'bg-yellow-100 text-yellow-700','D'=>'bg-orange-100 text-orange-700','E'=>'bg-red-100 text-red-700', default=>'bg-gray-100' }}">
                            {{ $a->grade }}
                        </span>
                    @else — @endif
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-0.5 rounded
                        {{ match($a->status) { 'draft'=>'bg-gray-100 text-gray-600','submitted'=>'bg-yellow-100 text-yellow-700','finalized'=>'bg-green-100 text-green-700', default=>'bg-gray-100' }}">
                        {{ $a->status }}
                    </span>
                </td>
                <td class="px-4 py-3 text-xs">{{ $a->reviewer?->name }}</td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.hr.kpi.show', $a) }}" class="text-xs underline ink-secondary">Detail</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="p-10 text-center text-gray-500 italic font-serif">Belum ada appraisal.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection

@extends('layouts.school-admin')
@section('title', 'Input Nilai')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.exams.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Daftar Ujian</a>

<div class="mb-7">
    <div class="elite-kicker mb-2">Notae Examinis</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Input Nilai: {{ $exam->title }}</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Total nilai: {{ $exam->total_marks }} · Nilai lulus: {{ $exam->pass_marks }} · {{ $students->count() }} siswa</p>
</div>

@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<form method="POST" action="{{ route('admin.exams.marks.save', $exam) }}">
    @csrf
    <div class="bg-white border border-rule overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-[var(--c-primary)] text-white">
                <tr>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">NIS</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
                    <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Nilai (max {{ $exam->total_marks }})</th>
                    <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Grade</th>
                    <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $s)
                    @php
                        $rec = $existing->get($s->id);
                        $score = $rec?->obtained_marks;
                        $passed = $score !== null && $score >= $exam->pass_marks;
                    @endphp
                    <tr class="border-t border-rule">
                        <td class="px-4 py-3 font-mono text-xs">{{ $s->admission_no }}</td>
                        <td class="px-4 py-3 font-serif">{{ $s->user?->name }}</td>
                        <td class="text-center px-4 py-3">
                            <input type="number" name="marks[{{ $s->id }}]" min="0" max="{{ $exam->total_marks }}"
                                   value="{{ $score }}"
                                   class="w-24 border-2 border-rule px-2 py-1 text-center font-mono">
                        </td>
                        <td class="text-center px-4 py-3 font-display text-lg ink-primary">{{ $rec?->grade ?? '—' }}</td>
                        <td class="text-center px-4 py-3">
                            @if($score !== null)
                                @if($passed)
                                    <span class="text-xs text-green-700">✓ Lulus</span>
                                @else
                                    <span class="text-xs text-red-700">✗ Tidak Lulus</span>
                                @endif
                            @else
                                <span class="text-xs text-gray-400">Belum diinput</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-4 bg-gray-50 text-right">
            <button class="btn-elite">Simpan Nilai</button>
        </div>
    </div>
</form>

@endsection

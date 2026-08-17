@extends('layouts.school-admin')
@section('title', 'Approval Rapor')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Approbatio</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Approval Rapor</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Alur: Draft → Diajukan → Disetujui → Dikunci.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<form method="GET" class="bg-white border border-rule p-4 mb-4 flex gap-2 items-center">
    <select name="semester_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— Semua semester —</option>
        @foreach($semesters as $s)<option value="{{ $s->id }}" @selected(request('semester_id') == $s->id)>{{ $s->name }}</option>@endforeach
    </select>
    <select name="status" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— Semua status —</option>
        @foreach(['draft','submitted','approved','locked'] as $st)<option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>@endforeach
    </select>
    <button class="btn-elite" style="padding:.5rem 1rem;font-size:.65rem;">Filter</button>
</form>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white"><tr>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Siswa</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Semester</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Rata-rata</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Grade</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Status</th>
            <th class="px-4 py-3"></th>
        </tr></thead>
        <tbody>
            @forelse($cards as $card)
            <tr class="border-t border-rule">
                <td class="px-4 py-3 font-serif">{{ $card->student?->user?->name }}</td>
                <td class="px-4 py-3 text-xs">{{ $card->semester?->name }}</td>
                <td class="px-4 py-3 text-center font-mono text-xs">{{ $card->total_percentage }}%</td>
                <td class="px-4 py-3 text-center font-display text-lg ink-primary">{{ $card->overall_grade ?? '—' }}</td>
                <td class="px-4 py-3 text-center">
                    @php $tone = ['draft'=>'gray','submitted'=>'amber','approved'=>'green','locked'=>'blue'][$card->status]; @endphp
                    <span class="text-xs px-2 py-0.5 rounded bg-{{ $tone }}-100 text-{{ $tone }}-800">{{ ucfirst($card->status) }}</span>
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                    @if($card->status === 'draft')
                    <form method="POST" action="{{ route('admin.grades.approval.submit', $card) }}" class="inline">@csrf<button class="text-xs underline ink-secondary">Ajukan</button></form>
                    @elseif($card->status === 'submitted')
                    <form method="POST" action="{{ route('admin.grades.approval.approve', $card) }}" class="inline">@csrf<button class="text-xs underline text-green-700">Setujui</button></form>
                    <form method="POST" action="{{ route('admin.grades.approval.reject', $card) }}" class="inline ml-2">@csrf<button class="text-xs underline text-red-700">Tolak</button></form>
                    @elseif($card->status === 'approved')
                    <form method="POST" action="{{ route('admin.grades.approval.lock', $card) }}" class="inline">@csrf<button class="text-xs underline ink-secondary">Kunci</button></form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada rapor.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $cards->links() }}</div>

@endsection

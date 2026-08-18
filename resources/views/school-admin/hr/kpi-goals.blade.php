@extends('layouts.school-admin')
@section('title', 'KPI Goals')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="flex justify-between items-end">
        <div>
            <div class="elite-kicker mb-2">Objectiva Praestantiae</div>
            <h1 class="elite-h1 text-3xl ink-primary mb-2">KPI Goals</h1>
            <div class="elite-rule"></div>
            <p class="font-serif text-sm text-gray-600 mt-3">Target kinerja staff per periode.</p>
        </div>
        <a href="{{ route('admin.hr.kpi.index') }}" class="btn-elite-ghost">← Appraisals</a>
    </div>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif

<div class="bg-white border border-rule p-5 mb-6">
    <form method="POST" action="{{ route('admin.hr.kpi.goals.store') }}" class="grid grid-cols-2 lg:grid-cols-5 gap-3 items-end">
        @csrf
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Staff</label>
            <select name="staff_id" required class="w-full border-2 border-rule px-2 py-1.5 font-serif text-xs">
                <option value="">— pilih —</option>
                @foreach($staffs as $s)<option value="{{ $s->id }}">{{ $s->user?->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Judul Goal</label>
            <input name="title" required class="w-full border-2 border-rule px-2 py-1.5 font-serif text-xs" placeholder="Tingkatkan pass rate siswa">
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Target</label>
            <input name="target_value" class="w-full border-2 border-rule px-2 py-1.5 font-mono text-xs" placeholder="90%">
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Deadline</label>
            <input type="date" name="due_date" class="w-full border-2 border-rule px-2 py-1.5 font-mono text-xs">
        </div>
        <button class="btn-elite" style="padding:.4rem;font-size:.65rem;">Tambah Goal</button>
    </form>
</div>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Staff</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Goal</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Target</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Actual</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Deadline</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($goals as $g)
            <tr class="border-t border-rule hover:bg-gray-50" x-data="{ edit: false }">
                <td class="px-4 py-3 font-serif text-xs">{{ $g->staff?->user?->name }}</td>
                <td class="px-4 py-3 text-xs" x-show="!edit">{{ $g->title }}</td>
                <td class="px-4 py-3 font-mono text-xs" x-show="!edit">{{ $g->target_value ?? '—' }}</td>
                <td class="px-4 py-3 font-mono text-xs" x-show="!edit">{{ $g->actual_value ?? '—' }}</td>
                <td class="px-4 py-3" x-show="!edit">
                    <span class="text-xs px-2 py-0.5 rounded
                        {{ match($g->status) { 'achieved'=>'bg-green-100 text-green-700','missed'=>'bg-red-100 text-red-700','in_progress'=>'bg-blue-100 text-blue-700', default=>'bg-gray-100' }}">
                        {{ str_replace('_', ' ', $g->status) }}
                    </span>
                </td>
                <td class="px-4 py-3 font-mono text-xs" x-show="!edit">{{ $g->due_date?->format('d M Y') ?? '—' }}</td>
                <td class="px-4 py-3 text-right" x-show="!edit">
                    <button @click="edit = true" class="text-xs underline ink-secondary">Edit</button>
                    <form method="POST" action="{{ route('admin.hr.kpi.goals.destroy', $g) }}" class="inline ml-1" onsubmit="return confirm('Hapus?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-700 hover:underline">×</button>
                    </form>
                </td>
                <td colspan="7" x-show="edit" x-cloak>
                    <form method="POST" action="{{ route('admin.hr.kpi.goals.update', $g) }}" class="p-3 bg-gray-50 grid grid-cols-5 gap-2 items-end">
                        @csrf
                        <div>
                            <label class="elite-kicker text-[.5rem] block">Actual Value</label>
                            <input name="actual_value" value="{{ $g->actual_value }}" class="w-full border border-rule px-2 py-1 font-mono text-xs">
                        </div>
                        <div>
                            <label class="elite-kicker text-[.5rem] block">Status</label>
                            <select name="status" class="w-full border border-rule px-2 py-1 text-xs">
                                <option value="in_progress" {{ $g->status==='in_progress'?'selected':'' }}>In Progress</option>
                                <option value="achieved" {{ $g->status==='achieved'?'selected':'' }}>Achieved</option>
                                <option value="missed" {{ $g->status==='missed'?'selected':'' }}>Missed</option>
                            </select>
                        </div>
                        <div class="flex gap-1">
                            <button class="btn-elite" style="padding:.2rem .5rem;font-size:.6rem;">Simpan</button>
                            <button type="button" @click="edit = false" class="btn-elite-ghost" style="font-size:.6rem;">Batal</button>
                        </div>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada goal.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection

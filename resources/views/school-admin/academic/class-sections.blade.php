@extends('layouts.school-admin')
@section('title', 'Rombel (Class Section)')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Combinationes</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Rombel (Kelas + Section)</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Gabungan Kelas × Section × Tahun Ajaran. Mis. "Kelas 10-A 2024/2025" dengan wali kelas.</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Tambah Rombel</h3>
            @if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('admin.academic.class-sections.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Kelas</label>
                    <select name="class_room_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— pilih —</option>
                        @foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Section</label>
                    <select name="section_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— pilih —</option>
                        @foreach($sections as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Medium</label>
                    <select name="medium_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— pilih —</option>
                        @foreach($mediums as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tahun Ajaran</label>
                    <select name="academic_year_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— pilih —</option>
                        @foreach($years as $y)<option value="{{ $y->id }}" @selected($y->is_active)>{{ $y->name }}{{ $y->is_active ? ' (aktif)' : '' }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Wali Kelas</label>
                    <select name="class_teacher_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— optional —</option>
                        @foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                    </select>
                </div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan Rombel</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white border border-rule overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white">
                    <tr>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Rombel</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tahun</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Wali Kelas</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Siswa</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classSections as $cs)
                        <tr class="border-t border-rule">
                            <td class="px-4 py-3 font-serif font-semibold ink-primary">{{ $cs->classRoom?->name }} — {{ $cs->section?->name }}</td>
                            <td class="px-4 py-3 text-xs">{{ $cs->academicYear?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $cs->classTeacher?->name ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono">{{ $cs->students_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.academic.class-sections.destroy', $cs) }}" class="inline" onsubmit="return confirm('Hapus rombel ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada rombel.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

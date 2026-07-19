@extends('layouts.school-admin')
@section('title', 'Survei Kepuasan')
@section('sidebar')@include('school-admin.partials.sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Satisfactio Mensura</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Template Survei</h1>
    <div class="elite-rule"></div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="elite-card p-6 sticky top-6" x-data="{ editing: null }">
            <h3 class="elite-h3 text-base ink-primary mb-3">Buat Template Baru</h3>
            @if($errors->any())
                <div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('admin.surveys.templates.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Judul Survei</label>
                    <input name="title" required maxlength="255" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Evaluasi Kinerja Guru Semester 1">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Penjelasan singkat tentang survei ini..."></textarea>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tipe Survei</label>
                    <select name="survey_type" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="guru">👩‍🏫 Evaluasi Guru</option>
                        <option value="staff">🧑‍💼 Evaluasi Staff</option>
                        <option value="kepsek">👨‍💼 Evaluasi Kepala Sekolah</option>
                        <option value="fasilitas">🏫 Evaluasi Fasilitas</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="elite-kicker text-[.6rem] block mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    </div>
                    <div>
                        <label class="elite-kicker text-[.6rem] block mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    </div>
                </div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Buat Template</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="elite-card overflow-hidden">
            <table class="w-full text-sm table-elite">
                <thead>
                    <tr>
                        <th class="text-left px-4 py-3">Judul</th>
                        <th class="text-left px-4 py-3">Tipe</th>
                        <th class="text-center px-4 py-3">Pertanyaan</th>
                        <th class="text-center px-4 py-3">Respons</th>
                        <th class="text-center px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $t)
                        <tr x-data="{ open: false }" class="border-t border-rule">
                            <td class="px-4 py-3 font-serif font-semibold ink-primary">{{ $t->title }}</td>
                            <td class="px-4 py-3 text-xs">
                                @php
                                    $typeLabels = ['guru' => 'Guru', 'staff' => 'Staff', 'kepsek' => 'Kepala Sekolah', 'fasilitas' => 'Fasilitas'];
                                @endphp
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $t->survey_type === 'guru' ? 'bg-blue-50 text-blue-800' : ($t->survey_type === 'fasilitas' ? 'bg-green-50 text-green-800' : 'bg-purple-50 text-purple-800') }}">
                                    {{ $typeLabels[$t->survey_type] ?? $t->survey_type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold">{{ $t->questions_count }}</td>
                            <td class="px-4 py-3 text-center font-semibold">{{ $t->responses_count }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($t->is_active)
                                    <span class="text-xs font-semibold text-green-700">● Aktif</span>
                                @else
                                    <span class="text-xs text-gray-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.surveys.questions', $t) }}" class="text-xs underline ink-secondary hover:ink-accent">Pertanyaan</a>
                                    <a href="{{ route('admin.surveys.responses', $t) }}" class="text-xs underline ink-secondary hover:ink-accent">Respons</a>
                                    <a href="{{ route('admin.surveys.analytics', $t) }}" class="text-xs underline ink-secondary hover:ink-accent">Analitik</a>
                                    <button @click="open = !open" class="text-xs underline ink-secondary hover:ink-accent ml-1">Edit</button>

                                    <form method="POST" action="{{ route('admin.surveys.templates.destroy', $t) }}" class="inline ml-1"
                                          onsubmit="return confirm('Hapus template survei ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                    </form>
                                </div>

                                <div x-show="open" x-cloak class="mt-3 p-4 bg-gray-50 rounded text-left">
                                    <form method="POST" action="{{ route('admin.surveys.templates.update', $t) }}" class="space-y-2">
                                        @csrf @method('PUT')
                                        <div class="grid grid-cols-2 gap-2">
                                            <input name="title" value="{{ $t->title }}" class="w-full border border-rule px-2 py-1 text-sm font-serif">
                                            <select name="survey_type" class="w-full border border-rule px-2 py-1 text-sm">
                                                <option value="guru" {{ $t->survey_type === 'guru' ? 'selected' : '' }}>Guru</option>
                                                <option value="staff" {{ $t->survey_type === 'staff' ? 'selected' : '' }}>Staff</option>
                                                <option value="kepsek" {{ $t->survey_type === 'kepsek' ? 'selected' : '' }}>Kepala Sekolah</option>
                                                <option value="fasilitas" {{ $t->survey_type === 'fasilitas' ? 'selected' : '' }}>Fasilitas</option>
                                            </select>
                                        </div>
                                        <textarea name="description" rows="2" class="w-full border border-rule px-2 py-1 text-sm font-serif">{{ $t->description }}</textarea>
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="date" name="start_date" value="{{ $t->start_date?->format('Y-m-d') }}" class="w-full border border-rule px-2 py-1 text-sm">
                                            <input type="date" name="end_date" value="{{ $t->end_date?->format('Y-m-d') }}" class="w-full border border-rule px-2 py-1 text-sm">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <label class="text-xs"><input type="checkbox" name="is_active" value="1" {{ $t->is_active ? 'checked' : '' }}> Aktif</label>
                                            <button class="btn-elite text-xs" style="padding:.35rem .8rem;font-size:.6rem;">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada template survei.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@stop

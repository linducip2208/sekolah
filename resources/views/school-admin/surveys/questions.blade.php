@extends('layouts.school-admin')
@section('title', 'Pertanyaan Survei — ' . $template->title)
@section('sidebar')@include('school-admin.partials.sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')

<div class="mb-7">
    <a href="{{ route('admin.surveys.templates.index') }}" class="text-xs ink-secondary hover:ink-accent">← Kembali ke Template</a>
    <div class="elite-kicker mb-2 mt-2">Quaestiones</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">{{ $template->title }}</h1>
    <p class="text-sm text-gray-600 font-serif mb-1">{{ $template->description }}</p>
    <div class="elite-rule"></div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="elite-card p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Tambah Pertanyaan</h3>
            @if($errors->any())
                <div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('admin.surveys.questions.store', $template) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Pertanyaan</label>
                    <textarea name="question_text" required rows="3" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Tulis pertanyaan survei..."></textarea>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tipe Pertanyaan</label>
                    <select name="question_type" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" x-data
                            x-on:change="document.getElementById('options-field').style.display = $el.value === 'multiple_choice' ? 'block' : 'none'">
                        <option value="rating_1_5">⭐ Rating 1-5</option>
                        <option value="text">📝 Text / Essay</option>
                        <option value="multiple_choice">🔘 Pilihan Ganda</option>
                    </select>
                </div>
                <div id="options-field" style="display:none;">
                    <label class="elite-kicker text-[.6rem] block mb-1">Opsi (JSON array)</label>
                    <textarea name="options" rows="3" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm font-mono"
                              placeholder='["Sangat Baik", "Baik", "Cukup", "Kurang"]'></textarea>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Urutan</label>
                    <input type="number" name="sort_order" value="0" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Tambah Pertanyaan</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="elite-card overflow-hidden">
            <table class="w-full text-sm table-elite">
                <thead>
                    <tr>
                        <th class="text-center px-4 py-3">#</th>
                        <th class="text-left px-4 py-3">Pertanyaan</th>
                        <th class="text-center px-4 py-3">Tipe</th>
                        <th class="text-center px-4 py-3">Opsi</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($questions as $i => $q)
                        <tr x-data="{ open: false }" class="border-t border-rule">
                            <td class="px-4 py-3 text-center font-semibold">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-serif">{{ $q->question_text }}</td>
                            <td class="px-4 py-3 text-center text-xs">
                                @if($q->question_type === 'rating_1_5')
                                    <span class="px-2 py-1 rounded bg-yellow-50 text-yellow-800 font-semibold">Rating 1-5</span>
                                @elseif($q->question_type === 'text')
                                    <span class="px-2 py-1 rounded bg-gray-100 text-gray-800 font-semibold">Text</span>
                                @else
                                    <span class="px-2 py-1 rounded bg-indigo-50 text-indigo-800 font-semibold">Pilihan Ganda</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-xs font-mono">
                                {{ $q->options ? implode(', ', $q->options) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button @click="open = !open" class="text-xs underline ink-secondary hover:ink-accent">Edit</button>
                                <form method="POST" action="{{ route('admin.surveys.questions.destroy', [$template, $q]) }}" class="inline ml-2"
                                      onsubmit="return confirm('Hapus pertanyaan ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                </form>

                                <div x-show="open" x-cloak class="mt-3 p-3 bg-gray-50 rounded text-left">
                                    <form method="POST" action="{{ route('admin.surveys.questions.update', [$template, $q]) }}" class="space-y-2">
                                        @csrf @method('PUT')
                                        <textarea name="question_text" rows="2" class="w-full border border-rule px-2 py-1 text-sm font-serif">{{ $q->question_text }}</textarea>
                                        <div class="grid grid-cols-2 gap-2">
                                            <select name="question_type" class="w-full border border-rule px-2 py-1 text-sm">
                                                <option value="rating_1_5" {{ $q->question_type === 'rating_1_5' ? 'selected' : '' }}>Rating 1-5</option>
                                                <option value="text" {{ $q->question_type === 'text' ? 'selected' : '' }}>Text</option>
                                                <option value="multiple_choice" {{ $q->question_type === 'multiple_choice' ? 'selected' : '' }}>Pilihan Ganda</option>
                                            </select>
                                            <input type="number" name="sort_order" value="{{ $q->sort_order }}" class="w-full border border-rule px-2 py-1 text-sm">
                                        </div>
                                        <textarea name="options" rows="2" class="w-full border border-rule px-2 py-1 text-sm font-mono" placeholder='["A","B","C"]'>{{ $q->options ? json_encode($q->options) : '' }}</textarea>
                                        <button class="btn-elite text-xs" style="padding:.35rem .8rem;font-size:.6rem;">Simpan</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada pertanyaan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@stop

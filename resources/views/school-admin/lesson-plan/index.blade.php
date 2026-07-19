@extends('layouts.school-admin')
@section('title', 'Lesson Plan / RPP')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">RPP</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Lesson Plan / RPP</h1><div class="elite-rule"></div></div>

@if($providers->isNotEmpty())
<div class="mb-5 p-4 bg-gradient-to-r from-indigo-50 to-violet-50 border border-indigo-200">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <div class="font-serif font-bold text-base ink-primary">🤖 Generate RPP dengan AI</div>
            <p class="text-xs text-gray-600">AI akan membuatkan RPP 1 lembar format Kurikulum Merdeka / K13 secara otomatis.</p>
        </div>
        <button onclick="openAiModal()" class="btn-elite bg-indigo-700 border-indigo-700 text-white">
            ✨ Generate RPP AI
        </button>
    </div>
</div>
@else
<div class="mb-5 p-3 bg-yellow-50 border border-yellow-200 text-yellow-800 text-xs">
    ⚠ Belum ada AI provider. <a href="{{ route('admin.ai.providers.index') }}" class="underline font-semibold">Tambahkan provider AI</a> untuk fitur generate RPP otomatis.
</div>
@endif

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Buat RPP Manual</summary>
@if($errors->any())<div class="mx-5 my-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('admin.lesson-plan.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-2 gap-3">@csrf
<div class="md:col-span-2"><input name="title" required maxlength="255" placeholder="Judul RPP" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></div>
<select name="subject_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— Mapel —</option>
@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</select>
<select name="class_section_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— Rombel —</option>
@foreach($classSections as $cs)<option value="{{ $cs->id }}">{{ $cs->classRoom?->name }} {{ $cs->section?->name }}</option>@endforeach
</select>
<select name="teacher_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— Guru —</option>
@foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
</select>
<input type="date" name="lesson_date" class="border-2 border-rule px-3 py-2 text-sm">
<input type="number" name="duration_minutes" min="15" max="300" placeholder="Durasi (menit)" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<div class="md:col-span-2"><textarea name="material_summary" rows="3" placeholder="Ringkasan materi" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea></div>
<div class="md:col-span-2"><button class="btn-elite">Simpan RPP</button></div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Judul</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Mapel</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Rombel</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Guru</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tgl</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">AI</th>
<th></th></tr></thead><tbody>
@forelse($plans as $p)<tr class="border-t border-rule">
<td class="px-3 py-3 font-serif font-semibold">{{ $p->title }}</td>
<td class="px-3 py-3 text-xs">{{ $p->subject?->name }}</td>
<td class="px-3 py-3 text-xs">{{ $p->classSection?->classRoom?->name }} {{ $p->classSection?->section?->name }}</td>
<td class="px-3 py-3 text-xs">{{ $p->teacher?->name }}</td>
<td class="px-3 py-3 text-xs">{{ $p->lesson_date?->format('d M Y') }}</td>
<td class="px-3 py-3"><span class="elite-kicker text-[.55rem]">{{ $p->status }}</span></td>
<td class="px-3 py-3 text-center">
    @if($p->ai_generated)
        <span class="text-[.6rem] text-indigo-600 font-medium" title="{{ $p->ai_tokens_used }} token">
            🤖 AI
        </span>
    @else
        <span class="text-[.6rem] text-gray-400">Manual</span>
    @endif
</td>
<td class="px-3 py-3 text-right">
<form method="POST" action="{{ route('admin.lesson-plan.destroy', $p) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
</td></tr>@empty<tr><td colspan="8" class="p-10 text-center text-gray-500 italic font-serif">Belum ada RPP.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $plans->links() }}</div>

{{-- AI Generate Modal --}}
<div id="aiModal" x-show="open" x-cloak x-data="{ open: false, generating: false, preview: null, error: null }"
     x-init="window.openAiModal = () => { open = true; preview = null; error = null; }"
     class="fixed inset-0 z-50 flex items-start justify-center pt-10 sm:pt-16 px-3"
     style="background: rgba(11,29,58,.75);">
    <div @click.outside="open = false" class="bg-white w-full max-w-4xl shadow-2xl border border-rule max-h-[85vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-rule">
            <div>
                <div class="font-serif font-bold text-lg">✨ Generate RPP dengan AI</div>
                <div class="text-[.6rem] text-gray-500">AI akan membuatkan RPP 1 lembar format Kurikulum Merdeka atau K13</div>
            </div>
            <button @click="open = false" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
        </div>

        {{-- Form --}}
        <form id="aiGenerateForm" class="px-6 py-4 grid md:grid-cols-3 gap-3" @submit.prevent="generateRpp()" x-show="!preview">
            <div class="md:col-span-3">
                <label class="elite-kicker text-[.6rem] block mb-1">Judul / Topik</label>
                <input id="aiTopic" required placeholder="contoh: Persamaan Kuadrat — Metode Pemfaktoran" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Mata Pelajaran</label>
                <input id="aiSubjectName" required placeholder="contoh: Matematika" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Kelas</label>
                <input id="aiClassLevel" required placeholder="contoh: X" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Pertemuan Ke-</label>
                <input id="aiMeetingNumber" type="number" value="1" min="1" max="100" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Kurikulum</label>
                <select id="aiCurriculumType" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="Merdeka">Kurikulum Merdeka</option>
                    <option value="K13">Kurikulum 2013</option>
                    <option value="Cambridge">Cambridge</option>
                    <option value="IB">International Baccalaureate</option>
                </select>
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Mapel (untuk simpan)</label>
                <select id="aiSubjectId" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="">— Pilih —</option>
                    @foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Rombel (untuk simpan)</label>
                <select id="aiClassSectionId" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="">— Pilih —</option>
                    @foreach($classSections as $cs)<option value="{{ $cs->id }}">{{ $cs->classRoom?->name }} {{ $cs->section?->name }}</option>@endforeach
                </select>
            </div>
            @if($aiModels->isNotEmpty())
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">AI Model</label>
                <select id="aiModelSelect" class="w-full border-2 border-rule px-3 py-2 font-serif text-xs">
                    <option value="">— Auto —</option>
                    @foreach($aiModels as $am)
                        <option value="{{ $am->provider?->id }}|{{ $am->id }}">
                            {{ $am->provider?->name }} / {{ $am->display_name ?? $am->model_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="md:col-span-3 flex gap-3 pt-2">
                <button type="submit" class="btn-elite bg-indigo-700 border-indigo-700 text-white" :disabled="generating">
                    <span x-show="!generating">🚀 Generate RPP</span>
                    <span x-show="generating" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Generating...
                    </span>
                </button>
                <button type="button" @click="open = false" class="btn-elite" style="background:var(--c-muted);border-color:var(--c-muted);">Batal</button>
            </div>
        </form>

        {{-- Preview --}}
        <div x-show="preview" class="px-6 py-4">
            <div class="flex items-center justify-between mb-4">
                <div class="font-serif font-bold text-lg text-green-800">✅ RPP Berhasil Digenerate</div>
                <div class="text-[.6rem] text-gray-500" x-text="preview && preview.tokens_used ? preview.tokens_used + ' token · ' + preview.processing_time_ms + 'ms' : ''"></div>
            </div>

            {{-- Preview Content --}}
            <div class="space-y-4 mb-6">
                <div class="bg-gray-50 border border-rule p-4">
                    <div class="elite-kicker text-[.6rem] mb-2">Identitas</div>
                    <div class="grid grid-cols-2 gap-2 text-sm" x-show="preview?.parsed?.identitas">
                        <template x-for="(val, key) in preview.parsed.identitas" :key="key">
                            <div class="flex gap-2">
                                <span class="text-gray-500 capitalize" x-text="key.replace(/_/g, ' ') + ':'"></span>
                                <span class="font-serif font-semibold" x-text="val"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="bg-gray-50 border border-rule p-4">
                    <div class="elite-kicker text-[.6rem] mb-2">Tujuan Pembelajaran</div>
                    <ul class="list-disc list-inside text-sm space-y-1" x-show="preview?.parsed?.tujuan_pembelajaran?.length">
                        <template x-for="t in preview.parsed.tujuan_pembelajaran">
                            <li x-text="t"></li>
                        </template>
                    </ul>
                </div>

                <div class="bg-gray-50 border border-rule p-4">
                    <div class="elite-kicker text-[.6rem] mb-2">Kegiatan Pendahuluan</div>
                    <p class="text-sm" x-text="preview?.parsed?.kegiatan_pendahuluan || '—'"></p>
                </div>

                <div class="bg-gray-50 border border-rule p-4">
                    <div class="elite-kicker text-[.6rem] mb-2">Kegiatan Inti</div>
                    <p class="text-sm whitespace-pre-wrap" x-text="preview?.parsed?.kegiatan_inti || '—'"></p>
                </div>

                <div class="bg-gray-50 border border-rule p-4">
                    <div class="elite-kicker text-[.6rem] mb-2">Penutup</div>
                    <p class="text-sm" x-text="preview?.parsed?.penutup || '—'"></p>
                </div>

                <div class="bg-gray-50 border border-rule p-4">
                    <div class="elite-kicker text-[.6rem] mb-2">Asesmen</div>
                    <ul class="list-disc list-inside text-sm space-y-1" x-show="preview?.parsed?.asesmen?.length">
                        <template x-for="a in preview.parsed.asesmen">
                            <li x-text="a"></li>
                        </template>
                    </ul>
                </div>

                <div class="bg-gray-50 border border-rule p-4" x-show="preview?.parsed?.materi">
                    <div class="elite-kicker text-[.6rem] mb-2">Materi</div>
                    <p class="text-sm" x-text="preview?.parsed?.materi"></p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 pt-2 border-t border-rule">
                <form method="POST" action="{{ route('admin.lesson-plan.generate-save') }}">
                    @csrf
                    <input type="hidden" name="title" id="saveTitle">
                    <input type="hidden" name="subject_name" id="saveSubjectName">
                    <input type="hidden" name="class_level" id="saveClassLevel">
                    <input type="hidden" name="curriculum_type" id="saveCurriculumType">
                    <input type="hidden" name="meeting_number" id="saveMeetingNumber">
                    <input type="hidden" name="subject_id" id="saveSubjectId">
                    <input type="hidden" name="class_section_id" id="saveClassSectionId">
                    <input type="hidden" name="ai_provider_id" id="saveProviderId">
                    <input type="hidden" name="ai_model_id" id="saveModelId">
                    <button type="submit" class="btn-elite bg-green-700 border-green-700 text-white">
                        💾 Simpan RPP
                    </button>
                </form>
                <button @click="preview = null" class="btn-elite" style="background:var(--c-muted);border-color:var(--c-muted);">
                    ← Edit & Generate Ulang
                </button>
                <button @click="open = false" class="btn-elite" style="background:transparent;color:var(--c-primary);border-color:var(--c-primary);">
                    Tutup
                </button>
            </div>
        </div>

        {{-- Error --}}
        <div x-show="error" class="px-6 py-4">
            <div class="bg-red-50 border border-red-200 text-red-800 p-4 text-sm" x-text="error"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openAiModal() {
    document.querySelector('#aiModal')._x_dataStack[0].open = true;
    document.querySelector('#aiModal')._x_dataStack[0].preview = null;
    document.querySelector('#aiModal')._x_dataStack[0].error = null;
}

function generateRpp() {
    const modal = document.querySelector('#aiModal')._x_dataStack[0];
    modal.generating = true;
    modal.error = null;

    const modelVal = document.getElementById('aiModelSelect')?.value || '';
    const [providerId, modelId] = modelVal.split('|');

    const title = document.getElementById('aiTopic').value;
    const subjectName = document.getElementById('aiSubjectName').value;
    const classLevel = document.getElementById('aiClassLevel').value;
    const meetingNumber = document.getElementById('aiMeetingNumber').value;
    const curriculumType = document.getElementById('aiCurriculumType').value;

    document.getElementById('saveTitle').value = title;
    document.getElementById('saveSubjectName').value = subjectName;
    document.getElementById('saveClassLevel').value = classLevel;
    document.getElementById('saveCurriculumType').value = curriculumType;
    document.getElementById('saveMeetingNumber').value = meetingNumber;
    document.getElementById('saveSubjectId').value = document.getElementById('aiSubjectId').value;
    document.getElementById('saveClassSectionId').value = document.getElementById('aiClassSectionId').value;
    document.getElementById('saveProviderId').value = providerId || '';
    document.getElementById('saveModelId').value = modelId || '';

    fetch('{{ route('admin.lesson-plan.generate') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            subject_name: subjectName,
            class_level: classLevel,
            title: title,
            meeting_number: parseInt(meetingNumber) || 1,
            curriculum_type: curriculumType,
            ai_provider_id: providerId || null,
            ai_model_id: modelId || null,
        }),
    })
    .then(r => r.json())
    .then(data => {
        modal.generating = false;
        if (data.success) {
            modal.preview = data;
        } else {
            modal.error = data.error || 'Gagal generate RPP.';
        }
    })
    .catch(err => {
        modal.generating = false;
        modal.error = 'Network error: ' + err.message;
    });
}
</script>
@endpush
@endsection

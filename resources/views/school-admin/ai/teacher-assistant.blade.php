@extends('layouts.school-admin')
@section('title', 'AI Teacher Assistant')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Auxilium Docentis AI</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">AI Teacher Assistant</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Bantuan AI untuk membuat modul ajar, rubrik, worksheet, variasi soal, dan remedial.</p>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-sm text-green-800">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-sm text-red-800">{{ $errors->first() }}</div>
@endif

<div x-data="aiTeacher()" class="space-y-6">

<div class="flex flex-wrap gap-2 border-b border-rule pb-3">
    <template x-for="(tab, i) in tabs" :key="i">
        <button @click="activeTab = tab.id"
                :class="activeTab === tab.id ? 'bg-[var(--c-primary)] text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                class="px-4 py-2 text-sm font-serif border border-rule transition-colors" x-text="tab.label"></button>
    </template>
</div>

<!-- MODUL AJAR -->
<div x-show="activeTab === 'modul-ajar'" x-cloak class="grid lg:grid-cols-2 gap-6">
<div class="bg-white border border-rule p-6">
    <h3 class="elite-kicker text-[.65rem] mb-3">Generator Modul Ajar</h3>
    <form @submit.prevent="callAi('modul-ajar', '{{ url('/ai/teacher-assistant/modul-ajar') }}')" class="space-y-3">
        <select x-model="formModulAjar.subject_name" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">-- Mata Pelajaran --</option>
            @foreach($subjects as $s)<option value="{{ $s->name }}">{{ $s->name }}</option>@endforeach
        </select>
        <input x-model="formModulAjar.topic" required placeholder="Topik (contoh: Persamaan Linear)" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        <input x-model="formModulAjar.grade_level" required placeholder="Kelas/Fase (contoh: VII-A)" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        <input x-model.number="formModulAjar.hours" type="number" min="1" max="20" placeholder="Jam Pelajaran" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        <select x-model="formModulAjar.ai_model_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">-- Model AI (otomatis) --</option>
            @foreach($aiModels as $m)<option value="{{ $m->id }}">{{ $m->name ?? $m->model_name }} ({{ $m->provider?->name }})</option>@endforeach
        </select>
        <button type="submit" class="btn-elite w-full" :disabled="loading" style="padding:.6rem;font-size:.65rem;">
            <span x-show="!loading">Generate Modul Ajar</span>
            <span x-show="loading">Generating...</span>
        </button>
    </form>
</div>
<div class="bg-white border border-rule p-6 overflow-auto max-h-[600px]">
    <h3 class="elite-kicker text-[.65rem] mb-3">Hasil</h3>
    <div x-show="resultModulAjar" x-html="renderModulAjar()" class="font-serif text-sm leading-relaxed"></div>
    <div x-show="!resultModulAjar && !loading" class="text-center text-gray-400 italic py-10 font-serif">Hasil akan muncul di sini...</div>
</div>
</div>

<!-- RUBRIK -->
<div x-show="activeTab === 'rubrik'" x-cloak class="grid lg:grid-cols-2 gap-6">
<div class="bg-white border border-rule p-6">
    <h3 class="elite-kicker text-[.65rem] mb-3">Generator Rubrik Penilaian</h3>
    <form @submit.prevent="callAi('rubrik', '{{ url('/ai/teacher-assistant/rubrik') }}')" class="space-y-3">
        <input x-model="formRubrik.assignment_title" required placeholder="Judul Tugas/Penilaian" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        <div>
            <label class="text-xs text-gray-500 mb-1 block">Kriteria (satu per baris)</label>
            <textarea x-model="formRubrik.criteria_text" rows="4" required placeholder="Ketepatan Isi&#10;Keterampilan Analisis&#10;Kreativitas&#10;Penyajian" class="w-full border-2 border-rule px-3 py-2 font-mono text-xs"></textarea>
        </div>
        <input x-model.number="formRubrik.max_score" type="number" min="1" max="100" placeholder="Skor Maksimum" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        <select x-model="formRubrik.ai_model_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">-- Model AI (otomatis) --</option>
            @foreach($aiModels as $m)<option value="{{ $m->id }}">{{ $m->name ?? $m->model_name }} ({{ $m->provider?->name }})</option>@endforeach
        </select>
        <button type="submit" class="btn-elite w-full" :disabled="loading" style="padding:.6rem;font-size:.65rem;">
            <span x-show="!loading">Generate Rubrik</span>
            <span x-show="loading">Generating...</span>
        </button>
    </form>
</div>
<div class="bg-white border border-rule p-6 overflow-auto max-h-[600px]">
    <h3 class="elite-kicker text-[.65rem] mb-3">Hasil Rubrik</h3>
    <div x-show="resultRubrik" x-html="renderRubrik()" class="font-serif text-sm leading-relaxed"></div>
    <div x-show="!resultRubrik && !loading" class="text-center text-gray-400 italic py-10 font-serif">Rubrik akan muncul di sini...</div>
</div>
</div>

<!-- WORKSHEET -->
<div x-show="activeTab === 'worksheet'" x-cloak class="grid lg:grid-cols-2 gap-6">
<div class="bg-white border border-rule p-6">
    <h3 class="elite-kicker text-[.65rem] mb-3">Generator Worksheet</h3>
    <form @submit.prevent="callAi('worksheet', '{{ url('/ai/teacher-assistant/worksheet') }}')" class="space-y-3">
        <select x-model="formWorksheet.subject_name" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">-- Mata Pelajaran --</option>
            @foreach($subjects as $s)<option value="{{ $s->name }}">{{ $s->name }}</option>@endforeach
        </select>
        <input x-model="formWorksheet.topic" required placeholder="Topik" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        <input x-model="formWorksheet.grade_level" required placeholder="Kelas/Fase" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        <input x-model.number="formWorksheet.question_count" type="number" min="1" max="50" placeholder="Jumlah Soal" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        <select x-model="formWorksheet.ai_model_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">-- Model AI (otomatis) --</option>
            @foreach($aiModels as $m)<option value="{{ $m->id }}">{{ $m->name ?? $m->model_name }} ({{ $m->provider?->name }})</option>@endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="btn-elite flex-1" :disabled="loading" style="padding:.6rem;font-size:.65rem;">
                <span x-show="!loading">Preview</span>
                <span x-show="loading">Generating...</span>
            </button>
            <button type="button" @click="callAiSaveWorksheet()" class="btn-elite flex-1" :disabled="loading" style="padding:.6rem;font-size:.65rem;background:var(--c-secondary,#059669);">
                <span x-show="!loading">Simpan ke Bank Soal</span>
                <span x-show="loading">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
<div class="bg-white border border-rule p-6 overflow-auto max-h-[600px]">
    <h3 class="elite-kicker text-[.65rem] mb-3">Hasil Worksheet</h3>
    <div x-show="resultWorksheet" x-html="renderWorksheet()" class="font-serif text-sm leading-relaxed"></div>
    <div x-show="!resultWorksheet && !loading" class="text-center text-gray-400 italic py-10 font-serif">Worksheet akan muncul di sini...</div>
</div>
</div>

<!-- VARIASI SOAL -->
<div x-show="activeTab === 'variasi'" x-cloak class="grid lg:grid-cols-2 gap-6">
<div class="bg-white border border-rule p-6">
    <h3 class="elite-kicker text-[.65rem] mb-3">Generator Variasi Soal</h3>
    <form @submit.prevent="callAi('variasi', '{{ url('/ai/teacher-assistant/variasi') }}')" class="space-y-3">
        <input x-model="formVariasi.question_id" type="number" required placeholder="ID Soal (dari Bank Soal)" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        <input x-model.number="formVariasi.variation_count" type="number" min="1" max="10" placeholder="Jumlah Variasi" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        <select x-model="formVariasi.ai_model_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">-- Model AI (otomatis) --</option>
            @foreach($aiModels as $m)<option value="{{ $m->id }}">{{ $m->name ?? $m->model_name }} ({{ $m->provider?->name }})</option>@endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="btn-elite flex-1" :disabled="loading" style="padding:.6rem;font-size:.65rem;">
                <span x-show="!loading">Preview Variasi</span>
                <span x-show="loading">Generating...</span>
            </button>
            <button type="button" @click="callAiSaveVariation()" class="btn-elite flex-1" :disabled="loading" style="padding:.6rem;font-size:.65rem;background:var(--c-secondary,#059669);">
                <span x-show="!loading">Simpan Variasi</span>
                <span x-show="loading">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
<div class="bg-white border border-rule p-6 overflow-auto max-h-[600px]">
    <h3 class="elite-kicker text-[.65rem] mb-3">Hasil Variasi</h3>
    <div x-show="resultVariasi" x-html="renderVariasi()" class="font-serif text-sm leading-relaxed"></div>
    <div x-show="!resultVariasi && !loading" class="text-center text-gray-400 italic py-10 font-serif">Variasi akan muncul di sini...</div>
</div>
</div>

<!-- REMEDIAL -->
<div x-show="activeTab === 'remedial'" x-cloak class="grid lg:grid-cols-2 gap-6">
<div class="bg-white border border-rule p-6">
    <h3 class="elite-kicker text-[.65rem] mb-3">Generator Remedial / Pengayaan</h3>
    <form @submit.prevent="callAi('remedial', '{{ url('/ai/teacher-assistant/remedial') }}')" class="space-y-3">
        <select x-model="formRemedial.student_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">-- Pilih Siswa --</option>
            @foreach($students as $s)<option value="{{ $s->id }}">{{ $s->user?->name }} ({{ $s->admission_no }})</option>@endforeach
        </select>
        <select x-model="formRemedial.subject_name" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">-- Mata Pelajaran --</option>
            @foreach($subjects as $s)<option value="{{ $s->name }}">{{ $s->name }}</option>@endforeach
        </select>
        <div>
            <label class="text-xs text-gray-500 mb-1 block">Topik Lemah (satu per baris)</label>
            <textarea x-model="formRemedial.weak_topics_text" rows="3" required placeholder="Persamaan Linear&#10;Fungsi Kuadrat&#10;Bangun Datar" class="w-full border-2 border-rule px-3 py-2 font-mono text-xs"></textarea>
        </div>
        <select x-model="formRemedial.ai_model_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">-- Model AI (otomatis) --</option>
            @foreach($aiModels as $m)<option value="{{ $m->id }}">{{ $m->name ?? $m->model_name }} ({{ $m->provider?->name }})</option>@endforeach
        </select>
        <button type="submit" class="btn-elite w-full" :disabled="loading" style="padding:.6rem;font-size:.65rem;">
            <span x-show="!loading">Generate Remedial</span>
            <span x-show="loading">Generating...</span>
        </button>
    </form>
</div>
<div class="bg-white border border-rule p-6 overflow-auto max-h-[600px]">
    <h3 class="elite-kicker text-[.65rem] mb-3">Hasil Remedial</h3>
    <div x-show="resultRemedial" x-html="renderRemedial()" class="font-serif text-sm leading-relaxed"></div>
    <div x-show="!resultRemedial && !loading" class="text-center text-gray-400 italic py-10 font-serif">Paket remedial akan muncul di sini...</div>
</div>
</div>

<!-- LAPORAN ORANG TUA -->
<div x-show="activeTab === 'parent-report'" x-cloak class="grid lg:grid-cols-2 gap-6">
<div class="bg-white border border-rule p-6">
    <h3 class="elite-kicker text-[.65rem] mb-3">Laporan Perkembangan untuk Orang Tua</h3>
    <form @submit.prevent="callAi('parent-report', '{{ url('/ai/teacher-assistant/parent-report') }}')" class="space-y-3">
        <select x-model="formParentReport.student_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">-- Pilih Siswa --</option>
            @foreach($students as $s)<option value="{{ $s->id }}">{{ $s->user?->name }} ({{ $s->admission_no }})</option>@endforeach
        </select>
        <input x-model="formParentReport.semester" required placeholder="Semester (contoh: Gasal 2026 / Genap 2026)" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        <select x-model="formParentReport.language" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="id">Bahasa Indonesia</option>
            <option value="en">English</option>
        </select>
        <select x-model="formParentReport.ai_model_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">-- Model AI (otomatis) --</option>
            @foreach($aiModels as $m)<option value="{{ $m->id }}">{{ $m->name ?? $m->model_name }} ({{ $m->provider?->name }})</option>@endforeach
        </select>
        <button type="submit" class="btn-elite w-full" :disabled="loading" style="padding:.6rem;font-size:.65rem;">
            <span x-show="!loading">Generate Laporan Orang Tua</span>
            <span x-show="loading">Generating...</span>
        </button>
    </form>
</div>
<div class="bg-white border border-rule p-6 overflow-auto max-h-[600px]">
    <h3 class="elite-kicker text-[.65rem] mb-3">Hasil Laporan</h3>
    <div x-show="resultParentReport" x-html="renderParentReport()" class="font-serif text-sm leading-relaxed"></div>
    <div x-show="!resultParentReport && !loading" class="text-center text-gray-400 italic py-10 font-serif">Laporan perkembangan akan muncul di sini...</div>
</div>
</div>

</div>
@push('scripts')
<script>
function aiTeacher() {
    return {
        activeTab: 'modul-ajar',
        loading: false,
        tabs: [
            { id: 'modul-ajar', label: 'Modul Ajar' },
            { id: 'rubrik', label: 'Rubrik Penilaian' },
            { id: 'worksheet', label: 'Worksheet' },
            { id: 'variasi', label: 'Variasi Soal' },
            { id: 'remedial', label: 'Remedial / Pengayaan' },
            { id: 'parent-report', label: 'Laporan Orang Tua' },
        ],
        formModulAjar: { subject_name: '', topic: '', grade_level: '', hours: 2, ai_model_id: '' },
        formRubrik: { assignment_title: '', criteria_text: '', max_score: 100, ai_model_id: '' },
        formWorksheet: { subject_name: '', topic: '', grade_level: '', question_count: 10, ai_model_id: '' },
        formVariasi: { question_id: '', variation_count: 3, ai_model_id: '' },
        formRemedial: { student_id: '', subject_name: '', weak_topics_text: '', ai_model_id: '' },
        formParentReport: { student_id: '', semester: '', language: 'id', ai_model_id: '' },
        resultModulAjar: null,
        resultRubrik: null,
        resultWorksheet: null,
        resultVariasi: null,
        resultRemedial: null,
        resultParentReport: null,

        async callAi(tool, url) {
            this.loading = true;
            try {
                let body;
                if (tool === 'modul-ajar') {
                    body = JSON.stringify(this.formModulAjar);
                } else if (tool === 'rubrik') {
                    body = JSON.stringify({
                        assignment_title: this.formRubrik.assignment_title,
                        criteria: this.formRubrik.criteria_text.split('\n').filter(s => s.trim()),
                        max_score: this.formRubrik.max_score,
                        ai_model_id: this.formRubrik.ai_model_id,
                    });
                } else if (tool === 'worksheet') {
                    body = JSON.stringify(this.formWorksheet);
                } else if (tool === 'variasi') {
                    body = JSON.stringify(this.formVariasi);
                } else if (tool === 'remedial') {
                    body = JSON.stringify({
                        student_id: this.formRemedial.student_id,
                        subject_name: this.formRemedial.subject_name,
                        weak_topics: this.formRemedial.weak_topics_text.split('\n').filter(s => s.trim()),
                        ai_model_id: this.formRemedial.ai_model_id,
                    });
                } else if (tool === 'parent-report') {
                    body = JSON.stringify({
                        student_id: this.formParentReport.student_id,
                        semester: this.formParentReport.semester,
                        language: this.formParentReport.language,
                        ai_model_id: this.formParentReport.ai_model_id,
                    });
                }
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: body,
                });
                const data = await res.json();
                if (data.success) {
                    if (tool === 'modul-ajar') this.resultModulAjar = data.parsed;
                    else if (tool === 'rubrik') this.resultRubrik = data.parsed;
                    else if (tool === 'worksheet') this.resultWorksheet = data.parsed;
                    else if (tool === 'variasi') this.resultVariasi = data.parsed;
                    else if (tool === 'parent-report') this.resultParentReport = data.parsed;
                    else if (tool === 'remedial') this.resultRemedial = data.parsed;
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                }
            } catch (e) {
                alert('Gagal menghubungi AI: ' + e.message);
            } finally {
                this.loading = false;
            }
        },

        async callAiSaveWorksheet() {
            this.loading = true;
            try {
                const res = await fetch('{{ url("/ai/teacher-assistant/worksheet/save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.formWorksheet),
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Berhasil disimpan!');
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                }
            } catch (e) {
                alert('Gagal menyimpan: ' + e.message);
            } finally {
                this.loading = false;
            }
        },

        async callAiSaveVariation() {
            this.loading = true;
            try {
                const res = await fetch('{{ url("/ai/teacher-assistant/variasi/save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.formVariasi),
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Berhasil disimpan!');
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                }
            } catch (e) {
                alert('Gagal menyimpan: ' + e.message);
            } finally {
                this.loading = false;
            }
        },

        renderModulAjar() {
            if (!this.resultModulAjar) return '';
            const d = this.resultModulAjar;
            let html = '<div class="space-y-4">';
            if (d.identitas) {
                html += '<div class="bg-gray-50 p-3 rounded"><strong class="text-xs uppercase tracking-wide text-gray-500">Identitas</strong><div class="mt-1">';
                for (const [k, v] of Object.entries(d.identitas)) {
                    html += '<div><span class="text-gray-500 text-xs">' + k.replace(/_/g, ' ') + ':</span> ' + v + '</div>';
                }
                html += '</div></div>';
            }
            if (d.tujuan_pembelajaran) {
                html += '<div><strong class="text-xs uppercase tracking-wide text-gray-500">Tujuan Pembelajaran</strong><ul class="list-disc ml-4 mt-1">';
                (Array.isArray(d.tujuan_pembelajaran) ? d.tujuan_pembelajaran : [d.tujuan_pembelajaran]).forEach(t => { html += '<li>' + t + '</li>'; });
                html += '</ul></div>';
            }
            const sections = ['pemahaman_bermakna', 'materi_pembelajaran', 'kegiatan_pembelajaran', 'asesmen', 'media_sumber', 'refleksi'];
            sections.forEach(s => {
                if (d[s]) {
                    const label = s.replace(/_/g, ' ');
                    html += '<div><strong class="text-xs uppercase tracking-wide text-gray-500">' + label + '</strong>';
                    if (typeof d[s] === 'object') {
                        if (Array.isArray(d[s])) {
                            html += '<ul class="list-disc ml-4 mt-1">';
                            d[s].forEach(v => { html += '<li>' + (typeof v === 'object' ? JSON.stringify(v) : v) + '</li>'; });
                            html += '</ul>';
                        } else {
                            html += '<div class="mt-1">';
                            for (const [k, v] of Object.entries(d[s])) {
                                html += '<div class="mb-1"><span class="text-gray-500 text-xs">' + k.replace(/_/g, ' ') + ':</span> ';
                                if (Array.isArray(v)) {
                                    html += '<ul class="list-disc ml-4">';
                                    v.forEach(item => { html += '<li>' + item + '</li>'; });
                                    html += '</ul>';
                                } else {
                                    html += v;
                                }
                                html += '</div>';
                            }
                            html += '</div>';
                        }
                    } else {
                        html += '<div class="mt-1">' + d[s] + '</div>';
                    }
                    html += '</div>';
                }
            });
            html += '</div>';
            return html;
        },

        renderRubrik() {
            if (!this.resultRubrik) return '';
            const d = this.resultRubrik;
            if (!d.criteria) return '<pre class="text-xs bg-gray-50 p-3 rounded overflow-auto">' + JSON.stringify(d, null, 2) + '</pre>';
            let html = '<div class="space-y-4">';
            html += '<h4 class="font-bold text-sm">' + (d.title || 'Rubrik Penilaian') + '</h4>';
            d.criteria.forEach(c => {
                html += '<div class="border border-rule p-3 rounded">';
                html += '<div class="font-semibold text-sm mb-2">' + c.name + ' <span class="text-xs text-gray-500">(Bobot: ' + c.weight + '%)</span></div>';
                html += '<table class="w-full text-xs border-collapse">';
                html += '<thead><tr class="bg-gray-50">';
                c.levels.forEach(l => { html += '<th class="border border-rule px-2 py-1 text-center">' + l.score + ' - ' + l.label + '</th>'; });
                html += '</tr></thead><tbody><tr>';
                c.levels.forEach(l => { html += '<td class="border border-rule px-2 py-1">' + l.description + '</td>'; });
                html += '</tr></tbody></table></div>';
            });
            html += '</div>';
            return html;
        },

        renderWorksheet() {
            if (!this.resultWorksheet) return '';
            const d = this.resultWorksheet;
            if (!d.questions) return '<pre class="text-xs bg-gray-50 p-3 rounded overflow-auto">' + JSON.stringify(d, null, 2) + '</pre>';
            let html = '<div class="space-y-4">';
            html += '<h4 class="font-bold text-sm">' + (d.title || 'Lembar Kerja') + '</h4>';
            d.questions.forEach((q, i) => {
                const diffColor = q.difficulty === 'easy' ? 'text-green-700' : (q.difficulty === 'hard' ? 'text-red-700' : 'text-amber-700');
                html += '<div class="border border-rule p-3 rounded">';
                html += '<div class="flex items-start gap-2"><span class="font-bold text-sm">' + (i + 1) + '.</span><div class="flex-1">';
                html += '<div class="font-serif">' + q.question + '</div>';
                html += '<span class="text-[.6rem] uppercase tracking-wide px-1.5 py-0.5 bg-gray-100 rounded ' + diffColor + '">' + (q.difficulty || '-') + '</span> ';
                html += '<span class="text-[.6rem] uppercase tracking-wide px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded">' + (q.cognitive_level || '-') + '</span>';
                if (q.options) {
                    html += '<div class="mt-2 space-y-1">';
                    q.options.forEach(o => { html += '<div class="text-xs ' + (o.is_correct ? 'font-bold text-green-700' : '') + '">' + o.text + '</div>'; });
                    html += '</div>';
                }
                if (q.explanation) { html += '<div class="mt-2 text-xs text-gray-500 italic">Pembahasan: ' + q.explanation + '</div>'; }
                html += '</div></div></div>';
            });
            html += '</div>';
            return html;
        },

        renderVariasi() {
            if (!this.resultVariasi) return '';
            const d = this.resultVariasi;
            if (!d.variations) return '<pre class="text-xs bg-gray-50 p-3 rounded overflow-auto">' + JSON.stringify(d, null, 2) + '</pre>';
            let html = '<div class="space-y-4">';
            html += '<h4 class="font-bold text-sm">Variasi Soal</h4>';
            d.variations.forEach((v, i) => {
                html += '<div class="border border-rule p-3 rounded">';
                html += '<div class="font-bold text-xs text-gray-500 mb-1">Variasi ' + (i + 1) + '</div>';
                html += '<div class="font-serif text-sm">' + v.question + '</div>';
                if (v.options) {
                    html += '<div class="mt-2 space-y-1">';
                    v.options.forEach(o => { html += '<div class="text-xs ' + (o.is_correct ? 'font-bold text-green-700' : '') + '">' + o.text + '</div>'; });
                    html += '</div>';
                }
                if (v.explanation) { html += '<div class="mt-2 text-xs text-gray-500 italic">Pembahasan: ' + v.explanation + '</div>'; }
                html += '</div>';
            });
            html += '</div>';
            return html;
        },

        renderRemedial() {
            if (!this.resultRemedial) return '';
            const d = this.resultRemedial;
            let html = '<div class="space-y-4">';
            html += '<h4 class="font-bold text-sm">' + (d.title || 'Paket Remedial') + '</h4>';
            if (d.diagnostic_notes) { html += '<div class="bg-yellow-50 border border-yellow-200 p-3 rounded text-xs"><strong>Diagnostik:</strong> ' + d.diagnostic_notes + '</div>'; }
            if (d.remedial_plan) {
                html += '<div class="bg-blue-50 border border-blue-200 p-3 rounded text-xs">';
                html += '<strong>Rencana Remedial:</strong><br>';
                if (d.remedial_plan.objectives) { html += 'Tujuan: ' + d.remedial_plan.objectives.join(', ') + '<br>'; }
                if (d.remedial_plan.estimated_time_minutes) { html += 'Estimasi: ' + d.remedial_plan.estimated_time_minutes + ' menit'; }
                html += '</div>';
            }
            if (d.exercises) {
                html += '<div><strong class="text-xs uppercase tracking-wide text-gray-500">Latihan</strong>';
                d.exercises.forEach((q, i) => {
                    const diffColor = q.difficulty === 'easy' ? 'text-green-700' : (q.difficulty === 'hard' ? 'text-red-700' : 'text-amber-700');
                    html += '<div class="border border-rule p-3 rounded mt-2">';
                    html += '<div class="flex items-start gap-2"><span class="font-bold text-sm">' + (i + 1) + '.</span><div class="flex-1">';
                    html += '<div class="font-serif">' + q.question + '</div>';
                    html += '<span class="text-[.6rem] uppercase px-1.5 py-0.5 bg-gray-100 rounded ' + diffColor + '">' + (q.difficulty || '-') + '</span>';
                    if (q.options) {
                        html += '<div class="mt-2 space-y-1">';
                        q.options.forEach(o => { html += '<div class="text-xs ' + (o.is_correct ? 'font-bold text-green-700' : '') + '">' + o.text + '</div>'; });
                        html += '</div>';
                    }
                    if (q.explanation) { html += '<div class="mt-2 text-xs text-gray-500 italic">' + q.explanation + '</div>'; }
                    html += '</div></div></div>';
                });
                html += '</div>';
            }
            if (d.enrichment) {
                html += '<div class="bg-purple-50 border border-purple-200 p-3 rounded text-xs"><strong>Pengayaan:</strong><ul class="list-disc ml-4 mt-1">';
                (d.enrichment.resources || []).forEach(r => { html += '<li>' + r + '</li>'; });
                html += '</ul></div>';
            }
            html += '</div>';
            return html;
        },

        renderParentReport() {
            if (!this.resultParentReport) return '';
            const d = this.resultParentReport;
            let html = '<div class="space-y-5">';
            html += '<h4 class="font-bold text-base ink-primary">' + (d.report_title || 'Laporan Perkembangan Siswa') + '</h4>';
            if (d.student_name) html += '<div class="text-xs text-gray-500">Siswa: <strong>' + d.student_name + '</strong> — Semester: <strong>' + (d.semester || '-') + '</strong></div>';

            // Academic Performance
            if (d.academic_performance) {
                const ap = d.academic_performance;
                html += '<div class="border border-rule rounded p-4">';
                html += '<h5 class="font-bold text-sm mb-2">📊 Performa Akademik</h5>';
                if (ap.summary) html += '<p class="text-xs mb-2">' + ap.summary + '</p>';
                if (ap.strengths && ap.strengths.length) {
                    html += '<div class="text-xs mb-1"><strong class="text-green-700">Kekuatan:</strong></div><ul class="list-disc ml-4 text-xs space-y-0.5">';
                    ap.strengths.forEach(s => { html += '<li>' + s + '</li>'; });
                    html += '</ul>';
                }
                if (ap.areas_for_improvement && ap.areas_for_improvement.length) {
                    html += '<div class="text-xs mt-2 mb-1"><strong class="text-amber-700">Perlu Diperbaiki:</strong></div><ul class="list-disc ml-4 text-xs space-y-0.5">';
                    ap.areas_for_improvement.forEach(a => { html += '<li>' + a + '</li>'; });
                    html += '</ul>';
                }
                html += '</div>';
            }

            // Attendance
            if (d.attendance_summary) {
                const at = d.attendance_summary;
                html += '<div class="border border-rule rounded p-4">';
                html += '<h5 class="font-bold text-sm mb-2">📋 Kehadiran</h5>';
                if (at.summary) html += '<p class="text-xs mb-2">' + at.summary + '</p>';
                html += '<div class="grid grid-cols-3 gap-2 text-xs">';
                html += '<div class="bg-green-50 rounded p-2 text-center"><div class="font-bold text-green-700">' + (at.present_days || 0) + '</div>Hadir</div>';
                html += '<div class="bg-yellow-50 rounded p-2 text-center"><div class="font-bold text-yellow-700">' + (at.late_days || 0) + '</div>Terlambat</div>';
                html += '<div class="bg-red-50 rounded p-2 text-center"><div class="font-bold text-red-700">' + (at.absent_days || 0) + '</div>Absen</div>';
                html += '</div>';
                html += '</div>';
            }

            // Behavior
            if (d.behavioral_observations) {
                const bo = d.behavioral_observations;
                html += '<div class="border border-rule rounded p-4">';
                html += '<h5 class="font-bold text-sm mb-2">🌟 Perilaku & Kedisiplinan</h5>';
                if (bo.summary) html += '<p class="text-xs mb-2">' + bo.summary + '</p>';
                if (bo.positive_behaviors && bo.positive_behaviors.length) {
                    html += '<ul class="list-disc ml-4 text-xs space-y-0.5">';
                    bo.positive_behaviors.forEach(p => { html += '<li class="text-green-700">' + p + '</li>'; });
                    html += '</ul>';
                }
                html += '</div>';
            }

            // Highlights
            if (d.highlights && d.highlights.length) {
                html += '<div class="bg-amber-50 border border-amber-200 rounded p-4">';
                html += '<h5 class="font-bold text-sm mb-2">🏆 Prestasi & Momen Positif</h5><ul class="list-disc ml-4 text-xs space-y-0.5">';
                d.highlights.forEach(h => { html += '<li>' + h + '</li>'; });
                html += '</ul></div>';
            }

            // Recommendations
            if (d.recommendations && d.recommendations.length) {
                html += '<div class="border border-rule rounded p-4">';
                html += '<h5 class="font-bold text-sm mb-2">💡 Rekomendasi</h5>';
                d.recommendations.forEach((r, i) => {
                    html += '<div class="flex gap-2 text-xs mb-2"><span class="font-bold text-[var(--c-primary)]">' + (i + 1) + '.</span><div><strong>' + (r.area || '') + ':</strong> ' + (r.action || '') + ' <span class="text-gray-400">(' + (r.by_whom || '') + ')</span></div></div>';
                });
                html += '</div>';
            }

            // Overall
            if (d.overall_assessment) {
                html += '<div class="bg-blue-50 border border-blue-200 rounded p-4 text-xs"><strong>Penilaian Keseluruhan:</strong><br>' + d.overall_assessment + '</div>';
            }
            if (d.encouragement_message) {
                html += '<div class="bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded p-4 text-xs italic text-center">' + d.encouragement_message + '</div>';
            }

            html += '</div>';
            return html;
        },
    }
}
</script>
@endpush

@extends('layouts.school-admin')
@section('title', 'Hasil Tugas: ' . $assignment->title)
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<a href="{{ route('admin.assignments.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kembali</a>
<div class="mb-7"><div class="elite-kicker mb-2">Operationes</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">{{ $assignment->title }}</h1><div class="elite-rule"></div></div>

<div class="elite-card p-5 mb-6 grid md:grid-cols-4 gap-3 text-sm">
    <div><span class="elite-kicker text-[.55rem]">Mata Pelajaran</span><br><span class="font-serif">{{ $assignment->lesson?->subject?->name }}</span></div>
    <div><span class="elite-kicker text-[.55rem]">Tipe</span><br>{{ match($assignment->question_type){'multiple_choice'=>'PG','mixed'=>'Campuran',default:'Essay'} }} {{ $assignment->auto_grade ? '(Auto-grade)' : '' }}</div>
    <div><span class="elite-kicker text-[.55rem]">Deadline</span><br>{{ $assignment->due_date?->format('d M Y H:i') }}</div>
    <div><span class="elite-kicker text-[.55rem]">Nilai Max</span><br><span class="font-mono font-bold">{{ $assignment->total_marks }}</span></div>
</div>

@if($assignment->questions->isNotEmpty())
<div class="elite-card p-5 mb-6">
    <h3 class="elite-h3 text-lg ink-primary mb-3">Kunci Jawaban</h3>
    <div class="table-scroll"><table class="w-full text-xs table-elite"><thead><tr><th>#</th><th>Soal</th><th>Jawaban</th><th>Poin</th></tr></thead><tbody>
    @foreach($assignment->questions as $q)
    <tr><td class="font-mono">{{ $q->question_number }}</td><td>{{ \Illuminate\Support\Str::limit($q->question_text, 60) }}</td>
    <td class="font-mono font-bold">{{ $q->correct_answer ?? '—' }}</td><td>{{ $q->points }}</td></tr>
    @endforeach
    </tbody></table></div>
</div>
@endif

<div class="flex flex-wrap gap-2 mb-5">
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari siswa..." class="border-2 border-rule px-3 py-2 text-sm w-48">
        <button class="btn-elite text-xs">Cari</button>
    </form>
</div>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Kelas</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Nilai</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Telat</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Feedback</th>
<th></th></tr></thead><tbody>
@forelse($submissions as $s)<tr class="border-t border-rule">
<td class="px-3 py-3"><div class="font-serif font-semibold">{{ $s->student?->user?->name }}</div></td>
<td class="px-3 py-3 text-xs">{{ $s->student?->classSection?->classRoom?->name }} {{ $s->student?->classSection?->section?->name }}</td>
<td class="px-3 py-3 text-right font-mono font-bold">{{ $s->marks !== null ? $s->marks . '/' . $assignment->total_marks : '—' }}</td>
<td class="px-3 py-3 text-center text-xs">{{ $s->is_late ? '⚠️ Telat' : '✓' }}</td>
<td class="px-3 py-3 text-xs text-gray-600 max-w-xs truncate">{{ $s->feedback ?? '—' }}</td>
<td class="px-3 py-3 text-right space-x-1">
    <button onclick="openGrade({{ $s->id }}, {{ $s->marks ?? 'null' }}, '{{ addslashes($s->feedback ?? '') }}', {{ $assignment->total_marks }})" class="text-xs underline ink-accent hover:ink-primary">Nilai</button>
    <form method="POST" action="{{ route('admin.assignments.submissions.destroy', $s) }}" class="inline" onsubmit="return confirm('Hapus pengumpulan?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
</td>
</tr>@empty<tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada yang mengumpulkan.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $submissions->links() }}</div>

{{-- Grade Modal --}}
<div x-data="gradeModal()" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(11,29,58,.75)">
    <div @click.outside="open = false" class="bg-white w-full max-w-md mx-4 shadow-2xl border border-rule p-6">
        <div class="elite-h3 text-lg ink-primary mb-4">Beri Nilai</div>
        <form method="POST" :action="'/admin/classroom/assignments/submissions/' + submissionId + '/grade'">
            @csrf
            <input type="number" name="marks" x-model="marks" min="0" :max="maxMarks" step="0.5" required class="border-2 border-rule px-3 py-2 font-mono text-lg w-full mb-3" placeholder="Nilai">
            <textarea name="feedback" x-model="feedback" rows="3" class="border-2 border-rule px-3 py-2 font-serif text-sm w-full mb-3" placeholder="Feedback..."></textarea>
            <div class="flex gap-2">
                <button class="btn-elite text-xs">Simpan Nilai</button>
                <button type="button" @click="open = false" class="btn-elite-ghost text-xs">Batal</button>
            </div>
        </form>
    </div>
</div>
<script>
function gradeModal() {
    return { open: false, submissionId: null, marks: 0, feedback: '', maxMarks: 100 };
}
function openGrade(id, currentMarks, currentFeedback, maxMarks) {
    const modal = document.querySelector('[x-data="gradeModal()"]').__x.$data;
    modal.submissionId = id;
    modal.marks = currentMarks ?? '';
    modal.feedback = currentFeedback ?? '';
    modal.maxMarks = maxMarks;
    modal.open = true;
}
</script>
@endsection

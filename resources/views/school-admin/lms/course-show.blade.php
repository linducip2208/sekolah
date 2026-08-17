@extends('layouts.school-admin')
@section('title', 'Detail Kursus')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.courses.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kursus</a>

<div class="mb-7">
    <div class="elite-kicker mb-2">{{ $course->icon ?? '📘' }}</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">{{ $course->title }}</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">{{ $course->description }}</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif

<div class="grid md:grid-cols-3 gap-6">
    <div class="md:col-span-2">
        @foreach($course->modules as $module)
        <div class="bg-white border border-rule mb-4 overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b border-rule flex items-center justify-between">
                <span class="font-serif font-semibold">{{ $module->order }}. {{ $module->title }}</span>
                <form method="POST" action="{{ route('admin.courses.modules.destroy', $module) }}" onsubmit="return confirm('Hapus modul?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
            </div>
            <div class="divide-y divide-rule">
                @forelse($module->lessons as $lesson)
                <div class="px-4 py-3 flex items-center justify-between">
                    <div>
                        <span class="font-serif text-sm">{{ $lesson->title }}</span>
                        @if($lesson->duration_minutes)<span class="text-xs text-gray-400 ml-2">{{ $lesson->duration_minutes }} mnt</span>@endif
                        @if($lesson->video_url)<span class="text-xs text-violet-600 ml-2">🎬 video</span>@endif
                    </div>
                    <form method="POST" action="{{ route('admin.courses.lessons.destroy', $lesson) }}" onsubmit="return confirm('Hapus materi?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
                </div>
                @empty
                <div class="px-4 py-3 text-xs text-gray-400 italic">Belum ada materi.</div>
                @endforelse
            </div>
            <details class="px-4 py-3 border-t border-rule">
                <summary class="elite-kicker text-[.6rem] cursor-pointer">+ Tambah Materi</summary>
                <form method="POST" action="{{ route('admin.courses.lessons.store', $module) }}" class="mt-2 grid gap-2">@csrf
                    <input name="title" required maxlength="200" placeholder="Judul materi" class="border-2 border-rule px-3 py-2 font-serif text-sm">
                    <input name="video_url" placeholder="URL video (opsional)" class="border-2 border-rule px-3 py-2 font-mono text-xs">
                    <input type="number" name="duration_minutes" min="0" max="600" placeholder="Durasi (menit)" class="border-2 border-rule px-3 py-2 font-mono text-xs">
                    <textarea name="content_html" rows="4" placeholder="Isi materi (HTML)" class="border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
                    <button class="btn-elite" style="padding:.4rem 1rem;font-size:.65rem;">Simpan</button>
                </form>
            </details>
        </div>
        @endforeach

        <details class="mb-4 bg-white border border-rule">
            <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Modul</summary>
            <form method="POST" action="{{ route('admin.courses.modules.store', $course) }}" class="px-5 py-4 border-t border-rule grid gap-2">@csrf
                <input name="title" required maxlength="200" placeholder="Judul modul" class="border-2 border-rule px-3 py-2 font-serif text-sm">
                <input name="description" placeholder="Deskripsi modul" class="border-2 border-rule px-3 py-2 font-serif text-sm">
                <button class="btn-elite" style="padding:.4rem 1rem;font-size:.65rem;">Simpan Modul</button>
            </form>
        </details>
    </div>

    <div>
        <div class="bg-white border border-rule p-4 mb-4">
            <div class="elite-kicker text-[.7rem] mb-2">Daftarkan Siswa</div>
            <form method="POST" action="{{ route('admin.courses.enroll', $course) }}">@csrf
                <div class="max-h-64 overflow-y-auto border border-rule p-2 space-y-1 mb-3">
                    @foreach($students as $s)
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="student_ids[]" value="{{ $s->id }}" @checked(in_array($s->id, $enrolledIds))> {{ $s->admission_no }} · {{ $s->user?->name }}</label>
                    @endforeach
                </div>
                <button class="btn-elite" style="padding:.4rem 1rem;font-size:.65rem;">Simpan Pendaftaran</button>
            </form>
        </div>

        <div class="bg-white border border-rule overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b border-rule elite-kicker text-[.7rem]">Progres Siswa ({{ $course->enrollments->count() }})</div>
            <table class="w-full text-sm">
                <tbody>
                    @forelse($course->enrollments as $e)
                    <tr class="border-b border-rule">
                        <td class="px-3 py-2 font-serif text-xs">{{ $e->student?->user?->name }}</td>
                        <td class="px-3 py-2">
                            <div class="h-2 bg-gray-100 rounded overflow-hidden"><div class="h-full bg-green-500" style="width:{{ $e->progress_pct }}%"></div></div>
                        </td>
                        <td class="px-3 py-2 text-xs font-mono">{{ $e->progress_pct }}%</td>
                        <td class="px-3 py-2 text-right whitespace-nowrap">
                            @if($e->certificate)
                                <a href="{{ route('admin.courses.certificates.show', $e->certificate) }}" class="text-xs underline ink-secondary mr-2">Sertifikat</a>
                            @elseif($e->progress_pct >= 100)
                                <form method="POST" action="{{ route('admin.courses.enrollments.certificate', $e) }}" class="inline mr-2">@csrf<button class="text-xs underline ink-secondary">Terbitkan Sertifikat</button></form>
                            @endif
                            <form method="POST" action="{{ route('admin.courses.enrollments.destroy', $e) }}" class="inline" onsubmit="return confirm('Hapus pendaftaran?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
                        </td>
                    </tr>
                    @empty
                    <tr><td class="p-6 text-center text-gray-400 italic font-serif text-xs">Belum ada siswa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

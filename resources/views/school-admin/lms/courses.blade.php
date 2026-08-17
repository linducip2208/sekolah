@extends('layouts.school-admin')
@section('title', 'Kursus (LMS)')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Academia</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Kursus (LMS)</h1>
    <div class="elite-rule"></div>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Buat Kursus</summary>
    <form method="POST" action="{{ route('admin.courses.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-2 gap-3">@csrf
        <input name="title" required maxlength="200" placeholder="Judul kursus" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <input name="icon" maxlength="50" placeholder="Emoji/icon (mis. 🧮)" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <select name="subject_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— mapel (opsional) —</option>
            @foreach(\App\Models\Academic\Subject::where('school_id', auth()->user()->school_id)->orderBy('name')->get() as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
            @endforeach
        </select>
        <select name="prerequisite_course_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— prasyarat (opsional) —</option>
            @foreach($courses as $c)<option value="{{ $c->id }}">{{ $c->title }}</option>@endforeach
        </select>
        <div class="flex items-center gap-2"><input type="checkbox" name="is_published" value="1" id="c-pub" class="w-4 h-4"><label for="c-pub" class="text-sm font-serif">Publish</label></div>
        <textarea name="description" rows="3" placeholder="Deskripsi kursus" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <div class="md:col-span-2"><button class="btn-elite">Buat Kursus</button></div>
    </form>
</details>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($courses as $c)
    <div class="bg-white border border-rule p-5 card-lift">
        <div class="flex items-start justify-between">
            <div class="text-3xl">{{ $c->icon ?? '📘' }}</div>
            @if($c->is_published)<span class="text-xs text-green-700">✓ Publish</span>@else<span class="text-xs text-gray-400">Draft</span>@endif
        </div>
        <a href="{{ route('admin.courses.show', $c) }}" class="font-serif font-semibold text-lg ink-primary hover:underline block mt-2">{{ $c->title }}</a>
        <p class="text-xs text-gray-500 mt-1">{{ Str::limit($c->description, 80) }}</p>
        <div class="flex gap-3 mt-3 text-xs text-gray-500">
            <span>{{ $c->modules_count }} modul</span>
            <span>{{ $c->lessons_count }} materi</span>
            <span>{{ $c->enrollments_count }} siswa</span>
        </div>
    </div>
    @empty
    <div class="md:col-span-3 bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada kursus.</div>
    @endforelse
</div>

@endsection

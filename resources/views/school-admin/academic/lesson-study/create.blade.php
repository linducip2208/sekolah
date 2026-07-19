@extends('layouts.school-admin')
@section('title', isset($lessonStudy) ? 'Edit Lesson Study' : 'Buat Lesson Study')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Studium Collaborativum</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">{{ isset($lessonStudy) ? 'Edit' : 'Buat' }} Lesson Study</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Fase Plan — rencanakan pembelajaran yang akan diobservasi bersama.</p>
</div>

<div class="max-w-2xl">
    <div class="bg-white border border-rule p-6">
        @if($errors->any())<div class="mb-4 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ isset($lessonStudy) ? route('admin.lesson-study.update', $lessonStudy) : route('admin.lesson-study.store') }}" class="space-y-4">
            @csrf
            @if(isset($lessonStudy)) @method('PUT') @endif

            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Judul Studi</label>
                <input name="title" required maxlength="255" value="{{ old('title', $lessonStudy->title ?? '') }}" placeholder="Pembelajaran Matematika Topik Aljabar" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Mata Pelajaran</label>
                    <select name="subject_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— pilih —</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" {{ old('subject_id', $lessonStudy->subject_id ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Rombel</label>
                    <select name="class_section_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— pilih —</option>
                        @foreach($classSections as $cs)
                            <option value="{{ $cs->id }}" {{ old('class_section_id', $lessonStudy->class_section_id ?? '') == $cs->id ? 'selected' : '' }}>
                                {{ $cs->classRoom->name }} {{ $cs->section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Topik Pembelajaran</label>
                <input name="topic" maxlength="255" value="{{ old('topic', $lessonStudy->topic ?? '') }}" placeholder="Operasi Aljabar Dasar" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>

            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Guru Model (Lead Teacher)</label>
                <select name="lead_teacher_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="">— pilih —</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ old('lead_teacher_id', $lessonStudy->lead_teacher_id ?? '') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Observer / Anggota Tim (opsional, bisa ditambah nanti)</label>
                <select name="member_ids[]" multiple class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" style="min-height:120px;">
                    @foreach($teachers as $t)
                        @php
                            $memberIds = old('member_ids', isset($lessonStudy) ? $lessonStudy->members->pluck('staff_id')->toArray() : []);
                        @endphp
                        <option value="{{ $t->id }}" {{ in_array($t->id, $memberIds) ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">Ctrl+klik untuk multi-pilih</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tanggal Plan</label>
                    <input type="date" name="plan_date" value="{{ old('plan_date', isset($lessonStudy) && $lessonStudy->plan_date ? $lessonStudy->plan_date->format('Y-m-d') : now()->format('Y-m-d')) }}" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tanggal Mengajar</label>
                    <input type="date" name="teach_date" value="{{ old('teach_date', isset($lessonStudy) && $lessonStudy->teach_date ? $lessonStudy->teach_date->format('Y-m-d') : '') }}" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
            </div>

            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Deskripsi</label>
                <textarea name="description" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">{{ old('description', $lessonStudy->description ?? '') }}</textarea>
            </div>

            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Catatan Perencanaan</label>
                <textarea name="plan_notes" rows="3" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Tujuan pembelajaran, langkah-langkah, media, asesmen...">{{ old('plan_notes', $lessonStudy->plan_notes ?? '') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="{{ route('admin.lesson-study.index') }}" class="btn-elite-ghost">Batal</a>
                <button class="btn-elite">{{ isset($lessonStudy) ? 'Simpan' : 'Buat Lesson Study' }}</button>
            </div>
        </form>
    </div>
</div>

@endsection

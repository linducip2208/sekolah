@extends('layouts.school-admin')
@section('title', isset($training) ? 'Edit Pelatihan' : 'Buat Pelatihan Baru')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Professio Perennis</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">{{ isset($training) ? 'Edit' : 'Buat' }} Pelatihan</h1>
    <div class="elite-rule"></div>
</div>

<div class="max-w-2xl">
    <div class="bg-white border border-rule p-6">
        @if($errors->any())<div class="mb-4 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ isset($training) ? route('admin.training.update', $training) : route('admin.training.store') }}" class="space-y-4">
            @csrf
            @if(isset($training)) @method('PUT') @endif

            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Judul Pelatihan</label>
                <input name="title" required maxlength="255" value="{{ old('title', $training->title ?? '') }}" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Penyelenggara</label>
                    <input name="provider" maxlength="255" value="{{ old('provider', $training->provider ?? '') }}" placeholder="Kemendikbud / LPMP / Mandiri" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Jenis</label>
                    <select name="training_type" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        @foreach(['seminar'=>'Seminar','workshop'=>'Workshop','diklat'=>'Diklat','online'=>'Online','sertifikasi'=>'Sertifikasi'] as $val => $label)
                            <option value="{{ $val }}" {{ old('training_type', $training->training_type ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" required value="{{ old('start_date', isset($training) ? $training->start_date->format('Y-m-d') : '') }}" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ old('end_date', isset($training) && $training->end_date ? $training->end_date->format('Y-m-d') : '') }}" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Durasi (jam)</label>
                    <input type="number" name="duration_hours" value="{{ old('duration_hours', $training->duration_hours ?? '') }}" min="1" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
                </div>
            </div>

            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Lokasi</label>
                <input name="location" maxlength="255" value="{{ old('location', $training->location ?? '') }}" placeholder="Aula Sekolah / Zoom / Google Meet" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>

            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Template Teks Sertifikat</label>
                <textarea name="certificate_template" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Diberikan kepada {nama} atas partisipasinya dalam {pelatihan}...">{{ old('certificate_template', $training->certificate_template ?? '') }}</textarea>
            </div>

            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Deskripsi</label>
                <textarea name="description" rows="3" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">{{ old('description', $training->description ?? '') }}</textarea>
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_mandatory" value="0">
                <input type="checkbox" name="is_mandatory" value="1" {{ old('is_mandatory', $training->is_mandatory ?? false) ? 'checked' : '' }} class="w-4 h-4">
                <span class="font-serif text-sm text-gray-700">Pelatihan Wajib</span>
            </label>

            <div class="flex gap-3 pt-2">
                <a href="{{ route('admin.training.index') }}" class="btn-elite-ghost">Batal</a>
                <button class="btn-elite">{{ isset($training) ? 'Simpan' : 'Buat Pelatihan' }}</button>
            </div>
        </form>
    </div>
</div>

@endsection

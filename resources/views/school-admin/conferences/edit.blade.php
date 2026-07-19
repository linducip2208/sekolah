@extends('layouts.school-admin')
@section('title', 'Edit Sesi Konferensi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.conferences.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kembali</a>

<div class="mb-7 max-w-3xl">
    <div class="elite-kicker mb-2">Edit Conferentia</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Edit {{ $session->title }}</h1>
    <div class="elite-rule"></div>
</div>

<div class="max-w-3xl">
    <div class="bg-white border border-rule p-7 space-y-5">
        <form method="POST" action="{{ route('admin.conferences.update', $session) }}">
            @csrf @method('PUT')

            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Judul</label>
                <input name="title" required maxlength="255" value="{{ old('title', $session->title) }}"
                       class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>

            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Deskripsi</label>
                <textarea name="description" rows="4" maxlength="5000"
                          class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">{{ old('description', $session->description) }}</textarea>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tipe Konferensi</label>
                    <select name="conference_type" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="individual" @selected(old('conference_type', $session->conference_type) === 'individual')>Individual</option>
                        <option value="group" @selected(old('conference_type', $session->conference_type) === 'group')>Grup</option>
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Durasi per Slot (menit)</label>
                    <input type="number" name="duration_minutes" required min="5" max="120" value="{{ old('duration_minutes', $session->duration_minutes) }}"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tanggal</label>
                    <input type="date" name="date" required value="{{ old('date', $session->date->format('Y-m-d')) }}"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Jam Mulai</label>
                    <input type="time" name="start_time" required value="{{ old('start_time', $session->start_time) }}"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Jam Selesai</label>
                    <input type="time" name="end_time" required value="{{ old('end_time', $session->end_time) }}"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Maksimal Booking</label>
                    <input type="number" name="max_bookings" min="1" value="{{ old('max_bookings', $session->max_bookings) }}"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Lokasi</label>
                    <select name="location" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="physical" @selected(old('location', $session->location) === 'physical')>Fisik</option>
                        <option value="online" @selected(old('location', $session->location) === 'online')>Online</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Ruangan / Link Meeting</label>
                <input name="location_detail" maxlength="255" value="{{ old('location_detail', $session->location_detail) }}"
                       class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>

            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Link Meeting (jika online)</label>
                <input type="url" name="meeting_link" maxlength="500" value="{{ old('meeting_link', $session->meeting_link) }}"
                       class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $session->is_published))>
                <span class="text-sm">Publikasikan</span>
            </label>

            <div class="pt-5 border-t border-rule flex gap-3">
                <button class="btn-elite">Simpan</button>
                <a href="{{ route('admin.conferences.index') }}" class="btn-elite-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>

@endsection

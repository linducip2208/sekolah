@extends('layouts.school-admin')
@section('title', 'Buat Sesi Konferensi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.conferences.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kembali</a>

<div class="mb-7 max-w-3xl">
    <div class="elite-kicker mb-2">Nova Conferentia</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Sesi Konferensi Baru</h1>
    <div class="elite-rule"></div>
</div>

<div class="max-w-3xl">
    <div class="bg-white border border-rule p-7 space-y-5">
        <form method="POST" action="{{ route('admin.conferences.store') }}">
            @csrf

            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Judul</label>
                <input name="title" required maxlength="255" value="{{ old('title') }}"
                       class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>

            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Deskripsi</label>
                <textarea name="description" rows="4" maxlength="5000"
                          class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">{{ old('description') }}</textarea>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tipe Konferensi</label>
                    <select name="conference_type" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="individual" @selected(old('conference_type') === 'individual')>Individual (1 orang tua per slot)</option>
                        <option value="group" @selected(old('conference_type') === 'group')>Grup (bisa lebih dari 1 per slot)</option>
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Durasi per Slot (menit)</label>
                    <input type="number" name="duration_minutes" required min="5" max="120" value="{{ old('duration_minutes', 15) }}"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tanggal</label>
                    <input type="date" name="date" required value="{{ old('date') }}"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Jam Mulai</label>
                    <input type="time" name="start_time" required value="{{ old('start_time') }}"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Jam Selesai</label>
                    <input type="time" name="end_time" required value="{{ old('end_time') }}"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Maksimal Booking (kosong = unlimited)</label>
                    <input type="number" name="max_bookings" min="1" value="{{ old('max_bookings') }}"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Lokasi</label>
                    <select name="location" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="physical" @selected(old('location') === 'physical')>Fisik (ruangan sekolah)</option>
                        <option value="online" @selected(old('location') === 'online')>Online (Zoom/Meet)</option>
                    </select>
                </div>
            </div>

            <div id="physical-fields">
                <label class="elite-kicker text-[.6rem] block mb-1">Ruangan</label>
                <input name="location_detail" maxlength="255" value="{{ old('location_detail') }}"
                       class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Contoh: Ruang Rapat Lantai 2">
            </div>

            <div id="online-fields" style="display:none;">
                <label class="elite-kicker text-[.6rem] block mb-1">Link Meeting (Zoom/Google Meet)</label>
                <input type="url" name="meeting_link" maxlength="500" value="{{ old('meeting_link') }}"
                       class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="https://meet.google.com/xxx">
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_published" value="1" @checked(old('is_published', false))>
                <span class="text-sm">Publikasikan langsung</span>
            </label>

            <div class="pt-5 border-t border-rule flex gap-3">
                <button class="btn-elite">Buat Sesi</button>
                <a href="{{ route('admin.conferences.index') }}" class="btn-elite-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.querySelector('select[name="location"]').addEventListener('change', function() {
    document.getElementById('physical-fields').style.display = this.value === 'physical' ? '' : 'none';
    document.getElementById('online-fields').style.display = this.value === 'online' ? '' : 'none';
});
document.querySelector('select[name="location"]').dispatchEvent(new Event('change'));
</script>
@endpush

@endsection

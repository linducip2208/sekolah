@extends('layouts.school-admin')
@section('title', 'Pesan Broadcast Baru')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Communication</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Pesan Broadcast Baru</h1>
    <div class="elite-rule"></div>
</div>

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-sm text-red-800">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('admin.broadcast.store') }}" class="max-w-2xl">
    @csrf
    <div class="bg-white border border-rule p-6 space-y-4">
        <div>
            <label class="text-xs text-gray-500 mb-1 block font-semibold">Judul</label>
            <input name="title" value="{{ old('title') }}" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Judul pesan broadcast">
        </div>

        <div>
            <label class="text-xs text-gray-500 mb-1 block font-semibold">Pesan</label>
            <textarea name="message" rows="5" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Isi pesan broadcast...">{{ old('message') }}</textarea>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-gray-500 mb-1 block font-semibold">Channel</label>
                <select name="channel" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="all" {{ old('channel') === 'all' ? 'selected' : '' }}>Semua Channel</option>
                    <option value="email" {{ old('channel') === 'email' ? 'selected' : '' }}>Email</option>
                    <option value="push" {{ old('channel') === 'push' ? 'selected' : '' }}>Push Notification</option>
                    <option value="sms" {{ old('channel') === 'sms' ? 'selected' : '' }}>SMS</option>
                    <option value="whatsapp" {{ old('channel') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                </select>
            </div>

            <div>
                <label class="text-xs text-gray-500 mb-1 block font-semibold">Segment Penerima</label>
                <select name="segment" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="all" {{ old('segment') === 'all' ? 'selected' : '' }}>Semua</option>
                    <option value="students" {{ old('segment') === 'students' ? 'selected' : '' }}>Siswa</option>
                    <option value="parents" {{ old('segment') === 'parents' ? 'selected' : '' }}>Orang Tua</option>
                    <option value="teachers" {{ old('segment') === 'teachers' ? 'selected' : '' }}>Guru</option>
                    <option value="staff" {{ old('segment') === 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="custom" {{ old('segment') === 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>
        </div>

        <div>
            <label class="text-xs text-gray-500 mb-1 block font-semibold">Jadwalkan Kirim (opsional)</label>
            <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            <p class="text-[.6rem] text-gray-400 mt-1">Kosongkan untuk draft. Isi untuk menjadwalkan.</p>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-elite" style="padding:.6rem 1.5rem;font-size:.65rem;">Simpan</button>
            <a href="{{ route('admin.broadcast.index') }}" class="px-4 py-2 text-sm text-gray-600 border border-rule hover:bg-gray-50">Batal</a>
        </div>
    </div>
</form>

@endsection

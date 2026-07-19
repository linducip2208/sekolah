@extends('elite.layout')
@section('title', 'Pendaftaran Kunjungan')
@section('description', 'Daftar kunjungan ke sekolah secara online')
@section('header')
<header class="bg-gradient-to-br from-indigo-800 via-indigo-900 to-stone-900 text-white py-16 px-4">
    <div class="max-w-3xl mx-auto text-center">
        <h1 class="font-display text-4xl sm:text-5xl font-bold mb-4">Pendaftaran Kunjungan</h1>
        <p class="text-indigo-200 text-lg">Isi formulir di bawah untuk mendaftar kunjungan ke sekolah. Anda akan menerima QR Code untuk check-in di gerbang.</p>
    </div>
</header>
@endsection
@section('content')
<div class="max-w-2xl mx-auto px-4 py-12">
    <div class="bg-white border border-rule shadow-sm p-6 sm:p-8">
        @if(session('success'))
            <div class="mb-6 px-4 py-3 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 font-serif text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-6 px-4 py-3 bg-red-50 border-l-4 border-red-500 text-red-700 font-serif text-sm">
                <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('visitor.register.submit') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-stone-700 mb-1.5">Nama Lengkap *</label>
                <input name="visitor_name" required maxlength="200" placeholder="Nama Anda" class="w-full border-2 border-stone-200 rounded-xl px-4 py-3 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-stone-700 mb-1.5">Nomor HP / WhatsApp *</label>
                <input name="phone" required maxlength="30" placeholder="62812..." class="w-full border-2 border-stone-200 rounded-xl px-4 py-3 text-sm font-mono focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-stone-700 mb-1.5">Tujuan Kunjungan *</label>
                <input name="purpose" required maxlength="200" placeholder="e.g. Menemui guru BK, Mengantar anak, Meeting..." class="w-full border-2 border-stone-200 rounded-xl px-4 py-3 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-stone-700 mb-1.5">Staff yang Dituju</label>
                <select name="host_staff_id" class="w-full border-2 border-stone-200 rounded-xl px-4 py-3 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
                    <option value="">-- Pilih Staff --</option>
                    @foreach($staff as $s)
                        <option value="{{ $s->id }}">{{ $s->user?->name ?? 'Staff #'.$s->id }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-stone-700 mb-1.5">Estimasi Kedatangan *</label>
                <input type="datetime-local" name="expected_arrival" required class="w-full border-2 border-stone-200 rounded-xl px-4 py-3 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-stone-700 mb-1.5">Plat Kendaraan (opsional)</label>
                <input name="vehicle_plate" maxlength="20" placeholder="e.g. B 1234 ABC" class="w-full border-2 border-stone-200 rounded-xl px-4 py-3 text-sm font-mono focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
            </div>
            <button type="submit" class="w-full py-3 bg-indigo-700 hover:bg-indigo-800 text-white font-semibold text-sm rounded-xl transition shadow-md shadow-indigo-200">
                Daftar Kunjungan
            </button>
        </form>
    </div>
</div>
@endsection

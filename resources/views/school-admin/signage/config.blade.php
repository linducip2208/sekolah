@extends('layouts.school-admin')
@section('title', 'Konfigurasi Digital Signage')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Tabula Praeconaria</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Digital Signage</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Konfigurasi tampilan layar publik untuk lobi sekolah, koridor, atau TV informasi.</p>
</div>

@php
    $school = \App\Models\School::find(auth()->user()->school_id);
    $signage = $school->getSetting('signage', []);
@endphp

<div class="grid lg:grid-cols-2 gap-6">
    <div>
        <div class="bg-white border border-rule p-6">
            <h3 class="elite-h3 text-base ink-primary mb-4">Widget yang Ditampilkan</h3>
            <form method="POST" action="{{ route('admin.signage.config.save') }}" class="space-y-4">
                @csrf
                @php
                $widgets = [
                    ['key' => 'show_announcements', 'label' => 'Pengumuman Terbaru', 'desc' => 'Tampilkan pengumuman bergulir'],
                    ['key' => 'show_schedule', 'label' => 'Jadwal Hari Ini', 'desc' => 'Tampilkan jadwal pelajaran hari ini'],
                    ['key' => 'show_achievements', 'label' => 'Prestasi Minggu Ini', 'desc' => 'Slide prestasi siswa terbaru'],
                    ['key' => 'show_events', 'label' => 'Event Mendatang', 'desc' => 'Tampilkan agenda & acara sekolah'],
                    ['key' => 'show_prayer_times', 'label' => 'Jadwal Sholat', 'desc' => 'Untuk madrasah / pesantren'],
                    ['key' => 'show_clock', 'label' => 'Jam Digital', 'desc' => 'Tampilkan jam & tanggal realtime'],
                    ['key' => 'show_weather', 'label' => 'Info Cuaca', 'desc' => 'Tampilkan info cuaca terkini'],
                    ['key' => 'show_ticker', 'label' => 'Ticker Berita', 'desc' => 'Teks berjalan di bagian bawah layar'],
                ];
                @endphp

                @foreach($widgets as $w)
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="hidden" name="{{ $w['key'] }}" value="0">
                    <input type="checkbox" name="{{ $w['key'] }}" value="1" {{ ($signage[$w['key']] ?? true) ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 border-2 border-rule text-[var(--c-accent)] focus:ring-[var(--c-accent)]">
                    <div>
                        <div class="font-serif text-sm ink-primary font-semibold">{{ $w['label'] }}</div>
                        <div class="text-xs text-gray-500">{{ $w['desc'] }}</div>
                    </div>
                </label>
                @endforeach

                <hr class="border-rule">

                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Motto Sekolah</label>
                    <input type="text" name="school_motto" value="{{ $signage['school_motto'] ?? $school->name }}" maxlength="200"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>

                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Teks Ticker</label>
                    <input type="text" name="ticker_text" value="{{ $signage['ticker_text'] ?? '' }}" maxlength="500"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Selamat datang di {{ $school->name }}">
                </div>

                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Interval Refresh (detik)</label>
                    <input type="number" name="refresh_interval" value="{{ $signage['refresh_interval'] ?? 60 }}" min="10" max="600"
                           class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
                </div>

                <button class="btn-elite" style="padding:.6rem 1.2rem;font-size:.65rem;">Simpan Konfigurasi</button>
            </form>
        </div>
    </div>

    <div>
        <div class="bg-white border border-rule p-6">
            <h3 class="elite-h3 text-base ink-primary mb-4">Preview & URL Publik</h3>

            <div class="bg-[#0a0a0a] border border-gray-700 p-4 text-center mb-4">
                <div class="text-white font-mono text-xs mb-2">LAYAR TV SEKOLAH</div>
                <div class="w-full h-32 flex items-center justify-center text-gray-500 font-serif italic text-sm">
                    <a href="{{ route('signage.display', $school->id) }}" target="_blank" class="text-[var(--c-accent)] hover:underline">
                        Buka di tab baru →
                    </a>
                </div>
            </div>

            <div class="bg-gray-50 border border-rule p-4">
                <div class="elite-kicker text-[.6rem] mb-1">URL Publik</div>
                <div class="flex items-center gap-2">
                    <code class="font-mono text-xs bg-white border border-rule px-3 py-2 flex-1 break-all">{{ route('signage.display', $school->id) }}</code>
                    <button onclick="navigator.clipboard.writeText('{{ route('signage.display', $school->id) }}')" class="text-xs text-[var(--c-accent)] hover:underline whitespace-nowrap">Salin</button>
                </div>
                <p class="text-xs text-gray-500 mt-2">URL ini bisa dibuka tanpa login. Ideal untuk Raspberry Pi atau mini PC yang terhubung ke TV lobi.</p>
            </div>

            <div class="mt-4 bg-amber-50 border border-amber-200 p-4">
                <div class="elite-kicker text-[.6rem] mb-1 text-amber-800">Tips Penggunaan</div>
                <ul class="text-xs text-amber-700 space-y-1 list-disc list-inside">
                    <li>Buka URL di browser fullscreen (F11)</li>
                    <li>Setel resolusi TV ke 1920×1080</li>
                    <li>Gunakan Raspberry Pi + Chromium auto-start</li>
                    <li>Aktifkan "Jadwal Sholat" untuk madrasah/pesantren</li>
                    <li>Konten otomatis refresh setiap 60 detik</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@extends('layouts.school-admin')
@section('title', 'Konfigurasi Dashboard TV')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="flex justify-between items-end">
        <div>
            <div class="elite-kicker mb-2">Monitor Praefectorum</div>
            <h1 class="elite-h1 text-3xl ink-primary mb-2">Dashboard TV</h1>
            <div class="elite-rule"></div>
            <p class="font-serif text-sm text-gray-600 mt-3">Tampilan statistik real-time untuk layar monitor ruang kepala sekolah. Optimasi untuk 1920×1080.</p>
        </div>
        <div>
            @php $schoolId = auth()->user()->school_id; @endphp
            <a href="{{ url("/signage/{$schoolId}/tv") }}" target="_blank" class="btn-elite">Buka Dashboard TV ↗</a>
        </div>
    </div>
</div>

<div class="max-w-2xl">
    <div class="bg-white border border-rule p-6">
        <h3 class="elite-h3 text-base ink-primary mb-4">Widget yang Ditampilkan</h3>
        <form method="POST" action="{{ route('admin.dashboard-tv.config.save') }}" class="space-y-4">
            @csrf
            @php
            $widgets = [
                ['key' => 'show_attendance', 'label' => 'Absensi Hari Ini', 'desc' => 'Jumlah & persentase kehadiran siswa'],
                ['key' => 'show_revenue', 'label' => 'Pemasukan Hari Ini', 'desc' => 'Total pembayaran SPP diterima hari ini'],
                ['key' => 'show_attendance_chart', 'label' => 'Grafik Kehadiran per Kelas', 'desc' => 'Bar chart absensi tiap rombel'],
                ['key' => 'show_activities', 'label' => 'Aktivitas Terkini', 'desc' => '10 aktivitas terbaru (absensi + pembayaran)'],
                ['key' => 'show_events', 'label' => 'Event Mendatang', 'desc' => '3 event terdekat'],
                ['key' => 'show_ticker', 'label' => 'Ticker Statistik', 'desc' => 'Total Siswa / Guru / Rombel / TA berjalan di bawah'],
            ];
            @endphp

            @foreach($widgets as $w)
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="hidden" name="{{ $w['key'] }}" value="0">
                <input type="checkbox" name="{{ $w['key'] }}" value="1" {{ ($tvConfig[$w['key']] ?? true) ? 'checked' : '' }}
                       class="mt-0.5 w-4 h-4">
                <div>
                    <div class="font-serif text-sm ink-primary">{{ $w['label'] }}</div>
                    <div class="text-xs text-gray-500">{{ $w['desc'] }}</div>
                </div>
            </label>
            @endforeach

            <hr class="border-rule">

            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Interval Refresh (detik)</label>
                <input type="number" name="refresh_interval" value="{{ $tvConfig['refresh_interval'] ?? 30 }}" min="10" max="600"
                       class="w-32 border-2 border-rule px-3 py-2 font-mono text-sm">
                <p class="text-xs text-gray-400 mt-1">Default 30 detik. Dashboard akan auto-refresh setiap interval ini.</p>
            </div>

            <button class="btn-elite">Simpan Konfigurasi</button>
        </form>
    </div>
</div>

@endsection

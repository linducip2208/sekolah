@extends('elite.layout')
@section('title', 'Pendaftaran Berhasil')
@section('description', 'Kunjungan Anda telah terdaftar')
@section('header')
<header class="bg-gradient-to-br from-indigo-800 via-indigo-900 to-stone-900 text-white py-12 px-4">
    <div class="max-w-3xl mx-auto text-center">
        <h1 class="font-display text-4xl font-bold mb-4">Pendaftaran Berhasil</h1>
    </div>
</header>
@endsection
@section('content')
<div class="max-w-lg mx-auto px-4 py-12 text-center">
    <div class="bg-white border border-rule shadow-sm p-8">
        <div class="text-6xl mb-6">✅</div>
        <h2 class="font-display text-2xl font-bold text-stone-800 mb-3">Terima Kasih, {{ $visitor->visitor_name }}!</h2>
        <p class="text-stone-600 mb-8">Kunjungan Anda telah terdaftar. Tunjukkan QR Code ini di gerbang sekolah saat check-in.</p>

        <div class="mb-6">
            <img src="{{ $qrDataUrl }}" alt="QR Code" class="mx-auto border-2 border-stone-200 rounded-xl p-2" width="250" height="250">
        </div>
        <div class="bg-stone-50 border border-stone-200 rounded-xl p-4 text-left text-sm space-y-2 mb-6">
            <div><span class="font-semibold">Nama:</span> {{ $visitor->visitor_name }}</div>
            <div><span class="font-semibold">Tujuan:</span> {{ $visitor->purpose }}</div>
            <div><span class="font-semibold">Kedatangan:</span> {{ $visitor->expected_arrival?->format('d M Y H:i') ?? '-' }}</div>
            @if($visitor->vehicle_plate)<div><span class="font-semibold">Kendaraan:</span> {{ $visitor->vehicle_plate }}</div>@endif
            <div><span class="font-semibold">Token QR:</span> <code class="text-xs bg-stone-200 px-2 py-0.5 rounded">{{ substr($visitor->qr_code, 0, 16) }}...</code></div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-700">
            <p class="font-semibold mb-1">Informasi</p>
            <p>QR Code ini berlaku 24 jam. Konfirmasi WhatsApp telah dikirim ke nomor Anda. Silakan tunjukkan QR ini kepada petugas keamanan saat tiba di gerbang.</p>
        </div>

        <a href="{{ url('/') }}" class="inline-block mt-6 text-sm text-indigo-600 hover:text-indigo-800 font-semibold">← Kembali ke Beranda</a>
    </div>
</div>
@endsection

@extends('layouts.school-admin')

@section('title', 'Kode Pemulihan 2FA')

@section('content')
<div class="max-w-2xl mx-auto p-6">
    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 mb-6">
        <h2 class="font-bold text-amber-900 mb-1">Simpan Kode Pemulihan Ini</h2>
        <p class="text-sm text-amber-800">Simpan di tempat aman. Setiap kode hanya bisa dipakai satu kali untuk login jika Anda kehilangan akses ke authenticator.</p>
    </div>

    <div class="bg-white border rounded p-6 mb-4">
        <div class="grid grid-cols-2 gap-3 font-mono text-lg">
            @foreach($codes as $code)
                <div class="bg-gray-100 px-3 py-2 rounded text-center select-all">{{ $code }}</div>
            @endforeach
        </div>
    </div>

    <div class="flex gap-2">
        <button onclick="navigator.clipboard.writeText({{ json_encode(implode("\n", $codes)) }}); this.textContent='Tersalin!'"
                class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Salin Semua</button>
        <a href="{{ route('2fa.enable') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded text-sm hover:bg-gray-300">Selesai</a>
    </div>
</div>
@endsection

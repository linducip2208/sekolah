@extends('layouts.school-admin')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-2">Two-Factor Authentication (2FA)</h1>
    <p class="text-gray-600 mb-6">Lindungi akun Anda dengan kode dari aplikasi authenticator (Google Authenticator, Authy, 1Password, dll).</p>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border-l-4 border-green-500 text-green-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 text-red-800">{{ $errors->first() }}</div>
    @endif

    @if($enabled)
        <div class="bg-white border rounded p-6 mb-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded">AKTIF</span>
                <span class="text-gray-700">2FA telah dikonfirmasi untuk akun ini.</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">Kode pemulihan tersisa: <strong>{{ $recoveryCnt }}</strong></p>

            <div class="flex gap-3">
                <form method="POST" action="{{ route('2fa.regenerate') }}">
                    @csrf
                    <button class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Generate Ulang Kode Pemulihan</button>
                </form>
            </div>

            <hr class="my-6">

            <h3 class="font-semibold mb-2">Nonaktifkan 2FA</h3>
            <form method="POST" action="{{ route('2fa.disable') }}" class="flex gap-2">
                @csrf
                <input type="text" name="code" maxlength="6" placeholder="Kode 6 digit" required
                       class="border rounded px-3 py-2 font-mono" autocomplete="off">
                <button class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Nonaktifkan</button>
            </form>
        </div>
    @else
        <div class="bg-white border rounded p-6">
            <ol class="list-decimal pl-5 space-y-3 mb-6">
                <li>Buka aplikasi authenticator Anda.</li>
                <li>Scan QR code di bawah, atau masukkan secret manual.</li>
                <li>Masukkan 6 digit kode yang muncul untuk konfirmasi.</li>
            </ol>

            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <div class="text-center">
                    <img src="{{ $qrUrl }}" alt="QR Code" class="mx-auto border" width="220" height="220">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Secret Manual</label>
                    <code class="block bg-gray-100 px-3 py-2 rounded font-mono text-sm break-all">{{ $secret }}</code>
                    <p class="text-xs text-gray-500 mt-2">Salin secret ini jika Anda tidak bisa scan QR.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('2fa.confirm') }}" class="flex gap-2">
                @csrf
                <input type="text" name="code" maxlength="6" placeholder="Kode 6 digit dari app"
                       class="flex-1 border rounded px-3 py-2 font-mono text-lg tracking-wider" autocomplete="off" autofocus required>
                <button class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-semibold">Aktifkan</button>
            </form>
        </div>
    @endif
</div>
@endsection

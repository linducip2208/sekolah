<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi 2FA — {{ config('app.name', 'eSchool') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
<div class="bg-white shadow rounded p-8 w-full max-w-md">
    <h1 class="text-2xl font-bold mb-2">Verifikasi Dua Langkah</h1>
    <p class="text-gray-600 mb-6">Masukkan 6 digit kode dari aplikasi authenticator Anda.</p>

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 text-red-800 text-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('2fa.challenge.verify') }}" class="space-y-4" x-data="{ useRecovery: false }">
        @csrf
        <div x-show="!useRecovery">
            <input type="text" name="code" maxlength="6" placeholder="000000" inputmode="numeric"
                   class="w-full border-2 rounded px-4 py-3 font-mono text-xl text-center tracking-widest"
                   autocomplete="one-time-code" autofocus>
        </div>

        <div x-show="useRecovery" x-cloak>
            <input type="text" name="recovery" placeholder="XXXXX-XXXXX"
                   class="w-full border-2 rounded px-4 py-3 font-mono uppercase" autocomplete="off">
            <p class="text-xs text-gray-500 mt-1">Kode pemulihan hanya bisa digunakan sekali.</p>
        </div>

        <button type="submit" class="w-full px-4 py-3 bg-blue-600 text-white rounded font-semibold hover:bg-blue-700">Verifikasi</button>

        <button type="button" @click="useRecovery = !useRecovery" class="w-full text-sm text-blue-600 hover:underline">
            <span x-show="!useRecovery">Gunakan kode pemulihan</span>
            <span x-show="useRecovery">Kembali ke kode authenticator</span>
        </button>
    </form>

    <hr class="my-6">
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button class="text-xs text-gray-500 hover:text-red-600">Batal & keluar</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>

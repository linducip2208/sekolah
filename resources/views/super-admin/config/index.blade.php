@extends('super-admin.layout')

@section('title', 'Konfigurasi Sistem')

@section('content')
<div class="max-w-2xl space-y-6">
    <h2 class="text-2xl font-bold text-gray-900">Konfigurasi Sistem</h2>

    <form method="POST" action="{{ route('super.config') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf @method('PUT')

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nama Aplikasi</label>
                <input type="text" name="app_name" value="{{ $config['app_name'] ?? 'Sikad Pro' }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Email Support</label>
                <input type="email" name="support_email" value="{{ $config['support_email'] ?? '' }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Trial Days</label>
                <input type="number" name="trial_days" value="{{ $config['trial_days'] ?? 14 }}" min="0"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Grace Period (hari)</label>
                <input type="number" name="grace_period_days" value="{{ $config['grace_period_days'] ?? 7 }}" min="0"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
            <div class="flex items-center gap-3">
                <input type="hidden" name="maintenance_mode" value="0">
                <input type="checkbox" name="maintenance_mode" value="1" id="maintenance"
                       {{ ($config['maintenance_mode'] ?? false) ? 'checked' : '' }}
                       class="rounded text-indigo-600">
                <label for="maintenance" class="text-sm text-gray-700">Mode Maintenance</label>
            </div>
            <div class="flex items-center gap-3">
                <input type="hidden" name="allow_school_register" value="0">
                <input type="checkbox" name="allow_school_register" value="1" id="selfReg"
                       {{ ($config['allow_school_register'] ?? true) ? 'checked' : '' }}
                       class="rounded text-indigo-600">
                <label for="selfReg" class="text-sm text-gray-700">Izinkan Registrasi Mandiri</label>
            </div>
        </div>

        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
            Simpan Konfigurasi
        </button>
    </form>
</div>
@endsection

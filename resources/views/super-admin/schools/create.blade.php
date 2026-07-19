@extends('super-admin.layout')

@section('title', 'Tambah Sekolah')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('super.schools.index') }}" class="text-indigo-600 hover:underline text-sm">← Kembali</a>
        <h2 class="text-2xl font-bold text-gray-900">Tambah Sekolah Baru</h2>
    </div>

    <form method="POST" action="{{ route('super.schools.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Sekolah *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subdomain *</label>
                <div class="flex">
                    <input type="text" name="subdomain" value="{{ old('subdomain') }}" required
                           class="flex-1 border border-gray-200 rounded-l-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    <span class="px-3 py-2 bg-gray-100 border border-l-0 border-gray-200 rounded-r-lg text-sm text-gray-400">.{{ config('multitenancy.base_domain') }}</span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Plan</label>
                <select name="plan_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    <option value="">Pilih Plan</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>
                            {{ $plan->name }} — Rp {{ number_format($plan->price/100, 0, ',', '.') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Berakhir</label>
                <input type="date" name="plan_expires_at" value="{{ old('plan_expires_at') }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Akun Admin Sekolah (Opsional)</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Admin</label>
                    <input type="text" name="admin_name" value="{{ old('admin_name') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Admin</label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Admin</label>
                    <input type="password" name="admin_password"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                Simpan Sekolah
            </button>
            <a href="{{ route('super.schools.index') }}"
               class="px-6 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-200">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection

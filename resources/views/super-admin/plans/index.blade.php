@extends('super-admin.layout')

@section('title', 'Plans')

@section('content')
<div class="space-y-6" x-data="{ showForm: false }">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Manajemen Plan</h2>
        <button @click="showForm = !showForm"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
            + Buat Plan Baru
        </button>
    </div>

    {{-- Create Form --}}
    <div x-show="showForm" x-collapse class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Plan Baru</h3>
        <form method="POST" action="{{ route('super.plans.store') }}" class="grid grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-xs text-gray-500 mb-1">Nama Plan</label>
                <input type="text" name="name" required placeholder="Pro, Basic, Enterprise..."
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Slug (unik)</label>
                <input type="text" name="slug" required placeholder="pro-plan"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Harga (cents)</label>
                <input type="number" name="price" min="0" required placeholder="9900000 = Rp99.000"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Max Siswa (0 = unlimited)</label>
                <input type="number" name="max_students" min="0" placeholder="500"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Features (JSON array, e.g. ["attendance","library"])</label>
                <input type="text" name="features_raw" placeholder='["attendance","library","fee"]'
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono">
            </div>
            <div class="col-span-2 flex gap-3">
                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm">Simpan</button>
                <button type="button" @click="showForm = false" class="px-5 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm">Batal</button>
            </div>
        </form>
    </div>

    {{-- Plans Table --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($plans as $plan)
        <div class="bg-white rounded-xl shadow-sm p-6 {{ !$plan->is_active ? 'opacity-60' : '' }}">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $plan->name }}</h3>
                    <p class="text-2xl font-bold text-indigo-600 mt-1">
                        Rp {{ number_format($plan->price/100, 0, ',', '.') }}
                        <span class="text-sm font-normal text-gray-400">/bln</span>
                    </p>
                </div>
                <span class="px-2 py-1 text-xs rounded-full {{ $plan->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $plan->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>

            <div class="mt-4 space-y-1 text-sm text-gray-600">
                <p>👥 Max Siswa: {{ $plan->max_students ?: '∞' }}</p>
                <p>👩‍🏫 Max Guru: {{ $plan->max_teachers ?: '∞' }}</p>
                <p>🏫 Sekolah: {{ $plan->schools_count }}</p>
            </div>

            <div class="mt-3 flex flex-wrap gap-1">
                @foreach($plan->features ?? [] as $feature)
                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 text-xs rounded">{{ $feature }}</span>
                @endforeach
            </div>

            <div class="mt-4 pt-4 border-t border-gray-100">
                <form method="POST" action="{{ route('super.plans.update', $plan) }}" class="flex gap-2">
                    @csrf @method('PUT')
                    <input type="hidden" name="is_active" value="{{ $plan->is_active ? 0 : 1 }}">
                    <button type="submit" class="text-xs text-gray-500 hover:underline">
                        {{ $plan->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

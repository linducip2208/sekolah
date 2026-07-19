@extends('super-admin.layout')

@section('title', $school->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('super.schools.index') }}" class="text-indigo-600 hover:underline text-sm">← Kembali</a>
            <h2 class="text-2xl font-bold text-gray-900">{{ $school->name }}</h2>
            <span class="px-2 py-1 rounded-full text-xs font-medium
                {{ $school->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ $school->is_active ? 'Aktif' : 'Suspended' }}
            </span>
        </div>
        <div class="flex gap-2">
            @if($school->is_active)
            <form method="POST" action="{{ route('super.schools.suspend', $school) }}"
                  onsubmit="return confirm('Suspend sekolah ini? Semua user tidak bisa login.')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm">Suspend</button>
            </form>
            @else
            <form method="POST" action="{{ route('super.schools.activate', $school) }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm">Aktifkan</button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Info --}}
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-3">
            <h3 class="text-sm font-semibold text-gray-700">Informasi Sekolah</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Subdomain</dt><dd>{{ $school->subdomain }}.{{ config('multitenancy.base_domain') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd>{{ $school->email ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd>{{ $school->phone ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Plan</dt><dd class="font-medium">{{ $school->plan?->name ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Berakhir</dt>
                    <dd class="{{ $school->plan_expires_at && $school->plan_expires_at->isPast() ? 'text-red-600 font-semibold' : '' }}">
                        {{ $school->plan_expires_at?->format('d M Y') ?? '-' }}
                    </dd>
                </div>
                <div class="flex justify-between"><dt class="text-gray-500">Terdaftar</dt><dd>{{ $school->created_at->format('d M Y') }}</dd></div>
            </dl>
        </div>

        {{-- Stats --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Statistik</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center p-3 bg-indigo-50 rounded-lg">
                    <span class="text-sm text-gray-600">Siswa</span>
                    <span class="text-xl font-bold text-indigo-600">{{ number_format($stats['students']) }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                    <span class="text-sm text-gray-600">Guru</span>
                    <span class="text-xl font-bold text-green-600">{{ number_format($stats['teachers']) }}</span>
                </div>
            </div>
        </div>

        {{-- Extend Subscription --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Perpanjang Langganan</h3>
            <form method="POST" action="{{ route('super.schools.extend', $school) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Plan</label>
                    <select name="plan_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" @selected($school->plan_id == $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Berakhir</label>
                    <input type="date" name="expires_at" required
                           value="{{ $school->plan_expires_at?->format('Y-m-d') ?? now()->addYear()->format('Y-m-d') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                </div>
                <button type="submit" class="w-full py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                    Perpanjang
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

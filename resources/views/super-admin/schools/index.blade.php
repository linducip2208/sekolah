@extends('super-admin.layout')

@section('title', 'Manajemen Sekolah')

@section('content')
<div class="space-y-6" x-data="schoolManager()">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Daftar Sekolah</h2>
        <a href="{{ route('super.schools.create') }}"
           class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
            + Tambah Sekolah
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex gap-3 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama sekolah..."
                   class="flex-1 min-w-48 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                <option value="active" @selected(request('status') === 'active')>Aktif</option>
                <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm">Filter</button>
            <a href="{{ route('super.schools.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-6 py-3">Sekolah</th>
                    <th class="px-6 py-3">Plan</th>
                    <th class="px-6 py-3">Berakhir</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($schools as $school)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3">
                        <p class="font-medium text-gray-900">{{ $school->name }}</p>
                        <p class="text-xs text-gray-400">{{ $school->subdomain }}.{{ config('multitenancy.base_domain') }}</p>
                    </td>
                    <td class="px-6 py-3 text-gray-600">{{ $school->plan?->name ?? '-' }}</td>
                    <td class="px-6 py-3 text-gray-600">
                        {{ $school->plan_expires_at?->format('d M Y') ?? '-' }}
                    </td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            {{ $school->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $school->is_active ? 'Aktif' : 'Suspended' }}
                        </span>
                    </td>
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('super.schools.show', $school) }}"
                               class="text-indigo-600 hover:underline">Detail</a>

                            @if($school->is_active)
                            <form method="POST" action="{{ route('super.schools.suspend', $school) }}"
                                  onsubmit="return confirm('Suspend sekolah ini?')">
                                @csrf @method('POST')
                                <button type="submit" class="text-red-500 hover:underline">Suspend</button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('super.schools.activate', $school) }}">
                                @csrf @method('POST')
                                <button type="submit" class="text-green-600 hover:underline">Aktifkan</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">Tidak ada data sekolah.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $schools->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection

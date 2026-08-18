@extends('super-admin.layout')

@section('title', 'Tenant Usage Analytics')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Tenant Usage Analytics</h2>
        <p class="text-sm text-gray-500 mt-0.5">Monitoring penggunaan resource per sekolah</p>
    </div>

    {{-- Month Selector --}}
    <form method="GET" class="flex flex-wrap gap-2 items-end">
        <div>
            <label class="text-xs font-medium text-gray-500">Bulan</label>
            <select name="month" class="mt-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                @foreach($months as $m)
                    <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>{{ \Carbon\Carbon::parse($m . '-01')->translatedFormat('F Y') }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari sekolah..."
                   class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 w-64">
        </div>
        <button type="submit" class="btn-elite">Filter</button>
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-indigo-500">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Sekolah</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary->school_count ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-emerald-500">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Siswa Aktif</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary->total_students ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-amber-500">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Guru Aktif</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary->total_teachers ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-purple-500">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Login</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary->total_logins ?? 0) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-cyan-500">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">API Calls</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary->total_api_calls ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-rose-500">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">SMS Terkirim</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary->total_sms ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-teal-500">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Email Terkirim</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary->total_emails ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-orange-500">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Storage Total</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format(($summary->total_storage ?? 0) / (1024*1024), 1) }} MB</p>
        </div>
    </div>

    {{-- Detail Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Sekolah</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-600">Siswa</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-600">Guru</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-600">Login</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-600">API Calls</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-600">Storage</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-600">SMS</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-600">Email</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($usage as $row)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-3 font-medium text-gray-900">{{ $row->school?->name ?? '-' }}</td>
                        <td class="px-5 py-3 text-right">{{ number_format($row->active_students) }}</td>
                        <td class="px-5 py-3 text-right">{{ number_format($row->active_teachers) }}</td>
                        <td class="px-5 py-3 text-right">{{ number_format($row->total_logins) }}</td>
                        <td class="px-5 py-3 text-right">{{ number_format($row->api_calls) }}</td>
                        <td class="px-5 py-3 text-right">{{ number_format($row->storage_used_mb, 1) }} MB</td>
                        <td class="px-5 py-3 text-right">{{ number_format($row->sms_sent) }}</td>
                        <td class="px-5 py-3 text-right">{{ number_format($row->emails_sent) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-gray-400">Belum ada data usage untuk bulan ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $usage->links() }}
        </div>
    </div>
</div>
@endsection

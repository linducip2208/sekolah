@extends('layouts.school-admin')
@section('title', 'Donasi')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')
<div class="space-y-6">
    <div class="flex justify-between">
        <h2 class="text-xl font-bold">Donasi & Fundraising</h2>
        <button class="btn-brand">+ Tambah Kampanye</button>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-lg p-5 shadow"><div class="text-xs text-gray-500">Total Terkumpul</div><div class="text-3xl font-bold text-green-600">Rp {{ number_format($totalRaised / 100, 0, ',', '.') }}</div></div>
        <div class="bg-white rounded-lg p-5 shadow"><div class="text-xs text-gray-500">Total Donatur</div><div class="text-3xl font-bold">{{ $donorCount }}</div></div>
        <div class="bg-white rounded-lg p-5 shadow"><div class="text-xs text-gray-500">Kampanye Aktif</div><div class="text-3xl font-bold">{{ $campaigns->where('status', 'active')->count() }}</div></div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b font-bold">Kampanye</div>
        <div class="divide-y">
            @forelse($campaigns as $c)
                <div class="p-4">
                    <div class="flex justify-between mb-2">
                        <h3 class="font-bold">{{ $c->title }}</h3>
                        <span class="px-2 py-0.5 bg-{{ $c->status === 'active' ? 'green' : 'gray' }}-100 rounded text-xs">{{ $c->status }}</span>
                    </div>
                    <div class="bg-gray-200 rounded h-2 overflow-hidden">
                        <div class="bg-green-500 h-2" style="width: {{ min(100, $c->progressPercent()) }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>Rp {{ number_format($c->raised_amount / 100, 0, ',', '.') }} terkumpul</span>
                        <span>{{ $c->progressPercent() }}% dari Rp {{ number_format($c->target_amount / 100, 0, ',', '.') }}</span>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">Belum ada kampanye</div>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b font-bold">Donasi Terbaru</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600"><tr><th class="px-4 py-2">Donor</th><th class="px-4 py-2">Jumlah</th><th class="px-4 py-2">Tanggal</th></tr></thead>
            <tbody class="divide-y">
                @forelse($recentDonations as $d)
                    <tr>
                        <td class="px-4 py-2">{{ $d->is_anonymous ? '🕶️ Anonim' : $d->donor_name }}</td>
                        <td class="px-4 py-2 font-bold text-green-600">Rp {{ number_format($d->amount / 100, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-xs">{{ $d->donated_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">Belum ada donasi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

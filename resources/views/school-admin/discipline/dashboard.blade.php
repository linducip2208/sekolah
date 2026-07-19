@extends('layouts.school-admin')
@section('title', 'Tata Tertib')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')
<div class="space-y-6">
    <h2 class="text-xl font-bold">Tata Tertib & Poin Pelanggaran</h2>

    <div class="grid grid-cols-3 gap-4">
        <div class="col-span-2 bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b font-bold">Pelanggaran/Prestasi Terbaru</div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-gray-600">
                    <tr><th class="px-4 py-2">Tanggal</th><th class="px-4 py-2">Siswa</th><th class="px-4 py-2">Poin</th><th class="px-4 py-2">Status</th></tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($recentRecords as $r)
                        <tr>
                            <td class="px-4 py-2 text-xs">{{ $r->incident_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">Student #{{ $r->student_id }}</td>
                            <td class="px-4 py-2 font-bold {{ $r->points >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $r->points >= 0 ? '+' : '' }}{{ $r->points }}</td>
                            <td class="px-4 py-2 text-xs">{{ $r->status }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Belum ada record</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b font-bold">🏆 Leaderboard Top 10</div>
            <div class="divide-y">
                @forelse($leaderboard as $i => $row)
                    <div class="p-3 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-gray-500">#{{ $i + 1 }}</span>
                            <span class="font-medium ml-1">Student #{{ $row->student_id }}</span>
                        </div>
                        <span class="font-bold {{ $row->total_points >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $row->total_points }} pt</span>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 text-sm">Tidak ada data</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b flex justify-between">
            <h3 class="font-bold">Kategori Pelanggaran/Prestasi ({{ $categories->count() }})</h3>
            <button class="btn-brand text-xs">+ Tambah Kategori</button>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 p-4">
            @foreach($categories as $c)
                <div class="border rounded p-3">
                    <div class="flex justify-between">
                        <div class="font-medium">{{ $c->name }}</div>
                        <div class="font-bold {{ $c->point_value >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $c->point_value >= 0 ? '+' : '' }}{{ $c->point_value }}</div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">{{ ucfirst($c->type) }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

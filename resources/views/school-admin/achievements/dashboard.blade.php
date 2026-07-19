@extends('layouts.school-admin')
@section('title', 'Prestasi Siswa')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')
<div class="space-y-6">
    <h2 class="text-xl font-bold">Prestasi Siswa</h2>

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-lg p-5 shadow"><div class="text-xs text-gray-500">Total Tahun Ini</div><div class="text-3xl font-bold">{{ $totalThisYear }}</div></div>
        <div class="bg-white rounded-lg p-5 shadow"><div class="text-xs text-gray-500">Belum Diverifikasi</div><div class="text-3xl font-bold text-orange-600">{{ $unverified }}</div></div>
        <div class="bg-white rounded-lg p-5 shadow"><div class="text-xs text-gray-500">Recent (50)</div><div class="text-3xl font-bold">{{ $recent->count() }}</div></div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b font-bold">Prestasi Terbaru</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600"><tr><th class="px-4 py-2">Tanggal</th><th class="px-4 py-2">Siswa</th><th class="px-4 py-2">Prestasi</th><th class="px-4 py-2">Issuer</th><th class="px-4 py-2">Status</th></tr></thead>
            <tbody class="divide-y">
                @forelse($recent as $a)
                    <tr>
                        <td class="px-4 py-2 text-xs">{{ $a->achieved_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">Student #{{ $a->student_id }}</td>
                        <td class="px-4 py-2 font-medium">{{ $a->title }}</td>
                        <td class="px-4 py-2 text-xs">{{ $a->issuer ?? '—' }}</td>
                        <td class="px-4 py-2">
                            @if($a->verified)<span class="px-2 py-0.5 bg-green-100 text-green-800 rounded text-xs">✓ Verified</span>
                            @else<span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded text-xs">Pending</span>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada prestasi tercatat</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

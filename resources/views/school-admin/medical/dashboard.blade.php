@extends('layouts.school-admin')
@section('title', 'UKS / Klinik')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')
<div class="space-y-6">
    <h2 class="text-xl font-bold">UKS / Klinik Sekolah</h2>

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-lg p-5 shadow">
            <div class="text-xs text-gray-500">Kunjungan Hari Ini</div>
            <div class="text-3xl font-bold">{{ $todayCount }}</div>
        </div>
        <div class="bg-white rounded-lg p-5 shadow">
            <div class="text-xs text-gray-500">Dipulangkan</div>
            <div class="text-3xl font-bold text-orange-600">{{ $sentHomeCount }}</div>
        </div>
        <div class="bg-white rounded-lg p-5 shadow">
            <div class="text-xs text-gray-500">Vaksinasi Bulan Ini</div>
            <div class="text-3xl font-bold text-green-600">{{ $recentVaccinations->where('vaccinated_at', '>=', now()->startOfMonth())->count() }}</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b flex justify-between">
            <h3 class="font-bold">Kunjungan Klinik</h3>
            <button class="btn-brand text-xs">+ Catat Kunjungan</button>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-2">Waktu</th>
                    <th class="px-4 py-2">Siswa</th>
                    <th class="px-4 py-2">Keluhan</th>
                    <th class="px-4 py-2">Diagnosis</th>
                    <th class="px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($recentVisits as $v)
                    <tr>
                        <td class="px-4 py-2 text-xs">{{ $v->visit_at->format('d/m H:i') }}</td>
                        <td class="px-4 py-2">Student #{{ $v->student_id }}</td>
                        <td class="px-4 py-2 text-xs max-w-xs truncate">{{ $v->symptoms }}</td>
                        <td class="px-4 py-2 text-xs">{{ $v->diagnosis ?? '—' }}</td>
                        <td class="px-4 py-2 text-xs">
                            @if($v->sent_home)<span class="px-2 py-0.5 bg-orange-100 text-orange-800 rounded">Pulang</span>@endif
                            @if($v->referred_external)<span class="px-2 py-0.5 bg-red-100 text-red-800 rounded">Rujukan</span>@endif
                            @if($v->parent_notified)<span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded">Notif</span>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

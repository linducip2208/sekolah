@extends('layouts.school-admin')
@section('title', 'Tamu Sekolah')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')
<div class="space-y-6">
    <div class="flex justify-between">
        <h2 class="text-xl font-bold">Manajemen Tamu</h2>
        <button class="btn-brand">+ Catat Tamu Baru</button>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-lg p-5 shadow"><div class="text-xs text-gray-500">Tamu Hari Ini</div><div class="text-3xl font-bold">{{ $todayVisitors->count() }}</div></div>
        <div class="bg-white rounded-lg p-5 shadow"><div class="text-xs text-gray-500">Sedang di Sekolah</div><div class="text-3xl font-bold text-blue-600">{{ $currentlyInside }}</div></div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b font-bold">Tamu Hari Ini</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600"><tr><th class="px-4 py-2">Waktu</th><th class="px-4 py-2">Nama</th><th class="px-4 py-2">Tujuan</th><th class="px-4 py-2">Host</th><th class="px-4 py-2">Status</th></tr></thead>
            <tbody class="divide-y">
                @forelse($todayVisitors as $v)
                    <tr class="{{ $v->is_blacklisted ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-2 text-xs">{{ $v->checked_in_at->format('H:i') }}</td>
                        <td class="px-4 py-2">
                            {{ $v->visitor_name }}
                            @if($v->is_blacklisted)<span class="ml-2 text-red-600">⚠️ Blacklist</span>@endif
                        </td>
                        <td class="px-4 py-2 text-xs">{{ $v->purpose }}</td>
                        <td class="px-4 py-2 text-xs">User #{{ $v->host_user_id }}</td>
                        <td class="px-4 py-2">
                            @if($v->checked_out_at)<span class="px-2 py-0.5 bg-gray-100 rounded text-xs">Selesai</span>
                            @else<span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-xs">Di sekolah</span>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada tamu hari ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

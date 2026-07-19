@extends('layouts.school-admin')
@section('title', 'Beasiswa')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')
<div class="space-y-6">
    <div class="flex justify-between">
        <h2 class="text-xl font-bold">Manajemen Beasiswa</h2>
        <button class="btn-brand">+ Tambah Program</button>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-lg p-5 shadow"><div class="text-xs text-gray-500">Program Aktif</div><div class="text-3xl font-bold">{{ $programs->count() }}</div></div>
        <div class="bg-white rounded-lg p-5 shadow"><div class="text-xs text-gray-500">Aplikasi Pending Review</div><div class="text-3xl font-bold text-orange-600">{{ $pendingReview }}</div></div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b font-bold">Program Beasiswa</div>
        <div class="divide-y">
            @forelse($programs as $p)
                <div class="p-4">
                    <div class="flex justify-between">
                        <h3 class="font-bold">{{ $p->name }}</h3>
                        <span class="px-2 py-0.5 bg-blue-50 text-blue-800 rounded text-xs">{{ $p->source }}</span>
                    </div>
                    <div class="text-sm text-gray-600 mt-1">
                        Diskon:
                        @if($p->discount_type === 'full')Penuh
                        @elseif($p->discount_type === 'percentage'){{ $p->discount_value }}%
                        @else Rp {{ number_format($p->discount_value / 100, 0, ',', '.') }}@endif
                        @if($p->quota) · Kuota: {{ $p->quota }} @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">Belum ada program beasiswa</div>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b font-bold">Aplikasi Terbaru</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600"><tr><th class="px-4 py-2">Tanggal</th><th class="px-4 py-2">Siswa</th><th class="px-4 py-2">Program</th><th class="px-4 py-2">Status</th></tr></thead>
            <tbody class="divide-y">
                @forelse($applications as $a)
                    <tr>
                        <td class="px-4 py-2 text-xs">{{ $a->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">Student #{{ $a->student_id }}</td>
                        <td class="px-4 py-2 text-xs">Program #{{ $a->scholarship_program_id }}</td>
                        <td class="px-4 py-2"><span class="px-2 py-0.5 bg-gray-100 rounded text-xs">{{ $a->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Belum ada aplikasi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

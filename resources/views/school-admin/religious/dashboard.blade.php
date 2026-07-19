@extends('layouts.school-admin')
@section('title', 'Religious / Pesantren')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')
<div class="space-y-6">
    <h2 class="text-xl font-bold">Religious / Pesantren Mode</h2>

    <div class="bg-white rounded-lg shadow p-5">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-bold">Status Mode</h3>
                <p class="text-sm text-gray-600">Toggle untuk aktifkan fitur tahfidz, ibadah harian, kitab kuning</p>
            </div>
            @if($config->enabled)
                <span class="px-3 py-1 bg-green-100 text-green-800 rounded font-medium">✓ AKTIF — {{ ucfirst($config->religion) }}</span>
            @else
                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded">Belum aktif</span>
            @endif
        </div>
    </div>

    @if($config->enabled)
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white rounded-lg shadow">
                <div class="px-4 py-3 border-b font-bold">📖 Setoran Hafalan Terbaru</div>
                <div class="divide-y max-h-96 overflow-y-auto">
                    @forelse($recentHafalan as $h)
                        <div class="p-3">
                            <div class="font-medium">{{ $h->surah }} : {{ $h->ayah_start }}-{{ $h->ayah_end }}</div>
                            <div class="text-xs text-gray-500">Student #{{ $h->student_id }} · {{ $h->memorized_at->format('d/m/Y') }} · {{ $h->quality }}</div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">Belum ada setoran</div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-5">
                <h3 class="font-bold mb-3">🤲 Mutaba'ah Ibadah Hari Ini</h3>
                <div class="text-3xl font-bold">{{ $todayIbadah }}</div>
                <p class="text-sm text-gray-600">Siswa sudah log ibadah</p>
            </div>
        </div>
    @endif
</div>
@endsection

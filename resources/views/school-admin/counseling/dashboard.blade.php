@extends('layouts.school-admin')
@section('title', 'BP/BK')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')
<div class="space-y-6">
    <h2 class="text-xl font-bold">BP/BK — Bimbingan Konseling</h2>

    @if($flaggedWellness->isNotEmpty())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="font-bold text-red-800">⚠️ {{ $flaggedWellness->count() }} siswa flagged dari wellness check-in (mood ≤ 3)</div>
            <p class="text-sm text-red-700 mt-1">Periksa segera, mungkin perlu sesi konseling darurat.</p>
        </div>
    @endif

    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b font-bold">📅 Sesi Konseling Mendatang ({{ $upcomingSessions->count() }})</div>
            <div class="divide-y max-h-96 overflow-y-auto">
                @forelse($upcomingSessions as $s)
                    <div class="p-3">
                        <div class="text-sm font-medium">{{ $s->scheduled_at->format('d M Y H:i') }}</div>
                        <div class="text-xs text-gray-600">Student #{{ $s->student_id }} · {{ ucfirst($s->type) }}</div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 text-sm">Tidak ada sesi mendatang</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b font-bold">🚨 Laporan Bullying Aktif ({{ $openBullyingReports->count() }})</div>
            <div class="divide-y max-h-96 overflow-y-auto">
                @forelse($openBullyingReports as $r)
                    <div class="p-3">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium">{{ ucfirst($r->type) }}</span>
                            <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded">{{ $r->status }}</span>
                        </div>
                        @if($r->is_anonymous)
                            <div class="text-xs text-gray-500 mt-1">🕶️ Anonymous report</div>
                        @endif
                        <div class="text-xs text-gray-700 mt-1 line-clamp-2">{{ \Illuminate\Support\Str::limit($r->description, 100) }}</div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 text-sm">Tidak ada laporan aktif</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

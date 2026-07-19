@extends('layouts.parent')
@section('title', 'Absensi QR')
@section('content')

@include('student-portal._nav')

<div class="mt-8 mb-8">
    <div class="elite-kicker mb-2">Praesentia</div>
    <h1 class="elite-h1 text-4xl ink-primary mb-2">Absensi QR</h1>
    <div class="elite-rule mb-3"></div>
    <p class="font-serif text-lg" style="color: var(--c-muted);">Riwayat absensi Anda melalui QR Code.</p>
</div>

@php
    $student = \App\Models\Academic\Student::where('user_id', auth()->id())->first();
    $records = \App\Models\Academic\QrAttendanceRecord::where('student_id', $student->id)
        ->with(['session.classSection.classRoom', 'session.classSection.section', 'session.subject', 'session.teacher:id,name'])
        ->orderByDesc('scanned_at')
        ->paginate(20);
@endphp

@if($records->isEmpty())
    <div class="bg-white border border-rule p-10 text-center">
        <p class="font-serif text-base text-gray-600 italic mb-2">Belum ada riwayat absensi QR.</p>
        <p class="font-serif text-sm text-gray-500">Scan QR yang ditampilkan guru untuk mencatat kehadiran.</p>
    </div>
@else
    <div class="grid md:grid-cols-3 gap-4 mb-6">
        @php
            $todayRecords = $records->filter(fn($r) => $r->session->session_date->isToday());
            $totalScans = $records->total();
            $lateCount = $records->where('status', 'late')->count();
        @endphp
        <div class="elite-card p-5 text-center">
            <div class="text-3xl font-display font-bold text-green-700">{{ $todayRecords->count() }}</div>
            <div class="elite-kicker text-[.6rem]">Scan Hari Ini</div>
        </div>
        <div class="elite-card p-5 text-center">
            <div class="text-3xl font-display font-bold ink-primary">{{ $totalScans }}</div>
            <div class="elite-kicker text-[.6rem]">Total Scan</div>
        </div>
        <div class="elite-card p-5 text-center">
            <div class="text-3xl font-display font-bold {{ $lateCount > 0 ? 'text-red-700' : 'text-green-700' }}">{{ $lateCount }}</div>
            <div class="elite-kicker text-[.6rem]">Terlambat</div>
        </div>
    </div>

    <div class="bg-white border border-rule">
        <div class="table-elite overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-rule text-left">
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4">Mapel</th>
                        <th class="py-3 px-4">Guru</th>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $r)
                        <tr class="border-b border-rule/40">
                            <td class="py-3 px-4">{{ $r->session->session_date->translatedFormat('d M Y') }}</td>
                            <td class="py-3 px-4">{{ $r->session->classSection?->classRoom?->name }} {{ $r->session->classSection?->section?->name }}</td>
                            <td class="py-3 px-4">{{ $r->session->subject?->name ?? '—' }}</td>
                            <td class="py-3 px-4">{{ $r->session->teacher?->name }}</td>
                            <td class="py-3 px-4 text-xs">{{ $r->scanned_at->format('H:i:s') }}</td>
                            <td class="py-3 px-4">
                                @if($r->status === 'late')
                                    <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5">Terlambat {{ $r->late_minutes }}m</span>
                                @else
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-0.5">Hadir</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $records->links() }}</div>
@endif

@endsection

@extends('layouts.school-admin')
@section('title', 'Detail QR Absensi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.qr-attendance.history') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Riwayat</a>

<div class="mb-7">
    <div class="elite-kicker mb-2">Detail Sesi</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">QR Absensi — {{ $session->session_date->translatedFormat('d M Y') }}</h1>
    <div class="elite-rule mb-3"></div>
    <div class="text-sm text-gray-600 flex flex-wrap gap-x-4 gap-y-1">
        <span>{{ $session->classSection?->classRoom?->name }} {{ $session->classSection?->section?->name }}</span>
        <span>Mapel: {{ $session->subject?->name ?? 'Semua' }}</span>
        <span>Guru: {{ $session->teacher?->name }}</span>
        <span>Kode: <span class="font-mono text-xs">{{ $session->qr_code }}</span></span>
    </div>
</div>

<div class="grid md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-rule p-4 text-center">
        <div class="text-3xl font-display font-bold ink-primary">{{ $allStudents->count() }}</div>
        <div class="elite-kicker text-[.6rem]">Total Siswa</div>
    </div>
    <div class="bg-white border border-rule p-4 text-center">
        <div class="text-3xl font-display font-bold text-green-700">{{ count($scannedIds) }}</div>
        <div class="elite-kicker text-[.6rem]">Hadir</div>
    </div>
    <div class="bg-white border border-rule p-4 text-center">
        <div class="text-3xl font-display font-bold text-red-700">{{ $allStudents->count() - count($scannedIds) }}</div>
        <div class="elite-kicker text-[.6rem]">Tidak Hadir</div>
    </div>
    <div class="bg-white border border-rule p-4 text-center">
        <div class="text-3xl font-display font-bold ink-primary">{{ $records->where('status', 'late')->count() }}</div>
        <div class="elite-kicker text-[.6rem]">Terlambat</div>
    </div>
</div>

<div class="table-elite overflow-x-auto bg-white border border-rule">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-rule text-left">
                <th class="py-3 px-4">NIS</th>
                <th class="py-3 px-4">Nama</th>
                <th class="py-3 px-4">Status Scan</th>
                <th class="py-3 px-4">Jam Scan</th>
                <th class="py-3 px-4">IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allStudents as $student)
                @php
                    $record = $records->firstWhere('student_id', $student->id);
                @endphp
                <tr class="border-b border-rule/40 {{ $record ? 'bg-green-50' : '' }}">
                    <td class="py-3 px-4 font-mono text-xs">{{ $student->admission_no }}</td>
                    <td class="py-3 px-4">{{ $student->user->name }}</td>
                    <td class="py-3 px-4">
                        @if($record)
                            @if($record->status === 'late')
                                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5">Terlambat ({{ $record->late_minutes }}m)</span>
                            @else
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-0.5">Hadir</span>
                            @endif
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-xs text-gray-500">{{ $record?->scanned_at?->format('H:i:s') ?? '—' }}</td>
                    <td class="py-3 px-4 text-xs text-gray-400 font-mono">{{ $record?->ip_address ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection

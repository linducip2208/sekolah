@extends('layouts.school-admin')
@section('title', 'Riwayat QR Absensi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Historia QR</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Riwayat QR Absensi</h1>
    <div class="elite-rule"></div>
</div>

<a href="{{ route('admin.qr-attendance.show') }}" class="btn-elite-gold mb-5 inline-block">+ Buat Sesi Baru</a>

<div class="table-elite overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-rule text-left">
                <th class="py-3 px-4">Tanggal</th>
                <th class="py-3 px-4">Kelas</th>
                <th class="py-3 px-4">Mapel</th>
                <th class="py-3 px-4">Guru</th>
                <th class="py-3 px-4">Hadir</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sessions as $s)
                <tr class="border-b border-rule/40">
                    <td class="py-3 px-4">{{ $s->session_date->translatedFormat('d M Y') }}</td>
                    <td class="py-3 px-4">{{ $s->classSection?->classRoom?->name }} {{ $s->classSection?->section?->name }}</td>
                    <td class="py-3 px-4">{{ $s->subject?->name ?? '—' }}</td>
                    <td class="py-3 px-4">{{ $s->teacher?->name }}</td>
                    <td class="py-3 px-4 font-semibold">{{ $s->records_count }} siswa</td>
                    <td class="py-3 px-4">
                        @if($s->is_active && $s->qr_expires_at->isFuture())
                            <span class="text-xs text-green-700 font-semibold">Aktif</span>
                        @else
                            <span class="text-xs text-gray-500">Selesai</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        <a href="{{ route('admin.qr-attendance.session', $s) }}" class="text-xs underline ink-secondary hover:ink-accent">Detail</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-10 text-center text-gray-500 italic font-serif">Belum ada sesi QR absensi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-5">{{ $sessions->links() }}</div>

@endsection

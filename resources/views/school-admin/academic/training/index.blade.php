@extends('layouts.school-admin')
@section('title', 'Diklat & Pelatihan Guru')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="flex justify-between items-end">
        <div>
            <div class="elite-kicker mb-2">Professio Perennis</div>
            <h1 class="elite-h1 text-3xl ink-primary mb-2">Diklat & Pelatihan</h1>
            <div class="elite-rule"></div>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.training.certifications') }}" class="btn-elite-ghost">Sertifikasi Guru</a>
            <a href="{{ route('admin.training.create') }}" class="btn-elite">+ Pelatihan Baru</a>
        </div>
    </div>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="elite-card p-4 text-center">
        <div class="font-display text-3xl ink-accent">{{ $stats['totalTrainings'] }}</div>
        <div class="elite-kicker text-[.6rem] mt-1">Total Pelatihan</div>
    </div>
    <div class="elite-card p-4 text-center">
        <div class="font-display text-3xl ink-accent">{{ $stats['totalParticipants'] }}</div>
        <div class="elite-kicker text-[.6rem] mt-1">Total Peserta</div>
    </div>
    <div class="elite-card p-4 text-center">
        <div class="font-display text-3xl ink-accent">{{ $stats['totalCompleted'] }}</div>
        <div class="elite-kicker text-[.6rem] mt-1">Terselesaikan</div>
    </div>
    <div class="elite-card p-4 text-center">
        <div class="font-display text-3xl ink-accent">{{ $stats['totalHours'] }}</div>
        <div class="elite-kicker text-[.6rem] mt-1">Total Jam</div>
    </div>
    <div class="elite-card p-4 text-center">
        <div class="font-display text-3xl ink-accent">{{ $stats['completionRate'] }}%</div>
        <div class="elite-kicker text-[.6rem] mt-1">Tingkat Kelulusan</div>
    </div>
</div>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Pelatihan</th>
                <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Jenis</th>
                <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tanggal</th>
                <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Peserta</th>
                <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Status</th>
                <th class="px-3 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($trainings as $t)
                <tr class="border-t border-rule hover:bg-gray-50">
                    <td class="px-3 py-3">
                        <div class="font-serif font-semibold">{{ $t->title }}</div>
                        <div class="text-xs text-gray-500">{{ $t->provider ?? 'Mandiri' }} · {{ $t->duration_hours }} jam</div>
                    </td>
                    <td class="px-3 py-3">
                        <span class="px-2 py-0.5 text-xs font-medium rounded
                            {{ $t->training_type === 'sertifikasi' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $t->training_type === 'workshop' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $t->training_type === 'seminar' ? 'bg-purple-100 text-purple-700' : '' }}
                            {{ $t->training_type === 'diklat' ? 'bg-orange-100 text-orange-700' : '' }}
                            {{ $t->training_type === 'online' ? 'bg-teal-100 text-teal-700' : '' }}">
                            {{ ucfirst($t->training_type) }}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-xs">
                        {{ $t->start_date->format('d M Y') }}
                        @if($t->end_date) — {{ $t->end_date->format('d M Y') }} @endif
                    </td>
                    <td class="px-3 py-3 text-center font-mono text-xs">{{ $t->participants_count }}</td>
                    <td class="px-3 py-3 text-center">
                        @if($t->is_mandatory)
                            <span class="text-xs text-red-600 font-semibold">Wajib</span>
                        @else
                            <span class="text-xs text-gray-400">Opsional</span>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-right">
                        <a href="{{ route('admin.training.participants', $t) }}" class="text-xs ink-accent hover:underline mr-3">Peserta</a>
                        <a href="{{ route('admin.training.edit', $t) }}" class="text-xs text-gray-600 hover:underline mr-3">Edit</a>
                        <form method="POST" action="{{ route('admin.training.destroy', $t) }}" class="inline" onsubmit="return confirm('Hapus?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-700 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada pelatihan. Buat pelatihan pertama.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $trainings->links() }}</div>

@endsection

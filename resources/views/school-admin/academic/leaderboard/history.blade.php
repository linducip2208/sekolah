@extends('layouts.school-admin')
@section('title', 'Riwayat Poin')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
<a href="{{ route('admin.leaderboard.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kembali ke Leaderboard</a>

<div class="mb-7">
    <div class="elite-kicker mb-2">Punctum Historiae</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Riwayat Poin Siswa</h1>
    <div class="elite-rule"></div>
</div>

<form method="GET" class="flex flex-wrap gap-3 mb-6 bg-white border border-rule p-4">
    <select name="student_id" class="border-2 border-rule px-3 py-2 text-sm">
        <option value="">— Semua Siswa —</option>
        @foreach($students as $s)
        <option value="{{ $s->id }}" {{ (string)$studentId === (string)$s->id ? 'selected' : '' }}>{{ $s->user?->name }}</option>
        @endforeach
    </select>
    <select name="point_type" class="border-2 border-rule px-3 py-2 text-sm">
        <option value="">— Semua Tipe —</option>
        <option value="academic" {{ $pointType === 'academic' ? 'selected' : '' }}>Akademik</option>
        <option value="attendance" {{ $pointType === 'attendance' ? 'selected' : '' }}>Absensi</option>
        <option value="extracurricular" {{ $pointType === 'extracurricular' ? 'selected' : '' }}>Ekskul</option>
        <option value="discipline" {{ $pointType === 'discipline' ? 'selected' : '' }}>Disiplin</option>
        <option value="other" {{ $pointType === 'other' ? 'selected' : '' }}>Lainnya</option>
    </select>
    <select name="period" class="border-2 border-rule px-3 py-2 text-sm">
        <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Bulan Ini</option>
        <option value="weekly" {{ $period === 'weekly' ? 'selected' : '' }}>Minggu Ini</option>
        <option value="semester" {{ $period === 'semester' ? 'selected' : '' }}>Semester Ini</option>
    </select>
    <button type="submit" class="btn-elite-ghost text-xs">Filter</button>
    <a href="{{ route('admin.leaderboard.history') }}" class="text-xs text-gray-500 hover:ink-accent self-center">Reset</a>
</form>

<div class="elite-card overflow-hidden">
    <div class="table-scroll">
        <table class="w-full text-sm">
            <thead class="bg-[var(--c-primary)] text-white">
                <tr>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tanggal</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tipe</th>
                    <th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Poin</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Alasan</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Diberikan Oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $point)
                <tr class="border-t border-rule">
                    <td class="px-3 py-3 text-xs">{{ $point->awarded_at?->format('d M Y H:i') }}</td>
                    <td class="px-3 py-3 font-serif font-semibold">{{ $point->student?->user?->name ?? '—' }}</td>
                    <td class="px-3 py-3">
                        <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded
                            {{ $point->point_type === 'academic' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $point->point_type === 'attendance' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $point->point_type === 'extracurricular' ? 'bg-purple-100 text-purple-800' : '' }}
                            {{ $point->point_type === 'discipline' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $point->point_type === 'other' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ $point->point_type }}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-right font-mono font-bold {{ $point->points >= 0 ? 'text-green-700' : 'text-red-700' }}">
                        {{ $point->points >= 0 ? '+' : '' }}{{ $point->points }}
                    </td>
                    <td class="px-3 py-3 text-xs max-w-[250px] truncate" title="{{ $point->reason }}">{{ $point->reason }}</td>
                    <td class="px-3 py-3 text-xs">{{ $point->awardedBy?->name ?? 'Otomatis' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada riwayat poin.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $history->links() }}</div>
@endsection

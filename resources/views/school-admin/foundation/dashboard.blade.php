@extends('layouts.school-admin')
@section('title', 'Dashboard Yayasan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7"><div class="elite-kicker mb-2">Yayasan</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">{{ $foundation->name }}</h1>
<div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">Dashboard konsolidasi seluruh cabang sekolah.</p></div>

<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-rule p-5 hover:shadow-md transition">
        <div class="elite-kicker text-[.55rem] text-gray-500 mb-1">Total Sekolah</div>
        <div class="font-display text-2xl ink-primary font-bold">{{ $foundation->schools->count() }}</div>
    </div>
    <div class="bg-white border border-rule p-5 hover:shadow-md transition">
        <div class="elite-kicker text-[.55rem] text-gray-500 mb-1">Total Siswa</div>
        <div class="font-display text-2xl ink-primary font-bold">{{ number_format($totalStudents) }}</div>
    </div>
    <div class="bg-white border border-rule p-5 hover:shadow-md transition">
        <div class="elite-kicker text-[.55rem] text-gray-500 mb-1">Admin Yayasan</div>
        <div class="font-display text-2xl ink-primary font-bold">{{ $foundation->admins->count() }}</div>
    </div>
    <div class="bg-white border border-rule p-5 hover:shadow-md transition">
        <div class="elite-kicker text-[.55rem] text-gray-500 mb-1">NPWP</div>
        <div class="font-mono text-sm ink-primary">{{ $foundation->npwp ?? '—' }}</div>
    </div>
</div>

<div class="bg-white border border-rule overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-rule bg-gray-50/50">
        <h2 class="elite-h3 text-base ink-primary">Perbandingan Antar Cabang</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-[var(--c-primary)] text-white"><tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Sekolah</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Jumlah Siswa</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tingkat Kehadiran</th>
            </tr></thead>
            <tbody>
            @forelse($schoolsData as $sd)
            <tr class="border-t border-rule hover:bg-gray-50">
                <td class="px-4 py-3 font-serif font-semibold ink-primary">{{ $sd['name'] }}</td>
                <td class="px-4 py-3 font-mono text-sm">{{ number_format($sd['student_count']) }}</td>
                <td class="px-4 py-3">
                    @if($sd['attendance_rate'] > 0)
                    <div class="flex items-center gap-2">
                        <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-green-500 rounded-full" style="width:{{ $sd['attendance_rate'] }}%"></div>
                        </div>
                        <span class="text-xs font-mono">{{ $sd['attendance_rate'] }}%</span>
                    </div>
                    @else <span class="text-xs text-gray-400">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="3" class="p-10 text-center text-gray-500 italic font-serif">Belum ada data sekolah cabang.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

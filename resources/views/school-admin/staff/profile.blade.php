@extends('layouts.school-admin')
@section('title', 'Profil Staff — ' . $staff->user?->name)
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.staff.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kembali ke Daftar Staff</a>

{{-- Staff Header --}}
<div class="bg-white border border-rule p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="elite-kicker mb-1">{{ $staff->employee_id ?? '#' . $staff->id }}</div>
            <h1 class="elite-h1 text-2xl ink-primary mb-1">{{ $staff->user?->name }}</h1>
            <div class="elite-rule mb-2"></div>
            <div class="flex flex-wrap gap-3 text-sm text-gray-600 font-serif">
                <span>{{ $staff->department ?? '—' }}</span>
                <span class="text-gray-400">·</span>
                <span>{{ $staff->designation ?? '—' }}</span>
                <span class="text-gray-400">·</span>
                <span>Bergabung {{ $staff->joining_date?->format('d M Y') ?? '—' }}</span>
            </div>
            <div class="flex gap-2 mt-2">
                @foreach($staff->user?->roles ?? [] as $r)
                    <span class="elite-kicker text-[.55rem]" style="color: var(--c-accent);">{{ $r->name }}</span>
                @endforeach
            </div>
        </div>
        <div class="text-right">
            <div class="elite-kicker text-[.6rem] mb-1">Gaji Pokok</div>
            <div class="font-mono text-lg font-bold ink-primary">{{ $staff->basic_salary ? 'Rp ' . number_format($staff->basic_salary / 100, 0, ',', '.') : '—' }}</div>
        </div>
    </div>
</div>

{{-- Tab Navigation --}}
@php
    $tabs = [
        'contracts' => 'Kontrak Kerja',
        'leaves' => 'Riwayat Cuti',
        'overtimes' => 'Lembur',
        'certifications' => 'Sertifikasi',
        'trainings' => 'Pelatihan',
        'payroll' => 'Slip Gaji',
        'kpi' => 'Penilaian KPI',
    ];
    $activeTab = request('tab', 'contracts');
@endphp

<div class="flex flex-wrap gap-1 mb-6 border-b border-rule pb-0">
    @foreach($tabs as $key => $label)
        <a href="?tab={{ $key }}"
           class="px-4 py-2 text-xs font-serif font-semibold rounded-t-lg transition
                  {{ $activeTab === $key ? 'bg-white border border-rule border-b-white -mb-px ink-primary' : 'text-gray-500 hover:text-gray-800' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- Tab Content --}}
<div class="bg-white border border-rule">

    {{-- Contracts --}}
    @if($activeTab === 'contracts')
    <div class="p-5">
        <h2 class="elite-h3 text-lg ink-primary mb-4">Riwayat Kontrak Kerja</h2>
        @if($contracts->isEmpty())
            <p class="text-sm text-gray-500 italic font-serif">Belum ada kontrak kerja.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white">
                    <tr>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Jenis</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Mulai</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Berakhir</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Gaji</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contracts as $c)
                    <tr class="border-t border-rule hover:bg-gray-50">
                        <td class="px-4 py-3 font-serif">{{ ucfirst($c->type) }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $c->start_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $c->end_date?->format('d M Y') ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ 'Rp ' . number_format($c->salary / 100, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded text-[.6rem] font-semibold
                                {{ $c->status === 'active' ? 'bg-green-100 text-green-800' : ($c->status === 'expired' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($c->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

    {{-- Leaves --}}
    @if($activeTab === 'leaves')
    <div class="p-5">
        <h2 class="elite-h3 text-lg ink-primary mb-4">Riwayat Cuti</h2>
        @if($leaves->isEmpty())
            <p class="text-sm text-gray-500 italic font-serif">Belum ada riwayat cuti.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white">
                    <tr>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Jenis</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Dari</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Sampai</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Hari</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Alasan</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaves as $l)
                    <tr class="border-t border-rule hover:bg-gray-50">
                        <td class="px-4 py-3 font-serif">{{ ucfirst($l->type) }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $l->start_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $l->end_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-center">{{ $l->days }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600 max-w-[200px] truncate">{{ $l->reason ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded text-[.6rem] font-semibold
                                {{ $l->status === 'approved' ? 'bg-green-100 text-green-800' : ($l->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($l->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

    {{-- Overtimes --}}
    @if($activeTab === 'overtimes')
    <div class="p-5">
        <h2 class="elite-h3 text-lg ink-primary mb-4">Riwayat Lembur</h2>
        @if($overtimes->isEmpty())
            <p class="text-sm text-gray-500 italic font-serif">Belum ada riwayat lembur.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white">
                    <tr>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tanggal</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Jam</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tarif/Jam</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Total</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Keterangan</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overtimes as $o)
                    <tr class="border-t border-rule hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $o->date->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $o->hours }} jam</td>
                        <td class="px-4 py-3 font-mono text-xs">Rp {{ number_format($o->rate_per_hour / 100, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 font-mono text-xs font-semibold">Rp {{ number_format($o->amount / 100, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600 max-w-[200px] truncate">{{ $o->note ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded text-[.6rem] font-semibold
                                {{ $o->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ ucfirst($o->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

    {{-- Certifications --}}
    @if($activeTab === 'certifications')
    <div class="p-5">
        <h2 class="elite-h3 text-lg ink-primary mb-4">Sertifikasi</h2>
        @if($certifications->isEmpty())
            <p class="text-sm text-gray-500 italic font-serif">Belum ada sertifikasi terdaftar.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white">
                    <tr>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama Sertifikasi</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Lembaga</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">No. Sertifikat</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Terbit</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kadaluarsa</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($certifications as $cert)
                    <tr class="border-t border-rule hover:bg-gray-50">
                        <td class="px-4 py-3 font-serif font-semibold">{{ $cert->certification_name }}</td>
                        <td class="px-4 py-3 text-xs">{{ $cert->issuing_body ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $cert->certificate_number ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $cert->issue_date?->format('d M Y') ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $cert->expiry_date?->format('d M Y') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $isExpired = $cert->expiry_date && $cert->expiry_date->isPast();
                                $isExpiringSoon = $cert->expiry_date && !$isExpired && $cert->expiry_date->diffInDays(now()) <= 30;
                            @endphp
                            <span class="inline-block px-2 py-0.5 rounded text-[.6rem] font-semibold
                                {{ $isExpired ? 'bg-red-100 text-red-800' : ($isExpiringSoon ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                {{ $isExpired ? 'Kadaluarsa' : ($isExpiringSoon ? 'Akan Kadaluarsa' : 'Aktif') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

    {{-- Trainings --}}
    @if($activeTab === 'trainings')
    <div class="p-5">
        <h2 class="elite-h3 text-lg ink-primary mb-4">Pelatihan & Diklat</h2>
        @if($trainings->isEmpty())
            <p class="text-sm text-gray-500 italic font-serif">Belum ada riwayat pelatihan.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white">
                    <tr>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama Pelatihan</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Penyelenggara</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tipe</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tanggal</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Skor</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trainings as $tp)
                    <tr class="border-t border-rule hover:bg-gray-50">
                        <td class="px-4 py-3 font-serif font-semibold">{{ $tp->training?->title ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs">{{ $tp->training?->provider ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs">{{ $tp->training?->training_type ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $tp->training?->start_date?->format('d M Y') ?? '—' }} — {{ $tp->training?->end_date?->format('d M Y') ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $tp->score ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded text-[.6rem] font-semibold
                                {{ $tp->status === 'completed' ? 'bg-green-100 text-green-800' : ($tp->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst(str_replace('_', ' ', $tp->status)) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

    {{-- Payroll --}}
    @if($activeTab === 'payroll')
    <div class="p-5">
        <h2 class="elite-h3 text-lg ink-primary mb-4">Slip Gaji</h2>
        @if($salarySlips->isEmpty())
            <p class="text-sm text-gray-500 italic font-serif">Belum ada slip gaji.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white">
                    <tr>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Bulan</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Gaji Pokok</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tunjangan</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Potongan</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Bersih</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salarySlips as $slip)
                    <tr class="border-t border-rule hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $slip->month }}</td>
                        <td class="px-4 py-3 font-mono text-xs">Rp {{ number_format($slip->basic_salary / 100, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-green-700">+ Rp {{ number_format($slip->total_allowances / 100, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-red-700">- Rp {{ number_format($slip->total_deductions / 100, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 font-mono text-xs font-bold ink-primary">Rp {{ number_format($slip->net_salary / 100, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded text-[.6rem] font-semibold
                                {{ $slip->status === 'paid' ? 'bg-green-100 text-green-800' : ($slip->status === 'approved' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($slip->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

    {{-- KPI --}}
    @if($activeTab === 'kpi')
    <div class="p-5">
        <h2 class="elite-h3 text-lg ink-primary mb-4">Penilaian KPI</h2>
        @if($kpiAppraisals->isEmpty())
            <p class="text-sm text-gray-500 italic font-serif">Belum ada penilaian KPI.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white">
                    <tr>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Periode</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Template</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Skor</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Grade</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Reviewer</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kpiAppraisals as $kpi)
                    <tr class="border-t border-rule hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $kpi->period }}</td>
                        <td class="px-4 py-3 text-xs">{{ $kpi->template?->name ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-xs font-bold">{{ $kpi->total_score ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @php $grade = $kpi->grade; @endphp
                            <span class="inline-block w-7 h-7 rounded text-center leading-7 text-xs font-bold
                                {{ $grade === 'A' ? 'bg-green-100 text-green-800' : ($grade === 'B' ? 'bg-blue-100 text-blue-800' : ($grade === 'C' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                {{ $grade }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs">{{ $kpi->reviewer?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded text-[.6rem] font-semibold
                                {{ $kpi->status === 'completed' ? 'bg-green-100 text-green-800' : ($kpi->status === 'in_review' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst(str_replace('_', ' ', $kpi->status)) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

</div>

@endsection

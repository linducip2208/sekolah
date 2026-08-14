@extends('layouts.school-admin')
@section('title', 'Profil Siswa')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

@php
    $s = $student;
    $u = $s->user;
    $school = \App\Models\School::find($s->school_id);
    $genderLabel = match ($s->gender) { 'male' => 'Laki-laki', 'female' => 'Perempuan', default => 'Lainnya' };
    $attStatus = fn ($st) => match ($st) { 'present' => 'Hadir', 'absent' => 'Alpha', 'late' => 'Terlambat', 'half_day' => 'Setengah Hari', 'on_leave' => 'Izin', default => $st };
    $invStatus = fn ($st) => match ($st) { 'unpaid' => 'Belum Bayar', 'partial' => 'Sebagian', 'paid' => 'Lunas', 'overdue' => 'Terlambat', default => $st };
    $invTone   = fn ($st) => match ($st) { 'paid' => 'success', 'partial' => 'warning', 'overdue' => 'danger', default => 'warning' };
    $riskLevel = $risk?->risk_level ?? $dropout?->risk_level ?? null;
    $riskTone  = match ($riskLevel) { 'critical' => 'danger', 'high' => 'danger', 'medium' => 'warning', 'low' => 'success', default => null };
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="card card-pad">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <x-ui.avatar :name="$u?->name ?? '?'" size="lg" />
            <div class="flex-1 min-w-0">
                <h1 class="page-title">{{ $u?->name ?? '—' }}</h1>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 text-sm text-[var(--color-text-secondary)]">
                    <span>NIS {{ $s->admission_no }}</span>
                    <span class="opacity-40">·</span>
                    <span>{{ $s->classSection?->classRoom?->name }} {{ $s->classSection?->section?->name }}</span>
                    <span class="opacity-40">·</span>
                    <span>{{ $genderLabel }}</span>
                    @if($u?->is_active)<x-ui.badge variant="success">Aktif</x-ui.badge>@else<x-ui.badge variant="danger">Nonaktif</x-ui.badge>@endif
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ui.button href="{{ route('admin.students.edit', $s) }}" variant="secondary" icon="edit">Edit</x-ui.button>
                <x-ui.button href="{{ route('admin.print.id-card', $s) }}" variant="ghost">ID Card</x-ui.button>
                <x-ui.button href="{{ route('admin.print.report-card', $s) }}" variant="ghost">Raport</x-ui.button>
            </div>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <a href="#attendance" class="card card-pad card-hover">
            <div class="text-sm text-[var(--color-text-secondary)]">Kehadiran</div>
            <div class="mt-1 text-2xl font-extrabold">{{ $attSummary['pct'] }}%</div>
            <div class="text-xs text-[var(--color-text-muted)] mt-1">{{ $attSummary['present'] }} hadir dari {{ $attSummary['total'] }} catatan</div>
        </a>
        <a href="#academic" class="card card-pad card-hover">
            <div class="text-sm text-[var(--color-text-secondary)]">Rata-rata Nilai</div>
            <div class="mt-1 text-2xl font-extrabold">{{ $avgMark !== null ? $avgMark.'%' : '—' }}</div>
            <div class="text-xs text-[var(--color-text-muted)] mt-1">{{ $marks->count() }} catatan nilai</div>
        </a>
        <a href="#finance" class="card card-pad card-hover">
            <div class="text-sm text-[var(--color-text-secondary)]">Keuangan</div>
            <div class="mt-1 text-2xl font-extrabold">{{ $financeSummary['unpaid'] }}</div>
            <div class="text-xs text-[var(--color-text-muted)] mt-1">tagihan belum lunas</div>
        </a>
        <div class="card card-pad">
            <div class="text-sm text-[var(--color-text-secondary)]">Risiko Siswa</div>
            <div class="mt-1 text-2xl font-extrabold">{{ $riskLevel ? ucfirst($riskLevel) : '—' }}</div>
            <div class="text-xs text-[var(--color-text-muted)] mt-1">{{ $risk ? $risk->snapshot_date?->format('d M Y') : 'Belum ada penilaian' }}</div>
        </div>
    </div>

    {{-- Tabs --}}
    <div x-data="{ tab: 'overview' }" class="card">
        <div class="flex gap-1 overflow-x-auto px-2 sm:px-4 border-b border-[var(--color-border)]" role="tablist" aria-label="Profil siswa">
            @foreach([
                'overview' => 'Ringkasan',
                'academic' => 'Akademik',
                'attendance' => 'Absensi',
                'discipline' => 'Disiplin',
                'counseling' => 'Konseling',
                'health' => 'Kesehatan',
                'finance' => 'Keuangan',
                'achievements' => 'Prestasi',
                'timeline' => 'Timeline',
            ] as $key => $label)
                <button type="button" role="tab" :aria-selected="(tab === '{{ $key }}').toString()" @click="tab = '{{ $key }}'"
                        class="px-4 py-3 text-sm font-semibold whitespace-nowrap border-b-2 -mb-px transition"
                        :class="tab === '{{ $key }}' ? 'border-[var(--color-primary)] text-[var(--color-primary)]' : 'border-transparent text-[var(--color-text-secondary)] hover:text-[var(--color-text)]'">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="p-4 sm:p-6">
            {{-- Ringkasan --}}
            <div x-show="tab === 'overview'" x-cloak class="space-y-6">
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    @foreach([
                        'Nama Lengkap' => $u?->name,
                        'NIS' => $s->admission_no,
                        'Email' => $u?->email,
                        'Telepon' => $u?->phone,
                        'Kelas' => $s->classSection?->classRoom?->name . ' ' . $s->classSection?->section?->name,
                        'Jenis Kelamin' => $genderLabel,
                        'Tanggal Lahir' => $s->date_of_birth?->format('d M Y'),
                        'Golongan Darah' => $s->blood_group,
                        'Alamat' => $s->address,
                        'Wali' => $s->guardian_name,
                        'Telp Wali' => $s->guardian_phone,
                        'WA' => $s->whatsapp_phone,
                    ] as $label => $val)
                        <div>
                            <div class="text-xs uppercase tracking-wide text-[var(--color-text-muted)]">{{ $label }}</div>
                            <div class="font-medium mt-0.5">{{ $val ?: '—' }}</div>
                        </div>
                    @endforeach
                </div>

                @if($risk || $dropout)
                    <div class="p-4 rounded-lg" style="background: var(--color-warning-soft);">
                        <h3 class="font-semibold mb-2">Early Warning — Risiko Siswa</h3>
                        @if($risk)
                            <div class="text-sm mb-1">Skor keseluruhan: <strong>{{ $risk->overall_risk }}</strong> ({{ $risk->risk_level }})</div>
                            @if($risk->top_risk_factors)
                                <div class="text-sm mb-1">Faktor: {{ implode(', ', (array) $risk->top_risk_factors) }}</div>
                            @endif
                            @if($risk->recommendations)
                                <div class="text-sm">Rekomendasi: {{ implode('; ', (array) $risk->recommendations) }}</div>
                            @endif
                        @endif
                        @if($dropout)
                            <div class="text-sm mt-2">Prediksi dropout (AI): <strong>{{ $dropout->risk_level }}</strong> ({{ $dropout->risk_score }})</div>
                            @if($dropout->recommended_actions)
                                <div class="text-sm">Tindakan: {{ implode('; ', (array) $dropout->recommended_actions) }}</div>
                            @endif
                        @endif
                    </div>
                @endif
            </div>

            {{-- Akademik --}}
            <div x-show="tab === 'academic'" x-cloak id="academic">
                @if($marks->isEmpty())
                    <x-feedback.empty-state icon="edit" title="Belum ada nilai" description="Belum ada catatan nilai untuk siswa ini." />
                @else
                    <div class="table-scroll">
                        <table class="table-elite">
                            <thead><tr><th>Mata Pelajaran</th><th>Ujian</th><th>Nilai</th><th>Grade</th></tr></thead>
                            <tbody>
                                @foreach($marks as $m)
                                    <tr>
                                        <td>{{ $m->subject?->name ?? '—' }}</td>
                                        <td>{{ $m->exam?->name ?? '—' }}</td>
                                        <td>{{ $m->obtained_marks }} / {{ $m->total_marks }}</td>
                                        <td><x-ui.badge variant="accent">{{ $m->grade ?? '—' }}</x-ui.badge></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Absensi --}}
            <div x-show="tab === 'attendance'" x-cloak id="attendance">
                @if($attSummary['total'] === 0)
                    <x-feedback.empty-state icon="check" title="Belum ada absensi" description="Belum ada catatan kehadiran untuk siswa ini." />
                @else
                    <div class="grid grid-cols-3 gap-3 mb-5 max-w-md">
                        <div class="card card-pad"><div class="text-sm text-[var(--color-text-secondary)]">Hadir</div><div class="text-xl font-bold text-[var(--color-success)]">{{ $attSummary['present'] }}</div></div>
                        <div class="card card-pad"><div class="text-sm text-[var(--color-text-secondary)]">Alpha</div><div class="text-xl font-bold text-[var(--color-danger)]">{{ $attSummary['absent'] }}</div></div>
                        <div class="card card-pad"><div class="text-sm text-[var(--color-text-secondary)]">Total</div><div class="text-xl font-bold">{{ $attSummary['total'] }}</div></div>
                    </div>
                    <p class="text-sm text-[var(--color-text-muted)]">Lihat detail di <a class="text-[var(--color-primary)]" href="{{ route('admin.attendance.index') }}">Absensi Harian</a>.</p>
                @endif
            </div>

            {{-- Disiplin --}}
            <div x-show="tab === 'discipline'" x-cloak>
                @if($discipline->isEmpty())
                    <x-feedback.empty-state icon="check" title="Catatan bersih" description="Tidak ada catatan pelanggaran untuk siswa ini." />
                @else
                    <div class="table-scroll">
                        <table class="table-elite">
                            <thead><tr><th>Tanggal</th><th>Deskripsi</th><th>Poin</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($discipline as $d)
                                    <tr>
                                        <td>{{ $d->incident_date?->format('d M Y') }}</td>
                                        <td>{{ $d->description }}</td>
                                        <td>{{ $d->points ?? 0 }}</td>
                                        <td><x-ui.badge variant="warning">{{ $d->status }}</x-ui.badge></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Konseling --}}
            <div x-show="tab === 'counseling'" x-cloak>
                @if($counseling->isEmpty())
                    <x-feedback.empty-state icon="user" title="Belum ada sesi" description="Belum ada sesi konseling untuk siswa ini." />
                @else
                    <div class="table-scroll">
                        <table class="table-elite">
                            <thead><tr><th>Tanggal</th><th>Tipe</th><th>Status</th><th>Catatan</th></tr></thead>
                            <tbody>
                                @foreach($counseling as $c)
                                    <tr>
                                        <td>{{ $c->scheduled_at?->format('d M Y H:i') }}</td>
                                        <td>{{ $c->type }}</td>
                                        <td><x-ui.badge variant="accent">{{ $c->status }}</x-ui.badge></td>
                                        <td class="max-w-xs truncate">{{ $c->notes }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Kesehatan --}}
            <div x-show="tab === 'health'" x-cloak>
                @if($health->isEmpty())
                    <x-feedback.empty-state icon="device" title="Belum ada kunjungan" description="Belum ada riwayat kunjungan UKS / klinik." />
                @else
                    <div class="table-scroll">
                        <table class="table-elite">
                            <thead><tr><th>Tanggal</th><th>Keluhan</th><th>Diagnosis</th><th>Penanganan</th></tr></thead>
                            <tbody>
                                @foreach($health as $h)
                                    <tr>
                                        <td>{{ $h->visit_at?->format('d M Y') }}</td>
                                        <td>{{ $h->symptoms }}</td>
                                        <td>{{ $h->diagnosis }}</td>
                                        <td>{{ $h->treatment }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Keuangan --}}
            <div x-show="tab === 'finance'" x-cloak id="finance">
                <div class="flex gap-3 mb-5">
                    <div class="card card-pad"><div class="text-sm text-[var(--color-text-secondary)]">Total tagihan</div><div class="text-xl font-bold">{{ $financeSummary['total'] }}</div></div>
                    <div class="card card-pad"><div class="text-sm text-[var(--color-text-secondary)]">Belum lunas</div><div class="text-xl font-bold text-[var(--color-warning)]">{{ $financeSummary['unpaid'] }}</div></div>
                    <div class="card card-pad"><div class="text-sm text-[var(--color-text-secondary)]">Piutang</div><div class="text-xl font-bold">{{ money($financeSummary['outstanding'], $school) }}</div></div>
                </div>
                @if($invoices->isEmpty())
                    <x-feedback.empty-state icon="inbox" title="Belum ada tagihan" description="Belum ada tagihan untuk siswa ini." />
                @else
                    <div class="table-scroll">
                        <table class="table-elite">
                            <thead><tr><th>No. Invoice</th><th>Periode</th><th>Jumlah</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($invoices as $inv)
                                    <tr>
                                        <td>{{ $inv->invoice_no }}</td>
                                        <td>{{ $inv->period }}</td>
                                        <td>{{ money($inv->amount, $school) }}</td>
                                        <td><x-ui.badge :variant="$invTone($inv->status)">{{ $invStatus($inv->status) }}</x-ui.badge></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Prestasi --}}
            <div x-show="tab === 'achievements'" x-cloak>
                @if($achievements->isEmpty())
                    <x-feedback.empty-state icon="school" title="Belum ada prestasi" description="Belum ada prestasi yang tercatat untuk siswa ini." />
                @else
                    <div class="grid sm:grid-cols-2 gap-3">
                        @foreach($achievements as $a)
                            <div class="card card-pad">
                                <div class="font-semibold">{{ $a->title }}</div>
                                <div class="text-sm text-[var(--color-text-secondary)] mt-1">{{ $a->description }}</div>
                                <div class="text-xs text-[var(--color-text-muted)] mt-2">{{ $a->achieved_at?->format('d M Y') }} · {{ $a->issuer }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Timeline --}}
            <div x-show="tab === 'timeline'" x-cloak>
                @if($activities->isEmpty())
                    <x-feedback.empty-state icon="refresh" title="Belum ada aktivitas" description="Belum ada log aktivitas untuk siswa ini." />
                @else
                    <ol class="space-y-0">
                        @foreach($activities as $act)
                            <li class="relative pl-6 pb-5" style="border-left: 2px solid var(--color-border);">
                                <span class="absolute -left-1.5 top-1 w-3 h-3 rounded-full" style="background: var(--color-primary);"></span>
                                <div class="font-medium text-sm">{{ $act->title }}</div>
                                <div class="text-xs text-[var(--color-text-muted)]">{{ $act->description }}</div>
                                <div class="text-xs text-[var(--color-text-muted)] mt-1">{{ $act->created_at?->diffForHumans() }}</div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

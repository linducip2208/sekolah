@extends('layouts.school-admin')
@section('title', 'Konfigurasi BPJS')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="flex justify-between items-end">
        <div>
            <div class="elite-kicker mb-2">Constitutio Securitatis</div>
            <h1 class="elite-h1 text-3xl ink-primary mb-2">Konfigurasi BPJS</h1>
            <div class="elite-rule"></div>
            <p class="font-serif text-sm text-gray-600 mt-3">Atur tarif BPJS Kesehatan dan Ketenagakerjaan untuk perhitungan slip gaji.</p>
        </div>
        <a href="{{ route('admin.payroll.slips.index') }}" class="btn-elite-ghost">← Slip Gaji</a>
    </div>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<form method="POST" action="{{ route('admin.payroll.bpjs.update') }}">
    @csrf
    <div class="grid lg:grid-cols-2 gap-6">

        {{-- BPJS Kesehatan --}}
        <div class="bg-white border border-rule p-6">
            <div class="elite-kicker text-[.7rem] mb-4">BPJS Kesehatan</div>
            <div class="space-y-3">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Iuran Karyawan (%)</label>
                    <input type="number" step="0.01" name="kesehatan_employee_pct" value="{{ number_format($config->kesehatan_employee_pct / 100, 2, '.', '') }}" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" required>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Iuran Perusahaan (%)</label>
                    <input type="number" step="0.01" name="kesehatan_employer_pct" value="{{ number_format($config->kesehatan_employer_pct / 100, 2, '.', '') }}" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" required>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Batas Gaji (Rp/bulan)</label>
                    <input type="number" name="kesehatan_salary_cap_rupiah" value="{{ number_format($config->kesehatan_salary_cap / 100, 0, '.', '') }}" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" required>
                </div>
            </div>
        </div>

        {{-- BPJS Ketenagakerjaan --}}
        <div class="bg-white border border-rule p-6">
            <div class="elite-kicker text-[.7rem] mb-4">BPJS Ketenagakerjaan</div>
            <div class="space-y-3">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">JKK (%)</label>
                    <input type="number" step="0.01" name="jkk_pct" value="{{ number_format($config->jkk_pct / 100, 2, '.', '') }}" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" required>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">JKM (%)</label>
                    <input type="number" step="0.01" name="jkm_pct" value="{{ number_format($config->jkm_pct / 100, 2, '.', '') }}" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" required>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">JHT Karyawan (%)</label>
                    <input type="number" step="0.01" name="jht_employee_pct" value="{{ number_format($config->jht_employee_pct / 100, 2, '.', '') }}" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" required>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">JHT Perusahaan (%)</label>
                    <input type="number" step="0.01" name="jht_employer_pct" value="{{ number_format($config->jht_employer_pct / 100, 2, '.', '') }}" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" required>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">JP Karyawan (%)</label>
                    <input type="number" step="0.01" name="jp_employee_pct" value="{{ number_format($config->jp_employee_pct / 100, 2, '.', '') }}" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" required>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">JP Perusahaan (%)</label>
                    <input type="number" step="0.01" name="jp_employer_pct" value="{{ number_format($config->jp_employer_pct / 100, 2, '.', '') }}" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" required>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Batas JP (Rp/bulan)</label>
                    <input type="number" name="jp_salary_cap_rupiah" value="{{ number_format($config->jp_salary_cap / 100, 0, '.', '') }}" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" required>
                </div>
            </div>
        </div>

    </div>

    <div class="mt-4">
        <button class="btn-elite" style="padding:.6rem 1.5rem;font-size:.65rem;">Simpan Konfigurasi</button>
    </div>
</form>

@endsection

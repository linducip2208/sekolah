@extends('layouts.school-admin')
@section('title', $title)
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7">
    <a href="{{ route('admin.import.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent inline-block mb-3">← Kembali ke Import</a>
    <div class="elite-kicker mb-2">Validation Gate</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">{{ $title }}</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-base text-gray-600 mt-3">Belum ada data yang ditulis. Periksa baris valid dan error berikut sebelum konfirmasi.</p>
</div>

<div class="grid sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white border border-rule p-5"><div class="elite-kicker text-xs">Total baris</div><div class="text-2xl font-bold ink-primary mt-1">{{ count($payload['rows']) }}</div></div>
    <div class="bg-green-50 border border-green-200 p-5"><div class="elite-kicker text-xs text-green-700">Siap diimport</div><div class="text-2xl font-bold text-green-800 mt-1">{{ $payload['valid_count'] }}</div></div>
    <div class="bg-red-50 border border-red-200 p-5"><div class="elite-kicker text-xs text-red-700">Perlu diperbaiki</div><div class="text-2xl font-bold text-red-800 mt-1">{{ $payload['invalid_count'] }}</div></div>
</div>

@if($payload['invalid_count'] > 0)
<div class="bg-red-50 border-l-4 border-red-600 p-5 mb-6">
    <h2 class="font-semibold text-red-900 mb-2">Baris yang akan dilewati</h2>
    <ul class="space-y-1 text-sm text-red-800">
        @foreach(array_filter($payload['rows'], fn ($row) => ! $row['valid']) as $row)
            <li><span class="font-mono">Baris {{ $row['line'] }}</span>: {{ implode(' ', $row['errors']) }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="bg-white border border-rule overflow-hidden">
    <div class="px-5 py-4 border-b border-rule flex flex-wrap items-center justify-between gap-3">
        <div><h2 class="font-semibold ink-primary">Preview data</h2><p class="text-xs text-gray-500 mt-1">Password tidak ditampilkan dan tersimpan terenkripsi sampai konfirmasi.</p></div>
        @if($payload['valid_count'] > 0)
            <form method="POST" action="{{ route($kind === 'students' ? 'admin.import.students.confirm' : 'admin.import.staff.confirm') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <button type="submit" class="btn-elite" onclick="return confirm('Import semua baris valid sekarang?')">Konfirmasi Import {{ $payload['valid_count'] }} Baris</button>
            </form>
        @endif
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-rule text-left">
                <th class="px-4 py-3">Baris</th>
                @if($kind === 'students')
                    <th class="px-4 py-3">No. Pendaftaran</th><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Gender</th>
                @else
                    <th class="px-4 py-3">Nama</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Role</th>
                @endif
                <th class="px-4 py-3">Status</th>
            </tr></thead>
            <tbody>
            @forelse($payload['rows'] as $row)
                <tr class="border-b border-rule last:border-0 {{ $row['valid'] ? '' : 'bg-red-50' }}">
                    <td class="px-4 py-3 font-mono text-xs">{{ $row['line'] }}</td>
                    @if($kind === 'students')
                        <td class="px-4 py-3">{{ $row['data']['admission_no'] ?? '—' }}</td><td class="px-4 py-3">{{ $row['data']['name'] ?? '—' }}</td><td class="px-4 py-3">{{ $row['data']['email'] ?? '—' }}</td><td class="px-4 py-3">{{ $row['data']['gender'] ?? '—' }}</td>
                    @else
                        <td class="px-4 py-3">{{ $row['data']['name'] ?? '—' }}</td><td class="px-4 py-3">{{ $row['data']['email'] ?? '—' }}</td><td class="px-4 py-3">{{ $row['data']['role'] ?? '—' }}</td>
                    @endif
                    <td class="px-4 py-3"><span class="px-2 py-1 rounded text-xs {{ $row['valid'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $row['valid'] ? 'Valid' : implode(' ', $row['errors']) }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">Belum ada data.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

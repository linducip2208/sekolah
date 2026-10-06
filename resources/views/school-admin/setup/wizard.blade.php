@extends('layouts.school-admin')
@section('title', 'Setup Wizard')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
@php $step = request('step', 'profile'); @endphp
<div class="mb-7"><div class="elite-kicker mb-2">Onboarding</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">{{ __('Setup Wizard — 5 menit jadi') }}</h1><div class="elite-rule"></div>
<p class="font-serif text-base text-gray-600 mt-3">{{ __('Selesaikan langkah berikut. Progress tersimpan otomatis.') }}</p></div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 text-sm text-green-800 border-l-4 border-green-700">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-4 p-3 bg-red-50 text-sm text-red-800 border-l-4 border-red-700">{{ $errors->first() }}</div>@endif

<div class="mb-6 flex items-center gap-2 text-sm">
<div class="flex-1 h-2 bg-gray-100 rounded"><div class="h-2 bg-blue-600 rounded" style="width: {{ $progress['percent'] }}%"></div></div>
<span class="font-mono">{{ $progress['done'] }}/{{ $progress['total'] }} ({{ $progress['percent'] }}%)</span>
</div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="bg-white border border-rule p-6">
<div class="elite-kicker mb-2">Langkah 1 — Profil</div>
<h3 class="elite-h3 text-lg mb-3">Profil Sekolah</h3>
<form method="POST" action="{{ route('admin.setup.wizard.profile') }}" class="space-y-3">@csrf
<input name="name" required maxlength="150" value="{{ old('name', $school->name) }}" placeholder="Nama sekolah" class="w-full border-2 border-rule px-3 py-2 text-sm">
<input name="phone" maxlength="30" value="{{ old('phone', $school->phone) }}" placeholder="Telepon/WA" class="w-full border-2 border-rule px-3 py-2 text-sm">
<input name="address" maxlength="255" value="{{ old('address', $school->address) }}" placeholder="Alamat" class="w-full border-2 border-rule px-3 py-2 text-sm">
<button class="btn-elite">Simpan & Lanjut →</button>
</form>
</div>

<div class="bg-white border border-rule p-6">
<div class="elite-kicker mb-2">Langkah 2 — Tahun Ajaran</div>
<h3 class="elite-h3 text-lg mb-3">Aktifkan Tahun Ajaran</h3>
<form method="POST" action="{{ route('admin.setup.wizard.year') }}" class="space-y-3">@csrf
<input name="name" required maxlength="50" placeholder="cth: 2026/2027" class="w-full border-2 border-rule px-3 py-2 text-sm">
<input name="start_date" required type="date" class="w-full border-2 border-rule px-3 py-2 text-sm">
<input name="end_date" required type="date" class="w-full border-2 border-rule px-3 py-2 text-sm">
<button class="btn-elite">Buat & Aktifkan →</button>
</form>
@if($years->count())<ul class="mt-3 text-xs text-gray-600">@foreach($years as $y)<li>{{ $y->name }} {{ $y->is_active ? '● aktif' : '' }}</li>@endforeach</ul>@endif
</div>

<div class="bg-white border border-rule p-6">
<div class="elite-kicker mb-2">Langkah 3 — Kelas</div>
<h3 class="elite-h3 text-lg mb-3">Kelas Pertama</h3>
<form method="POST" action="{{ route('admin.setup.wizard.class') }}" class="space-y-3">@csrf
<input name="name" required maxlength="100" placeholder="cth: Kelas 7A" class="w-full border-2 border-rule px-3 py-2 text-sm">
<button class="btn-elite">Tambah Kelas →</button>
</form>
@if($classes->count())<ul class="mt-3 text-xs text-gray-600">@foreach($classes as $c)<li>{{ $c->name }}</li>@endforeach</ul>@endif
</div>

<div class="bg-white border border-rule p-6">
<div class="elite-kicker mb-2">Langkah 4 — Siswa</div>
<h3 class="elite-h3 text-lg mb-3">Import Siswa Massal</h3>
<p class="text-sm text-gray-600 mb-3">Upload CSV atau Excel (.xlsx) — preview, validasi duplikat, kuota paket dicek otomatis.</p>
<a href="{{ route('admin.import.index') }}" class="btn-elite inline-block">Buka Import →</a>
<a href="{{ route('admin.import.template.students') }}" class="block mt-2 text-xs underline">⤓ Template CSV</a>
</div>

<div class="bg-white border border-rule p-6">
<div class="elite-kicker mb-2">Langkah 5 — SPP</div>
<h3 class="elite-h3 text-lg mb-3">Struktur SPP</h3>
<form method="POST" action="{{ route('admin.setup.wizard.fee') }}" class="space-y-3">@csrf
<input name="name" required maxlength="100" placeholder="cth: SPP Bulanan" class="w-full border-2 border-rule px-3 py-2 text-sm">
<input name="amount_rupiah" required inputmode="numeric" placeholder="Nominal Rp per bulan" class="w-full border-2 border-rule px-3 py-2 text-sm">
<button class="btn-elite">Simpan SPP →</button>
</form>
@if($fees->count())<ul class="mt-3 text-xs text-gray-600">@foreach($fees as $f)<li>{{ $f->name }}</li>@endforeach</ul>@endif
</div>

<div class="bg-white border border-rule p-6">
<div class="elite-kicker mb-2">Selesai</div>
<h3 class="elite-h3 text-lg mb-3">Siap Beroperasi</h3>
<ol class="text-sm text-gray-700 space-y-1">@foreach($progress['steps'] as $s)<li>{{ ($s['done'] ? '✓' : '○').' '.$s['label'] }}</li>@endforeach</ol>
<a href="{{ route('admin.dashboard') }}" class="btn-elite inline-block mt-3">Ke Dashboard →</a>
</div>
</div>
@endsection

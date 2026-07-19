@extends('super-admin.layout')
@section('title', 'Maintenance Mode')
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Status Manutentionis</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Maintenance Mode</h1><div class="elite-rule"></div></div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 text-sm text-green-800 border-l-4 border-green-700">{{ session('success') }}</div>@endif

<div class="bg-white border-l-4 {{ $is_down ? 'border-red-700' : 'border-green-700' }} p-7 mb-6">
<div class="elite-kicker mb-2">Status saat ini</div>
<h2 class="font-display text-3xl ink-primary mb-2">{{ $is_down ? 'MAINTENANCE MODE AKTIF' : 'NORMAL OPERATION' }}</h2>
<p class="font-serif text-sm text-gray-700">{{ $is_down ? 'Pengunjung tidak bisa akses (kecuali super admin via secret URL).' : 'Semua route dapat diakses normal oleh user.' }}</p>
</div>

@if($is_down)
<form method="POST" action="{{ route('super.maintenance.disable') }}">
@csrf
<button class="btn-elite">Matikan Maintenance Mode</button>
</form>
@else
<form method="POST" action="{{ route('super.maintenance.enable') }}" class="bg-white border border-rule p-6 space-y-3">
@csrf
<input name="message" maxlength="500" placeholder="Pesan untuk visitor (opsional)" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<button class="btn-elite-gold">Aktifkan Maintenance Mode</button>
</form>
@endif
@endsection

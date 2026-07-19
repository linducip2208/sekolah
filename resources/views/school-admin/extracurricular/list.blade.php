@extends('layouts.school-admin')
@section('title', 'Ekstrakurikuler')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Activitates Extracurriculares</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Ekstrakurikuler</h1><div class="elite-rule"></div></div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Ekskul</summary>
<form method="POST" action="{{ route('admin.extracurricular.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-2 gap-3">@csrf
<input name="name" required maxlength="200" placeholder="Pramuka/Robotik/dll" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<select name="coach_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— Pelatih —</option>
@foreach($coaches as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
</select>
<input name="schedule" maxlength="200" placeholder="Jadwal (Sabtu 13:00-15:00)" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<input type="number" min="1" name="capacity" placeholder="Kapasitas" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<input type="number" step="1000" min="0" name="fee_per_month_rupiah" placeholder="Biaya/bulan (Rp)" class="md:col-span-2 border-2 border-rule px-3 py-2 font-mono text-sm">
<textarea name="description" rows="2" placeholder="Deskripsi" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
<div class="md:col-span-2"><button class="btn-elite">Simpan</button></div>
</form></details>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
@forelse($extras as $e)
<div class="elite-card p-5 {{ !$e->is_active ? 'opacity-50' : '' }}">
<h3 class="elite-h3 text-base ink-primary mb-1">{{ $e->name }}</h3>
<p class="text-xs text-gray-500 mb-2">{{ $e->coach?->name ?? 'Tanpa pelatih' }}</p>
@if(!empty($e->schedule['description']))<p class="text-xs text-gray-700">⏰ {{ $e->schedule['description'] }}</p>@endif
@if($e->capacity)<p class="text-xs text-gray-700">👥 Kapasitas: {{ $e->capacity }}</p>@endif
<p class="text-xs font-mono mt-2">Rp {{ number_format($e->fee_per_month/100, 0, ',', '.') }}/bln</p>
@if($e->description)<p class="text-xs text-gray-600 mt-2">{{ Str::limit($e->description, 80) }}</p>@endif
<form method="POST" action="{{ route('admin.extracurricular.destroy', $e) }}" class="mt-3 text-right" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
</div>
@empty<div class="md:col-span-2 lg:col-span-3 bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada ekskul.</div>
@endforelse
</div>
@endsection

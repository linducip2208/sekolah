@extends('layouts.school-admin')
@section('title', 'Laporan Bullying')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.counseling.sessions.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Sesi Konseling</a>

<div class="mb-7"><div class="elite-kicker mb-2">Relationes Vexationis</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Laporan Bullying</h1>
<div class="elite-rule"></div></div>

<div class="space-y-3">
@forelse($reports as $r)
<div class="bg-white border border-rule p-5">
<div class="flex justify-between items-start mb-2">
<div class="flex items-baseline gap-3">
<h3 class="elite-h3 text-base ink-primary">Laporan #{{ $r->id }} · {{ $r->type }}</h3>
<span class="text-xs px-2 py-0.5 rounded
{{ $r->status==='received' ? 'bg-gray-100 text-gray-700' : '' }}
{{ $r->status==='investigating' ? 'bg-yellow-100 text-yellow-700' : '' }}
{{ $r->status==='action_taken' ? 'bg-green-100 text-green-700' : '' }}
{{ $r->status==='closed' ? 'bg-blue-100 text-blue-700' : '' }}
{{ $r->status==='unfounded' ? 'bg-red-100 text-red-700' : '' }}">{{ $r->status }}</span>
@if($r->is_anonymous)<span class="text-xs text-gray-500">(Anonim)</span>@endif
</div>
<span class="text-xs text-gray-400">{{ $r->incident_date?->format('d M Y') }} · {{ $r->location ?? '—' }}</span>
</div>
<p class="font-serif text-sm text-gray-700 mb-3">{{ Str::limit($r->description, 250) }}</p>
<form method="POST" action="{{ route('admin.counseling.bullying.update', $r) }}" class="grid md:grid-cols-3 gap-2">
@csrf @method('PUT')
<select name="status" class="border-2 border-rule px-2 py-1 text-xs">
@foreach(['received','investigating','action_taken','closed','unfounded'] as $st)
<option value="{{ $st }}" @selected($r->status===$st)>{{ $st }}</option>
@endforeach
</select>
<input type="text" name="investigation_notes" value="{{ $r->investigation_notes }}" placeholder="Catatan investigasi" class="md:col-span-2 border-2 border-rule px-2 py-1 text-xs">
<input type="text" name="action_summary" value="{{ $r->action_summary }}" placeholder="Tindakan diambil" class="md:col-span-3 border-2 border-rule px-2 py-1 text-xs">
<button class="btn-elite md:col-span-3" style="padding:.4rem;font-size:.6rem;">Simpan</button>
</form>
</div>
@empty<div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada laporan bullying.</div>
@endforelse
</div>
<div class="mt-4">{{ $reports->links() }}</div>
@endsection

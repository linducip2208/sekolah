@extends('layouts.school-admin')
@section('title', 'Distribusi Grade')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><h1 class="elite-h1 text-3xl ink-primary mb-2">Distribusi Grade per Mapel</h1><div class="elite-rule"></div></div>

<div class="grid md:grid-cols-2 gap-6">
@forelse($rows as $subject => $grades)
<div class="bg-white border border-rule p-6">
<h3 class="elite-h3 text-base ink-primary mb-3">{{ $subject }}</h3>
@php $total = $grades->sum('cnt'); @endphp
<div class="space-y-2">
@foreach(['A', 'B', 'C', 'D', 'E'] as $g)
@php
    $cnt = $grades->where('grade', $g)->first()->cnt ?? 0;
    $pct = $total > 0 ? round($cnt / $total * 100, 1) : 0;
@endphp
<div>
<div class="flex justify-between text-xs mb-1">
<span class="font-display font-bold">{{ $g }}</span>
<span class="font-mono">{{ $cnt }} ({{ $pct }}%)</span>
</div>
<div class="bg-gray-200 h-2 rounded">
<div style="width:{{ $pct }}%; background: {{ $g === 'A' ? '#16a34a' : ($g === 'B' ? '#3b82f6' : ($g === 'C' ? '#eab308' : ($g === 'D' ? '#f97316' : '#dc2626'))) }};" class="h-full rounded"></div>
</div>
</div>
@endforeach
</div>
<div class="text-xs text-gray-500 mt-3">Total {{ $total }} nilai</div>
</div>
@empty<div class="md:col-span-2 bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada nilai untuk dianalisis.</div>
@endforelse
</div>
@endsection

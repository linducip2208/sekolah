@extends('layouts.school-admin')
@section('title', 'AI Recommendation')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Consilium AI</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">AI Recommendation</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">{{ $pendingCount }} rekomendasi menunggu tindakan. Setiap tindakan sensitif tetap butuh approval manusia.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif

<div class="mb-6">
    <form method="POST" action="{{ route('admin.ai.recommendations.generate') }}" class="inline">@csrf
        <button class="btn-elite">Generate Rekomendasi</button>
    </form>
    <span class="text-xs text-gray-400 ml-3">Dari siswa at-risk (high/critical).</span>
</div>

<div class="space-y-4">
    @forelse($recommendations as $r)
    <div class="bg-white border border-rule p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <span class="font-serif font-semibold">{{ $r->student?->user?->name ?? '—' }}</span>
                    <span class="text-xs px-2 py-0.5 rounded {{ $r->risk_level === 'critical' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800' }}">{{ strtoupper($r->risk_level) }}</span>
                    <span class="text-xs text-gray-400">{{ $r->status }}</span>
                </div>
                <div class="mt-2 space-y-1">
                    @foreach($r->actions ?? [] as $a)
                    <div class="text-sm flex items-start gap-2"><span class="text-[var(--c-accent)]">→</span> {{ $a }}</div>
                    @endforeach
                </div>
            </div>
            <div class="flex gap-2 text-xs shrink-0">
                @if($r->status === 'pending')
                <form method="POST" action="{{ route('admin.ai.recommendations.action', $r) }}" class="inline">@csrf<button class="text-green-700 underline">Tindak Lanjuti</button></form>
                <form method="POST" action="{{ route('admin.ai.recommendations.dismiss', $r) }}" class="inline" onsubmit="return confirm('Buang rekomendasi?')">@csrf<button class="text-red-700 underline">Buang</button></form>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada rekomendasi.</div>
    @endforelse
</div>
<div class="mt-4">{{ $recommendations->links() }}</div>

@endsection

@extends('layouts.school-admin')
@section('title', 'Peringatan Darurat')
@section('sidebar')
    @include('school-admin.partials.sidebar')
@endsection

@section('content')
<div class="mb-7">
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Peringatan Darurat</h1>
    <div class="elite-rule"></div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-7">
    <div class="bg-white border-l-4 border-red-600 p-5">
        <div class="elite-kicker text-[.6rem]">Total Terkirim</div>
        <div class="font-display text-3xl ink-primary mt-2">{{ $totalSent }}</div>
    </div>
    <div class="bg-white border-l-4 border-yellow-500 p-5">
        <div class="elite-kicker text-[.6rem]">Draft Tertunda</div>
        <div class="font-display text-3xl ink-primary mt-2">{{ $totalDraft }}</div>
    </div>
    <div class="bg-white border-l-4 border-blue-600 p-5">
        <div class="elite-kicker text-[.6rem]">Terakhir Dikirim</div>
        <div class="font-display text-base ink-primary mt-2">{{ $lastSent?->sent_at?->diffForHumans() ?? '—' }}</div>
    </div>
</div>

<div class="flex justify-between items-center mb-4">
    <div>
        <a href="{{ route('admin.emergency.create') }}" class="btn-elite" style="background:#dc2626; border-color:#dc2626;">
            + Buat Peringatan Darurat
        </a>
        <a href="{{ route('admin.emergency.contacts') }}" class="btn-elite-ghost ml-3">Kontak Darurat</a>
    </div>
</div>

<div class="table-scroll">
<table class="table-elite w-full">
    <thead>
        <tr>
            <th>Tipe</th>
            <th>Judul</th>
            <th>Severity</th>
            <th>Status</th>
            <th>Penerima</th>
            <th>Dipicu Oleh</th>
            <th>Waktu</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse($alerts as $a)
        <tr>
            <td data-label="Tipe">
                @php
                    $typeIcons = [
                        'fire' => '🔥', 'earthquake' => '🌍', 'flood' => '🌊',
                        'security' => '🛡️', 'medical' => '🏥', 'other' => '⚠️',
                    ];
                @endphp
                <span class="text-lg">{{ $typeIcons[$a->alert_type] ?? '⚠️' }}</span>
                {{ ucfirst($a->alert_type) }}
            </td>
            <td data-label="Judul" class="font-serif font-semibold">{{ $a->title }}</td>
            <td data-label="Severity">
                @if($a->severity === 'critical')
                    <span class="px-2 py-1 text-xs font-mono" style="background:#dc2626; color:#fff;">KRITIS</span>
                @elseif($a->severity === 'warning')
                    <span class="px-2 py-1 text-xs font-mono" style="background:#eab308; color:#000;">WASPADA</span>
                @else
                    <span class="px-2 py-1 text-xs font-mono border">INFO</span>
                @endif
            </td>
            <td data-label="Status">
                @if($a->status === 'sent')
                    <span class="ink-accent">Terkirim</span>
                @elseif($a->status === 'cancelled')
                    <span class="text-gray-400">Dibatalkan</span>
                @else
                    <span class="text-yellow-600">Draft</span>
                @endif
            </td>
            <td data-label="Penerima">{{ $a->recipient_count }}</td>
            <td data-label="Dipicu Oleh">{{ $a->triggeredBy?->name }}</td>
            <td data-label="Waktu" class="text-xs">{{ $a->created_at->format('d/m/Y H:i') }}</td>
            <td>
                @if($a->status === 'draft')
                    <form method="POST" action="{{ route('admin.emergency.cancel', $a) }}" class="inline">
                        @csrf
                        <button class="text-xs text-red-600 hover:underline">Batalkan</button>
                    </form>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center text-gray-500 italic py-8">Belum ada peringatan darurat.</td></tr>
        @endforelse
    </tbody>
</table>
</div>
@endsection

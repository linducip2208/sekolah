@extends('layouts.school-admin')
@section('title', 'Events')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')
<div class="space-y-6">
    <div class="flex justify-between">
        <h2 class="text-xl font-bold">Event Management</h2>
        <button class="btn-brand">+ Tambah Event</button>
    </div>

    <div class="bg-white rounded-lg p-5 shadow">
        <div class="text-xs text-gray-500">Total RSVP Going (acara mendatang)</div>
        <div class="text-3xl font-bold">{{ $totalRsvps }}</div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b font-bold">Event Mendatang</div>
        <div class="divide-y">
            @forelse($upcoming as $e)
                <div class="p-4">
                    <div class="flex justify-between">
                        <h3 class="font-bold">{{ $e->title }}</h3>
                        <span class="px-2 py-0.5 bg-purple-50 text-purple-800 rounded text-xs">{{ $e->event_type }}</span>
                    </div>
                    <div class="text-sm text-gray-600 mt-1">
                        📅 {{ $e->starts_at->format('d M Y H:i') }} — {{ $e->ends_at->format('H:i') }}
                    </div>
                    <div class="text-sm text-gray-600">📍 {{ $e->venue }}@if($e->city), {{ $e->city }}@endif</div>
                    @if($e->ticket_price > 0)
                        <div class="text-sm font-bold mt-1">💰 Rp {{ number_format($e->ticket_price / 100, 0, ',', '.') }}</div>
                    @endif
                    <div class="text-xs text-gray-500 mt-2">
                        Capacity: {{ $e->capacity ?? '∞' }}
                        @if(!$e->is_published)
                            <span class="ml-2 text-orange-600">⚠️ Belum di-publish</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">Belum ada event mendatang</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

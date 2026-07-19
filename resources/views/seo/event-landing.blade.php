@extends('seo.layout')
@section('content')
<article class="prose max-w-none">
    @if($event->cover_image_path)
        <img src="{{ $event->cover_image_path }}" alt="{{ $event->title }}" class="w-full rounded-lg mb-6">
    @endif

    <h1 class="text-3xl font-bold mb-2">{{ $event->title }}</h1>
    <p class="text-gray-600">Acara di <strong>{{ $school->name }}</strong></p>

    <div class="not-prose my-6 grid grid-cols-2 gap-4 text-sm">
        <div class="bg-gray-50 p-4 rounded">
            <div class="text-gray-500 text-xs">Mulai</div>
            <div class="font-bold">{{ $event->starts_at->format('d M Y H:i') }}</div>
        </div>
        <div class="bg-gray-50 p-4 rounded">
            <div class="text-gray-500 text-xs">Selesai</div>
            <div class="font-bold">{{ $event->ends_at->format('d M Y H:i') }}</div>
        </div>
        <div class="bg-gray-50 p-4 rounded">
            <div class="text-gray-500 text-xs">Tempat</div>
            <div class="font-bold">{{ $event->venue }}</div>
        </div>
        <div class="bg-gray-50 p-4 rounded">
            <div class="text-gray-500 text-xs">Kota</div>
            <div class="font-bold">{{ $event->city ?? '—' }}</div>
        </div>
    </div>

    @if($event->ticket_price > 0)
        <div class="not-prose mb-6 p-4 bg-blue-50 rounded-lg">
            <div class="text-sm text-gray-600">Tiket</div>
            <div class="text-2xl font-bold">Rp {{ number_format($event->ticket_price / 100, 0, ',', '.') }}</div>
        </div>
    @endif

    <div class="not-prose mb-6">
        <a href="https://{{ $school->subdomain }}.{{ config('multitenancy.base_domain') }}/events/{{ $event->slug }}/rsvp"
           class="block text-center px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700">
            RSVP / Daftar Sekarang
        </a>
    </div>

    <h2 class="text-2xl font-semibold mt-8">Detail Acara</h2>
    <div class="text-gray-700">{!! $event->description !!}</div>

    <h2 class="text-2xl font-semibold mt-8">Lokasi</h2>
    <p>{{ $event->venue }}@if($event->city), {{ $event->city }}@endif</p>
    @if($event->venue_lat && $event->venue_lng)
        <a href="https://www.google.com/maps/search/?api=1&query={{ $event->venue_lat }},{{ $event->venue_lng }}"
           target="_blank" class="text-blue-600 hover:underline">Buka di Google Maps</a>
    @endif
</article>
@endsection

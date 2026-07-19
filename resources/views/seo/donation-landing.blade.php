@extends('seo.layout')
@section('content')
<article class="prose max-w-none">
    @if($campaign->cover_image_path)
        <img src="{{ $campaign->cover_image_path }}" alt="{{ $campaign->title }}" class="w-full rounded-lg mb-6">
    @endif

    <h1 class="text-3xl font-bold mb-2">{{ $campaign->title }}</h1>
    <p class="text-gray-600">Donasi untuk <strong>{{ $school->name }}</strong></p>

    <div class="not-prose my-6 bg-gray-50 rounded-lg p-5">
        <div class="flex justify-between text-sm mb-2">
            <span>Terkumpul</span>
            <span class="font-bold">Rp {{ number_format($campaign->raised_amount / 100, 0, ',', '.') }}</span>
        </div>
        <div class="bg-gray-200 rounded-full h-2 overflow-hidden">
            <div class="bg-green-500 h-2"
                 style="width: {{ min(100, $campaign->progressPercent()) }}%"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-500 mt-1">
            <span>{{ $campaign->progressPercent() }}% target</span>
            <span>Target: Rp {{ number_format($campaign->target_amount / 100, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="not-prose mb-6">
        <a href="https://{{ $school->subdomain }}.{{ config('multitenancy.base_domain') }}/donate/{{ $campaign->slug }}"
           class="block text-center px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700">
            Donasi Sekarang
        </a>
    </div>

    <h2 class="text-2xl font-semibold mt-8">Tentang Kampanye</h2>
    <div class="text-gray-700">{!! $campaign->description !!}</div>

    @if($campaign->updates && count($campaign->updates) > 0)
        <h2 class="text-2xl font-semibold mt-8">Update Kampanye</h2>
        @foreach($campaign->updates as $u)
            <div class="border-l-4 border-blue-500 pl-4 mb-4">
                <p class="text-xs text-gray-500">{{ $u['date'] ?? '' }}</p>
                <h3 class="font-semibold">{{ $u['title'] ?? '' }}</h3>
                <p>{{ $u['description'] ?? '' }}</p>
            </div>
        @endforeach
    @endif

    <h2 class="text-2xl font-semibold mt-8">Cara Donasi</h2>
    <ol>
        <li>Klik tombol "Donasi Sekarang" di atas</li>
        <li>Isi nominal donasi (minimal Rp 10.000)</li>
        <li>Pilih metode pembayaran (VA, QRIS, e-wallet, transfer)</li>
        <li>Selesaikan pembayaran</li>
        <li>Kuitansi pajak otomatis dikirim ke email Anda</li>
    </ol>

    <p class="text-sm text-gray-500 mt-8">
        Bagikan kampanye:
        <a href="https://wa.me/?text={{ urlencode('Mari donasi untuk ' . $campaign->title . ' di ' . url()->current()) }}" target="_blank" class="text-green-600 hover:underline">WhatsApp</a> ·
        <a href="https://twitter.com/intent/tweet?text={{ urlencode($campaign->title) }}&url={{ urlencode(url()->current()) }}" target="_blank" class="text-blue-500 hover:underline">Twitter</a>
    </p>
</article>
@endsection

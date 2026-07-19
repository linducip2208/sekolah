@extends('seo.layout')
@section('content')
<article class="prose max-w-none">
    <h1 class="text-3xl font-bold mb-4">10 Alternatif Sekolah Selain {{ $school->name }}</h1>
    <p class="text-gray-700 leading-relaxed">
        Jika Anda mencari alternatif sekolah serupa dengan {{ $school->name }}, berikut 10 sekolah lain yang patut dipertimbangkan.
        Setiap sekolah memiliki karakteristik berbeda — kurikulum, biaya SPP, fasilitas, dan kekhususan tertentu.
    </p>

    <div class="space-y-4 mt-8">
        @foreach($alternatives as $i => $s)
            <div class="border rounded-lg p-5">
                <h3 class="font-bold text-lg">{{ $i + 1 }}. {{ $s->name }}</h3>
                <p class="text-sm text-gray-600 mt-1">{{ $s->address ?? '—' }}</p>
                <div class="mt-3 flex gap-2">
                    <a href="https://{{ $s->subdomain }}.{{ config('multitenancy.base_domain') }}" class="px-3 py-1 bg-blue-600 text-white rounded text-sm">Kunjungi</a>
                    <a href="/compare/{{ $school->subdomain }}-vs-{{ $s->subdomain }}" class="px-3 py-1 border rounded text-sm">Bandingkan dengan {{ $school->name }}</a>
                </div>
            </div>
        @endforeach
    </div>
</article>
@endsection

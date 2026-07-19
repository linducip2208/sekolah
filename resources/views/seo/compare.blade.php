@extends('seo.layout')
@section('content')
<article class="prose max-w-none">
    <h1 class="text-3xl font-bold mb-6">{{ $a->name }} vs {{ $b->name }} — Perbandingan Lengkap</h1>
    <p class="text-gray-700 leading-relaxed">
        Bandingkan {{ $a->name }} dengan {{ $b->name }} secara lengkap dari sisi kurikulum, biaya, fasilitas, prestasi,
        dan ulasan untuk membantu Anda memilih sekolah terbaik untuk anak.
    </p>

    <div class="grid grid-cols-2 gap-4 mt-8 not-prose">
        @foreach([$a, $b] as $school)
            <div class="border rounded-lg p-5">
                @if($school->logo)
                    <img src="{{ $school->logo }}" alt="" class="h-12 mb-3">
                @endif
                <h2 class="text-xl font-bold">{{ $school->name }}</h2>
                <table class="w-full mt-4 text-sm">
                    <tr class="border-t"><td class="py-2 text-gray-500">Alamat</td><td class="py-2">{{ $school->address ?? '—' }}</td></tr>
                    <tr class="border-t"><td class="py-2 text-gray-500">Telepon</td><td class="py-2">{{ $school->phone ?? '—' }}</td></tr>
                    <tr class="border-t"><td class="py-2 text-gray-500">Email</td><td class="py-2">{{ $school->email ?? '—' }}</td></tr>
                    <tr class="border-t"><td class="py-2 text-gray-500">Subdomain</td><td class="py-2 font-mono">{{ $school->subdomain }}</td></tr>
                </table>
                <a href="https://{{ $school->subdomain }}.{{ config('multitenancy.base_domain') }}" class="block mt-4 text-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Kunjungi {{ $school->name }}
                </a>
            </div>
        @endforeach
    </div>

    <h2 class="text-2xl font-semibold mt-8 mb-4">Kesimpulan</h2>
    <p>Pilihan terbaik bergantung pada prioritas Anda — apakah lebih mengutamakan akademis, biaya, lokasi, atau nilai-nilai khusus seperti keagamaan. Disarankan untuk mengunjungi langsung kedua sekolah sebelum memutuskan.</p>
</article>
@endsection

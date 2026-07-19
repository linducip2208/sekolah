@extends('seo.layout')
@section('content')
<article class="prose max-w-none">
    <h1 class="text-3xl font-bold mb-4">PPDB Online {{ $city }} {{ now()->year }}</h1>
    <p class="text-gray-700 leading-relaxed">
        Informasi pendaftaran peserta didik baru (PPDB) di {{ $city }} tahun {{ now()->year }}.
        Daftar online tanpa antri, jalur zonasi, prestasi, afirmasi, dan undian.
    </p>

    @if($periods->isEmpty())
        <p class="text-gray-500 mt-8">Belum ada sekolah di {{ $city }} yang membuka PPDB saat ini.</p>
    @else
        <div class="space-y-4 mt-8">
            @foreach($periods as $p)
                <div class="border rounded-lg p-5">
                    <h3 class="font-bold text-lg">{{ $p->school->name }}</h3>
                    <div class="text-sm text-gray-600 mt-1">
                        <strong>{{ $p->name }}</strong>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        Buka: {{ $p->open_date->format('d M Y') }} — Tutup: {{ $p->close_date->format('d M Y') }}
                        @if($p->form_fee > 0)
                            <span class="ml-3">Biaya formulir: Rp {{ number_format($p->form_fee / 100, 0, ',', '.') }}</span>
                        @endif
                    </div>
                    @if($p->jalur_config)
                        <div class="mt-2 flex gap-2 flex-wrap">
                            @foreach($p->jalur_config as $jalur => $quota)
                                <span class="text-xs px-2 py-1 bg-blue-50 text-blue-800 rounded">{{ ucfirst($jalur) }}: {{ $quota }} kursi</span>
                            @endforeach
                        </div>
                    @endif
                    <a href="https://{{ $p->school->subdomain }}.{{ config('multitenancy.base_domain') }}/ppdb"
                       class="inline-block mt-3 px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                        Daftar Sekarang
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    <h2 class="text-2xl font-semibold mt-8 mb-4">Tentang PPDB Online</h2>
    <p>
        PPDB online adalah sistem pendaftaran siswa baru berbasis aplikasi yang memungkinkan calon siswa dan orang tua
        mendaftar tanpa harus datang ke sekolah. Beberapa keuntungan PPDB online:
    </p>
    <ul>
        <li>Hemat waktu dan biaya transportasi</li>
        <li>Status pendaftaran realtime</li>
        <li>Bisa upload dokumen (KK, akta, rapor) secara digital</li>
        <li>Bayar formulir online via VA / QRIS / e-wallet</li>
        <li>Pengumuman langsung via email/SMS/push notification</li>
    </ul>
</article>
@endsection

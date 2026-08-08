@extends('seo.layout')
@section('content')
<article class="prose max-w-none">
    <h1 class="text-3xl font-bold mb-4">10 Sekolah Terbaik di {{ $city }} Tahun {{ $year }}</h1>
    <p class="text-gray-700 leading-relaxed">
        Memilih sekolah yang tepat untuk anak adalah keputusan penting yang berdampak panjang terhadap
        masa depan akademik dan karakter mereka. Daftar berikut menyajikan 10 sekolah terbaik di {{ $city }}
        tahun {{ $year }} berdasarkan akreditasi BAN-SM, prestasi siswa di tingkat nasional, kualitas tenaga pengajar,
        kelengkapan fasilitas, dan ulasan parent.
    </p>

    <h2 class="text-2xl font-semibold mt-8 mb-4">Kriteria Pemeringkatan</h2>
    <ul>
        <li><strong>Akreditasi:</strong> Hanya sekolah dengan akreditasi A yang dipertimbangkan.</li>
        <li><strong>Prestasi Akademik:</strong> Olimpiade, lulusan diterima di PTN/luar negeri.</li>
        <li><strong>Fasilitas:</strong> Lab, perpustakaan, sarana olahraga.</li>
        <li><strong>Rasio Guru-Siswa:</strong> Maksimum 1:25.</li>
        <li><strong>Ulasan Parent:</strong> Berdasar feedback aplikasi Sikad Pro.</li>
    </ul>

    <h2 class="text-2xl font-semibold mt-8 mb-4">Daftar Sekolah</h2>
    <div class="space-y-4">
        @foreach($schools as $i => $s)
            <div class="border rounded-lg p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-lg">{{ $i + 1 }}. {{ $s->name }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $s->address ?? 'Alamat belum tersedia' }}</p>
                    </div>
                    <a href="https://{{ $s->subdomain }}.{{ config('multitenancy.base_domain') }}" target="_blank"
                       class="px-4 py-2 bg-blue-600 text-white rounded text-sm whitespace-nowrap hover:bg-blue-700">
                        Kunjungi
                    </a>
                </div>
                @if($s->phone || $s->email)
                    <div class="text-sm text-gray-500 mt-2">
                        @if($s->phone) <span>📞 {{ $s->phone }}</span> @endif
                        @if($s->email) <span class="ml-3">✉️ {{ $s->email }}</span> @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <h2 class="text-2xl font-semibold mt-8 mb-4">FAQ</h2>
    <div class="space-y-4">
        <div>
            <h3 class="font-semibold">Bagaimana cara mendaftar di sekolah-sekolah ini?</h3>
            <p>Sebagian besar sekolah membuka <a href="/ppdb/{{ strtolower($city) }}" class="text-blue-600 hover:underline">PPDB online di {{ $city }}</a> setiap tahun ajaran baru. Klik link sekolah untuk informasi pendaftaran lengkap.</p>
        </div>
        <div>
            <h3 class="font-semibold">Berapa kisaran biaya SPP?</h3>
            <p>Biaya SPP bervariasi dari Rp 500.000 hingga Rp 5.000.000 per bulan, tergantung sekolah dan jenjang. Detail tersedia di profil masing-masing sekolah.</p>
        </div>
        <div>
            <h3 class="font-semibold">Apakah ada beasiswa?</h3>
            <p>Ya, banyak sekolah menyediakan beasiswa untuk siswa berprestasi atau dari keluarga kurang mampu. Hubungi admin sekolah untuk informasi.</p>
        </div>
    </div>
</article>
@endsection

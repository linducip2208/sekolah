@extends('layouts.parent')
@section('title', 'e-Portfolio Saya')
@section('content')
@include('student-portal._nav')

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-7">
        <div>
            <div class="elite-kicker mb-2">Opus Meum</div>
            <h1 class="elite-h1 text-3xl ink-primary mb-2">e-Portfolio Saya</h1>
            <p class="text-sm text-gray-600 font-serif">Kumpulkan dan bagikan karya serta prestasi Anda.</p>
            <div class="elite-rule"></div>
        </div>
        <button onclick="document.getElementById('add-form').classList.toggle('hidden')" class="btn-elite-gold text-xs">
            + Tambah Karya
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm font-serif">{{ session('success') }}</div>
    @endif

    {{-- Add Form --}}
    <div id="add-form" class="hidden mb-6">
        <div class="elite-card p-6">
            <h3 class="elite-h3 text-base ink-primary mb-4">Tambah Portofolio Baru</h3>
            @if($errors->any())
                <div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('student.portfolios.store') }}" enctype="multipart/form-data" class="grid md:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tipe</label>
                    <select name="portfolio_type" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="academic">Akademik</option>
                        <option value="achievement">Prestasi</option>
                        <option value="project">Proyek</option>
                        <option value="certificate">Sertifikat</option>
                        <option value="artwork">Karya Seni</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Judul</label>
                    <input name="title" required maxlength="255" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Judul portofolio...">
                </div>
                <div class="md:col-span-2">
                    <label class="elite-kicker text-[.6rem] block mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Ceritakan tentang karya ini..."></textarea>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">File (max 20MB)</label>
                    <input type="file" name="file" class="w-full border-2 border-rule px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Thumbnail (max 5MB)</label>
                    <input type="file" name="thumbnail" class="w-full border-2 border-rule px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">URL (opsional)</label>
                    <input type="url" name="url" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="https://...">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tags (pisahkan dengan koma)</label>
                    <input name="tags" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="sains, olimpiade">
                </div>
                <div class="md:col-span-2">
                    <button class="btn-elite">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @if($portfolios->isEmpty())
        <div class="elite-card p-10 text-center">
            <p class="text-gray-500 font-serif text-lg italic">Belum ada karya. Mulai tambahkan portofolio Anda!</p>
        </div>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($portfolios as $p)
                <div class="elite-card overflow-hidden">
                    @if($p->thumbnail_path)
                        <img src="{{ asset('storage/' . $p->thumbnail_path) }}" alt="{{ $p->title }}" class="w-full h-40 object-cover">
                    @else
                        <div class="w-full h-40 bg-gray-100 flex items-center justify-center">
                            <span class="text-4xl">
                                {{ match($p->portfolio_type) { 'academic' => '📚', 'achievement' => '🏆', 'project' => '🔬', 'certificate' => '📜', 'artwork' => '🎨', default => '📁' } }}
                            </span>
                        </div>
                    @endif
                    <div class="p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-800">{{ $typeLabels[$p->portfolio_type] ?? $p->portfolio_type }}</span>
                            @if($p->approved_at)
                                <span class="text-[10px] text-green-600 font-semibold">✓ Disetujui</span>
                            @else
                                <span class="text-[10px] text-yellow-600">⏳ Review</span>
                            @endif
                        </div>
                        <h4 class="font-serif font-semibold ink-primary text-sm mb-1">{{ $p->title }}</h4>
                        <p class="text-xs text-gray-400 mb-2">{{ $p->created_at->format('d M Y') }}</p>
                        <form method="POST" action="{{ route('student.portfolios.destroy', $p) }}"
                              onsubmit="return confirm('Hapus portofolio ini?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-700 hover:underline">Hapus</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $portfolios->links() }}
        </div>
    @endif
</div>

@stop

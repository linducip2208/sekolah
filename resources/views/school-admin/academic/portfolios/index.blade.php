@extends('layouts.school-admin')
@section('title', 'e-Portfolio Siswa')
@section('sidebar')@include('school-admin.partials.sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')

<div class="mb-7 flex flex-wrap items-center justify-between gap-3">
    <div>
        <div class="elite-kicker mb-2">Opus Discipuli</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">e-Portfolio Siswa</h1>
        <div class="elite-rule"></div>
    </div>
    <button onclick="document.getElementById('add-form').classList.toggle('hidden')" class="btn-elite-gold text-xs">
        + Tambah Portofolio
    </button>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.portfolios.index') }}" class="elite-card p-4 mb-6 flex flex-wrap items-end gap-3">
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Filter Siswa</label>
        <select name="student_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">Semua Siswa</option>
            @foreach($students as $s)
                <option value="{{ $s->id }}" {{ request('student_id') == $s->id ? 'selected' : '' }}>{{ $s->user?->name }} ({{ $s->admission_no }})</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Tipe</label>
        <select name="portfolio_type" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">Semua Tipe</option>
            @foreach($typeLabels as $val => $label)
                <option value="{{ $val }}" {{ request('portfolio_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Status</label>
        <select name="approved" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">Semua</option>
            <option value="yes" {{ request('approved') == 'yes' ? 'selected' : '' }}>Disetujui</option>
            <option value="no" {{ request('approved') == 'no' ? 'selected' : '' }}>Belum Disetujui</option>
        </select>
    </div>
    <button type="submit" class="btn-elite text-xs">Filter</button>
    @if(request()->anyFilled(['student_id', 'portfolio_type', 'approved']))
        <a href="{{ route('admin.portfolios.index') }}" class="text-xs underline ink-secondary hover:ink-accent">Reset</a>
    @endif
</form>

{{-- Add Form --}}
<div id="add-form" class="hidden mb-6">
    <div class="elite-card p-6">
        <h3 class="elite-h3 text-base ink-primary mb-4">Tambah Portofolio Baru</h3>
        @if($errors->any())
            <div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('admin.portfolios.store') }}" enctype="multipart/form-data" class="grid md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Siswa</label>
                <select name="student_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="">-- Pilih Siswa --</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}">{{ $s->user?->name }} — {{ $s->admission_no }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Tipe Portofolio</label>
                <select name="portfolio_type" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="academic">Akademik</option>
                    <option value="achievement">Prestasi</option>
                    <option value="project">Proyek</option>
                    <option value="certificate">Sertifikat</option>
                    <option value="artwork">Karya Seni</option>
                    <option value="other">Lainnya</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="elite-kicker text-[.6rem] block mb-1">Judul</label>
                <input name="title" required maxlength="255" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Judul portofolio...">
            </div>
            <div class="md:col-span-2">
                <label class="elite-kicker text-[.6rem] block mb-1">Deskripsi</label>
                <textarea name="description" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Deskripsi singkat..."></textarea>
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
                <input name="tags" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="sains, olimpiade, 2024">
            </div>
            <div class="md:col-span-2">
                <button class="btn-elite">Simpan Portofolio</button>
            </div>
        </form>
    </div>
</div>

{{-- Portfolio Grid --}}
@if($portfolios->isEmpty())
    <div class="elite-card p-10 text-center">
        <p class="text-gray-500 font-serif text-lg italic">Belum ada data portofolio.</p>
    </div>
@else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 mb-6">
        @foreach($portfolios as $p)
            <div class="elite-card overflow-hidden group" x-data="{ open: false }">
                @if($p->thumbnail_path)
                    <img src="{{ asset('storage/' . $p->thumbnail_path) }}" alt="{{ $p->title }}" class="w-full h-40 object-cover">
                @else
                    <div class="w-full h-40 bg-gray-100 flex items-center justify-center">
                        @php
                            $icon = match($p->portfolio_type) {
                                'academic' => '📚', 'achievement' => '🏆', 'project' => '🔬',
                                'certificate' => '📜', 'artwork' => '🎨', default => '📁'
                            };
                        @endphp
                        <span class="text-4xl">{{ $icon }}</span>
                    </div>
                @endif
                <div class="p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ match($p->portfolio_type) { 'academic' => 'bg-blue-50 text-blue-800', 'achievement' => 'bg-yellow-50 text-yellow-800', 'project' => 'bg-indigo-50 text-indigo-800', 'certificate' => 'bg-green-50 text-green-800', 'artwork' => 'bg-pink-50 text-pink-800', default => 'bg-gray-100 text-gray-800' } }}">
                            {{ $typeLabels[$p->portfolio_type] ?? $p->portfolio_type }}
                        </span>
                        @if($p->approved_at)
                            <span class="text-[10px] text-green-600 font-semibold">✓ Disetujui</span>
                        @else
                            <span class="text-[10px] text-gray-400">Menunggu</span>
                        @endif
                    </div>
                    <h4 class="font-serif font-semibold ink-primary text-sm mb-1">{{ $p->title }}</h4>
                    <p class="text-xs text-gray-500 mb-2">{{ $p->student?->user?->name }}</p>
                    @if($p->tags)
                        <div class="flex flex-wrap gap-1 mb-2">
                            @foreach($p->tags as $tag)
                                <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif
                    <div class="flex items-center justify-between text-xs mt-2">
                        <span class="text-gray-400">{{ $p->created_at->format('d M Y') }}</span>
                        <button @click="open = !open" class="underline ink-secondary hover:ink-accent">Kelola</button>
                    </div>

                    <div x-show="open" x-cloak class="mt-3 pt-3 border-t border-rule space-y-1">
                        <div class="flex flex-wrap gap-1">
                            @if($p->approved_at)
                                <form method="POST" action="{{ route('admin.portfolios.reject', $p) }}" class="inline">
                                    @csrf
                                    <button class="text-xs text-red-700 hover:underline">Batalkan</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.portfolios.approve', $p) }}" class="inline">
                                    @csrf
                                    <button class="text-xs text-green-700 hover:underline">Setujui</button>
                                </form>
                            @endif
                            @if($p->share_token)
                                <button onclick="navigator.clipboard.writeText('{{ route('portfolio.public', $p->share_token) }}')" class="text-xs underline ink-secondary hover:ink-accent ml-2">Salin Link</button>
                            @endif
                            @if($p->file_path)
                                <a href="{{ asset('storage/' . $p->file_path) }}" target="_blank" class="text-xs underline ink-secondary hover:ink-accent ml-2">Unduh</a>
                            @endif
                            <form method="POST" action="{{ route('admin.portfolios.destroy', $p) }}" class="inline ml-2"
                                  onsubmit="return confirm('Hapus portofolio ini?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-700 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="px-0">
        {{ $portfolios->links() }}
    </div>
@endif

@stop

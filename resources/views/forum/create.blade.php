@extends('layouts.parent')
@section('title', 'Buat Topik Baru')
@section('content')

<a href="{{ route('forum.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Forum</a>

<div class="mb-8">
    <div class="elite-kicker mb-2">Novum Thema</div>
    <h1 class="elite-h1 text-4xl ink-primary mb-2">Buat Topik Diskusi</h1>
    <div class="elite-rule"></div>
</div>

<div class="max-w-2xl">
    <div class="bg-white border border-rule p-7">
        <form method="POST" action="{{ route('forum.store') }}">
            @csrf

            @if($errors->any())
                <div class="mb-5 px-5 py-3 bg-red-50 border-l-4 border-red-700">
                    <ul class="list-disc list-inside font-serif text-sm text-red-800">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-4">
                <label class="elite-kicker text-[.6rem] block mb-1">Kategori</label>
                <select name="forum_category_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('forum_category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="elite-kicker text-[.6rem] block mb-1">Judul</label>
                <input name="title" required maxlength="255" value="{{ old('title') }}"
                       class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>

            <div class="mb-4">
                <label class="elite-kicker text-[.6rem] block mb-1">Konten</label>
                <textarea name="content" rows="8" required maxlength="10000"
                          class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Tulis isi diskusi Anda...">{{ old('content') }}</textarea>
            </div>

            <div class="flex gap-3">
                <button class="btn-elite">Publikasikan Topik</button>
                <a href="{{ route('forum.index') }}" class="btn-elite-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>

@endsection

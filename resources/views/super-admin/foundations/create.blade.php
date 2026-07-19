@extends('super-admin.layout')
@section('title', 'Tambah Yayasan')
@section('content')

<div class="max-w-2xl">
    <a href="{{ route('super.foundations.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-3 inline-block">← Kembali</a>

    <div class="elite-kicker mb-2">Nova Fundatio</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Yayasan Baru</h1>
    <div class="elite-rule mb-7"></div>

    @if($errors->any())
        <div class="mb-5 px-5 py-3 bg-red-50 border-l-4 border-red-700">
            <ul class="list-disc list-inside font-serif text-sm text-red-800">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('super.foundations.store') }}" class="bg-white border border-rule p-7 space-y-5">
        @csrf
        <div>
            <label class="elite-kicker block mb-2">Nama Yayasan</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full border-2 border-rule px-4 py-3 font-serif">
        </div>
        <div>
            <label class="elite-kicker block mb-2">Slug (URL-friendly)</label>
            <input type="text" name="slug" value="{{ old('slug') }}" required pattern="[a-z0-9\-_]+"
                   class="w-full border-2 border-rule px-4 py-3 font-serif font-mono"
                   placeholder="yayasan-pendidikan-jaya">
        </div>
        <div>
            <label class="elite-kicker block mb-2">NPWP (opsional)</label>
            <input type="text" name="npwp" value="{{ old('npwp') }}" maxlength="30"
                   class="w-full border-2 border-rule px-4 py-3 font-serif font-mono">
        </div>
        <div>
            <label class="elite-kicker block mb-2">Alamat (opsional)</label>
            <textarea name="address" rows="3" class="w-full border-2 border-rule px-4 py-3 font-serif">{{ old('address') }}</textarea>
        </div>
        <button type="submit" class="btn-elite">Simpan Yayasan</button>
    </form>
</div>

@endsection

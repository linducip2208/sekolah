@extends('layouts.school-admin')
@section('title', 'Indikator Adiwiyata')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
<div class="mb-6">
    <div class="elite-kicker mb-2">Adiwiyata</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Indikator Adiwiyata</h1>
    <div class="elite-rule"></div>
</div>

<form method="GET" class="flex gap-3 mb-6 bg-white border border-rule p-4">
    <select name="category" class="border-2 border-rule px-3 py-2 text-sm">
        <option value="">— Semua Kategori —</option>
        @foreach($allCategories as $cat)
        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn-elite-ghost text-xs">Filter</button>
</form>

@foreach($categories as $category)
<div class="mb-8">
    <h2 class="elite-h2 text-xl ink-primary mb-3">{{ $category->name }}</h2>
    <div class="elite-card overflow-hidden">
        <table class="table-elite w-full text-sm">
            <thead>
                <tr>
                    <th class="w-12">Kode</th>
                    <th>Deskripsi</th>
                    <th class="w-16">Skor Max</th>
                    <th class="w-24">Bukti</th>
                    <th class="w-20"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($category->indicators as $ind)
                @php $ev = $evidences[$ind->id] ?? null; @endphp
                <tr>
                    <td class="font-mono text-xs">{{ $ind->code }}</td>
                    <td>
                        <div class="font-serif text-sm">{{ $ind->description }}</div>
                        @if($ind->evidence_hint)
                        <div class="text-[.55rem] text-gray-400 mt-1">💡 {{ $ind->evidence_hint }}</div>
                        @endif
                    </td>
                    <td class="text-center font-mono text-xs">{{ $ind->max_score }}</td>
                    <td>
                        @if($ev && in_array($ev->status, ['submitted', 'verified']))
                        <span class="text-[.6rem] uppercase px-2 py-0.5 rounded {{ $ev->status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            Skor: {{ $ev->score }} · {{ $ev->status === 'verified' ? 'OK' : 'Menunggu' }}
                        </span>
                        @else
                        <span class="text-[.55rem] text-gray-400">Belum ada</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.adiwiyata.evidence', $ind) }}" class="text-xs underline ink-accent">
                            {{ $ev ? 'Edit' : 'Upload' }}
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endforeach
@endsection

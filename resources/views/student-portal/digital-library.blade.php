@extends('layouts.school-admin')
@section('title', 'Perpustakaan Digital')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Bibliotheca Digitalis</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Perpustakaan Digital</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Buku digital yang sedang Anda pinjam. Klik "Baca" untuk membuka reader online.</p>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse($digitalIssues as $issue)
        @php $book = $issue->book; @endphp
        <div class="elite-card p-5 flex flex-col">
            <div class="flex-1">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-12 h-16 bg-[var(--c-primary)] text-white flex items-center justify-center font-bold text-lg flex-shrink-0" style="font-family:'Playfair Display',serif;">
                        {{ strtoupper(substr($book->title ?? 'B', 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="elite-h3 text-base ink-primary leading-tight">{{ $book->title ?? 'Buku Digital' }}</h3>
                        <p class="text-xs text-gray-500">{{ $book->author ?? 'Tanpa penulis' }} · {{ strtoupper($book->file_type ?? 'PDF') }}</p>
                    </div>
                </div>

                @if($issue->readingProgress)
                <div class="mb-2">
                    <div class="flex justify-between text-[.6rem] text-gray-500 mb-1">
                        <span>Halaman {{ $issue->readingProgress->current_page }}/{{ $issue->readingProgress->total_pages ?? '?' }}</span>
                        <span>{{ $issue->readingProgress->progress_percent }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 h-1.5 rounded">
                        <div class="h-1.5 rounded transition-all" style="width:{{ $issue->readingProgress->progress_percent }}%;background:var(--c-accent);"></div>
                    </div>
                </div>
                @endif

                <p class="text-[.6rem] text-gray-400 mt-2">
                    Berlaku sampai: {{ $issue->access_expires_at?->format('d M Y') ?? 'Selamanya' }}
                    · Terakhir dibaca: {{ $issue->readingProgress?->last_read_at?->diffForHumans() ?? 'Belum pernah' }}
                </p>
            </div>

            <a href="{{ route('reader.view', $issue->access_token) }}" target="_blank"
               class="btn-elite mt-3 text-xs text-center" style="padding:.5rem;">
                📖 Baca Sekarang
            </a>
        </div>
    @empty
        <div class="col-span-full p-10 text-center">
            <div class="text-4xl mb-3">📚</div>
            <p class="font-serif text-base text-gray-500 italic">Belum ada buku digital yang dipinjam.</p>
            <p class="text-xs text-gray-400 mt-1">Hubungi pustakawan untuk mendapatkan akses buku digital.</p>
        </div>
    @endforelse
</div>

@endsection

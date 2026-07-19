@extends('layouts.school-admin')
@section('title', 'Persetujuan Dokumen')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-8">
    <div class="elite-kicker mb-2">Administrasi</div>
    <h1 class="elite-h1 text-4xl ink-primary mb-1">Persetujuan Dokumen</h1>
    <p class="font-serif text-sm" style="color: var(--c-muted);">Daftar dokumen yang menunggu persetujuan untuk dipublikasikan.</p>
</div>

<div class="space-y-4">
    @forelse($approvals ?? [] as $approval)
        @php $doc = $approval->document; @endphp
        <div class="elite-card p-6" x-data="{ open: false }">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="px-2 py-0.5 text-[.6rem] font-semibold uppercase tracking-wider bg-amber-50 text-amber-700">Menunggu</span>
                        <span class="font-serif text-xs text-stone-500">v{{ $doc->version }}</span>
                        <span class="font-serif text-xs text-stone-500">{{ $doc->file_type }}</span>
                    </div>
                    <h3 class="elite-h3 text-lg ink-primary mb-2">{{ $doc->title }}</h3>
                    <p class="font-serif text-sm text-stone-600 mb-2">{{ $doc->description ?: 'Tidak ada deskripsi.' }}</p>
                    <div class="flex flex-wrap gap-3 text-[.6rem] uppercase tracking-wider text-stone-500">
                        <span>Kategori: {{ $doc->category?->name ?? '-' }}</span>
                        <span>Diunggah oleh: {{ $doc->uploader?->name ?? 'System' }}</span>
                        <span>{{ $doc->created_at->translatedFormat('d M Y H:i') }}</span>
                    </div>
                </div>
                <div class="flex gap-2 flex-shrink-0">
                    <a href="{{ route('admin.documents.download', $doc->id) }}" class="px-3 py-1.5 border border-stone-300 text-xs font-semibold uppercase tracking-wider hover:bg-stone-50 transition">
                        Unduh
                    </a>
                    <button @click="open = !open" class="btn-elite text-xs">
                        <template x-if="!open">Putuskan</template>
                        <template x-if="open">Tutup</template>
                    </button>
                </div>
            </div>

            <div x-show="open" x-cloak class="mt-4 pt-4 border-t border-stone-200">
                <form method="POST" action="{{ route('admin.documents.decide-approval', $approval->id) }}" class="flex flex-wrap items-end gap-4">
                    @csrf
                    @method('POST')
                    <div class="flex-1">
                        <label class="elite-kicker block mb-1">Catatan (opsional)</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]" placeholder="Alasan persetujuan/penolakan..."></textarea>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" name="decision" value="rejected" class="px-4 py-2 border border-red-300 text-xs font-semibold uppercase tracking-wider text-red-600 hover:bg-red-50 transition">Tolak</button>
                        <button type="submit" name="decision" value="approved" class="btn-elite text-xs">Setujui & Terbitkan</button>
                    </div>
                </form>
            </div>
        </div>
    @empty
    <div class="elite-card p-10 text-center">
        <svg class="w-12 h-12 mx-auto mb-3" style="color: var(--c-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        <p class="font-serif text-stone-500">Semua dokumen sudah diproses. Tidak ada yang menunggu persetujuan.</p>
    </div>
    @endforelse
</div>

{{ $approvals->links() ?? '' }}

@endsection

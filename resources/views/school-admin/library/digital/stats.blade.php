@extends('layouts.school-admin')
@section('title', 'Statistik Buku Digital')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="flex justify-between items-end">
        <div>
            <div class="elite-kicker mb-2">Statistica Digitalis</div>
            <h1 class="elite-h1 text-3xl ink-primary mb-2">Statistik Buku Digital</h1>
            <div class="elite-rule"></div>
        </div>
        <a href="{{ route('admin.library.digital.upload') }}" class="btn-elite-ghost">Upload Buku</a>
    </div>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="elite-card p-5 text-center">
        <div class="font-display text-4xl ink-accent">{{ $totalDigital }}</div>
        <div class="elite-kicker text-[.6rem] mt-1">Buku Digital</div>
    </div>
    <div class="elite-card p-5 text-center">
        <div class="font-display text-4xl ink-accent">{{ number_format($totalReads) }}</div>
        <div class="elite-kicker text-[.6rem] mt-1">Total Dibaca</div>
    </div>
    <div class="elite-card p-5 text-center">
        <div class="font-display text-4xl ink-accent">{{ number_format($totalDownloads) }}</div>
        <div class="elite-kicker text-[.6rem] mt-1">Total Download</div>
    </div>
    <div class="elite-card p-5 text-center">
        <div class="font-display text-4xl ink-accent">{{ $activeIssues->count() }}</div>
        <div class="elite-kicker text-[.6rem] mt-1">Akses Aktif</div>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div>
        <div class="bg-white border border-rule p-5">
            <h3 class="elite-h3 text-base ink-primary mb-4">10 Buku Terbanyak Dibaca</h3>
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Judul</th>
                        <th class="text-center px-3 py-2 elite-kicker text-[.6rem]">Dibaca</th>
                        <th class="text-center px-3 py-2 elite-kicker text-[.6rem]">Diunduh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topBooks as $b)
                        <tr class="border-t border-rule">
                            <td class="px-3 py-2 font-serif text-sm">{{ $b->title }}</td>
                            <td class="px-3 py-2 text-center font-mono text-xs">{{ $b->read_count }}</td>
                            <td class="px-3 py-2 text-center font-mono text-xs">{{ $b->download_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="p-4 text-center text-gray-500 italic">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <div class="bg-white border border-rule p-5">
            <h3 class="elite-h3 text-base ink-primary mb-4">Akses Aktif</h3>
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @forelse($activeIssues as $issue)
                    <div class="flex items-center justify-between p-3 border border-rule">
                        <div>
                            <div class="font-serif font-semibold text-sm">{{ $issue->book->title ?? '—' }}</div>
                            <div class="text-xs text-gray-500">
                                {{ $issue->student?->name ?? $issue->staff?->name ?? '—' }}
                                · Berlaku sampai {{ $issue->access_expires_at?->format('d M Y') ?? 'selamanya' }}
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.library.digital.revoke', $issue) }}" onsubmit="return confirm('Cabut akses?')">
                            @csrf
                            <button class="text-xs text-red-700 hover:underline">Cabut</button>
                        </form>
                    </div>
                @empty
                    <p class="text-gray-500 italic text-sm">Tidak ada akses aktif.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="mt-6">
    <div class="bg-white border border-rule p-5">
        <h3 class="elite-h3 text-base ink-primary mb-4">Progres Membaca Terbaru</h3>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Buku</th>
                    <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Pembaca</th>
                    <th class="text-center px-3 py-2 elite-kicker text-[.6rem]">Halaman</th>
                    <th class="text-center px-3 py-2 elite-kicker text-[.6rem]">Progres</th>
                    <th class="text-right px-3 py-2 elite-kicker text-[.6rem]">Terakhir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($progressData as $p)
                    <tr class="border-t border-rule">
                        <td class="px-3 py-2 font-serif text-sm">{{ $p->digitalBookIssue->book->title ?? '—' }}</td>
                        <td class="px-3 py-2 text-xs">{{ $p->digitalBookIssue->student?->name ?? $p->digitalBookIssue->staff?->name ?? '—' }}</td>
                        <td class="px-3 py-2 text-center font-mono text-xs">{{ $p->current_page }}/{{ $p->total_pages ?? '?' }}</td>
                        <td class="px-3 py-2 text-center">
                            <div class="w-full bg-gray-200 h-2 rounded">
                                <div class="h-2 rounded" style="width:{{ $p->progress_percent }}%;background:var(--c-accent);"></div>
                            </div>
                            <span class="text-[.6rem] text-gray-500">{{ $p->progress_percent }}%</span>
                        </td>
                        <td class="px-3 py-2 text-right text-xs text-gray-400">{{ $p->last_read_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-4 text-center text-gray-500 italic">Belum ada data progres membaca.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

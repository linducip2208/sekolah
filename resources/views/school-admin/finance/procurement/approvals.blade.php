@extends('layouts.school-admin')
@section('title', 'Persetujuan Pengadaan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-8">
    <div class="elite-kicker mb-2">Keuangan SPP &mdash; Pengadaan</div>
    <h1 class="elite-h1 text-4xl ink-primary mb-1">Persetujuan Pengadaan</h1>
    <p class="font-serif text-sm" style="color: var(--c-muted);">Daftar permintaan pengadaan yang menunggu persetujuan Anda.</p>
</div>

<div class="space-y-4">
    @forelse($approvals ?? [] as $approval)
        @php $pr = $approval->request; @endphp
        <div class="elite-card p-5" x-data="{ open: false, decision: '' }">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="font-mono text-sm font-semibold text-[var(--c-primary)]">{{ $pr->request_number }}</span>
                        <span class="px-2 py-0.5 text-[.6rem] font-semibold uppercase tracking-wider bg-blue-100 text-blue-700">{{ $pr->status }}</span>
                        <span class="text-[.6rem] uppercase tracking-wider text-stone-400">Step {{ $approval->step_order }}</span>
                    </div>
                    <h3 class="font-serif font-semibold text-lg ink-primary mb-2">{{ $pr->title }}</h3>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-stone-500 font-serif">
                        <span>Pemohon: {{ $pr->requester?->name ?? '-' }}</span>
                        <span>{{ $pr->items->count() }} item</span>
                        <span>Rp {{ number_format($pr->totalEstimated() / 100, 0, ',', '.') }}</span>
                        <span>{{ $pr->created_at->translatedFormat('d M Y') }}</span>
                    </div>
                </div>
                <button @click="open = !open" class="btn-elite text-xs flex-shrink-0">
                    <template x-if="!open">Putuskan</template>
                    <template x-if="open">Tutup</template>
                </button>
            </div>

            <div x-show="open" x-cloak class="mt-4 pt-4 border-t border-stone-200">
                <p class="font-serif text-sm text-stone-600 mb-3">{{ $pr->description ?: 'Tidak ada deskripsi.' }}</p>

                <div class="overflow-x-auto mb-4">
                    <table class="table-elite w-full text-sm">
                        <thead>
                            <tr>
                                <th class="text-left">Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Harga Satuan</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pr->items as $item)
                            <tr>
                                <td class="font-serif">{{ $item->item_name }}</td>
                                <td class="text-center">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                                <td class="text-right font-mono">Rp {{ number_format($item->estimated_unit_price / 100, 0, ',', '.') }}</td>
                                <td class="text-right font-mono">Rp {{ number_format($item->subtotalEstimated() / 100, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="{{ route('admin.procurement.decide-approval', $approval->id) }}" class="flex flex-wrap items-end gap-4">
                    @csrf
                    <div class="flex-1 min-w-[200px]">
                        <textarea name="notes" rows="2" placeholder="Catatan (opsional)..." class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]"></textarea>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" name="decision" value="rejected" class="px-4 py-2 border border-red-300 text-xs font-semibold uppercase tracking-wider text-red-600 hover:bg-red-50 transition">Tolak</button>
                        <button type="submit" name="decision" value="approved" class="btn-elite text-xs">Setujui</button>
                    </div>
                </form>
            </div>
        </div>
    @empty
    <div class="elite-card p-10 text-center">
        <svg class="w-12 h-12 mx-auto mb-3" style="color: var(--c-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        <p class="font-serif text-stone-500">Tidak ada permintaan yang menunggu persetujuan.</p>
    </div>
    @endforelse
</div>

{{ $approvals->links() ?? '' }}

@endsection

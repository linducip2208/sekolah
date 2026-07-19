@extends('super-admin.layout')
@section('title', 'Rekening Bayar')
@section('content')

<div class="mb-8">
    <div class="elite-kicker mb-2">Rationes Solutionis · Platform-Level</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Rekening Manual Platform</h1>
    <div class="elite-rule mb-3"></div>
    <p class="font-serif text-base text-gray-600 max-w-3xl">
        Rekening tujuan <strong>transfer manual</strong> dari sekolah yang berlangganan ke <strong>Anda sebagai pemilik platform</strong>.
        Tampil di halaman pembayaran publik (<code class="font-mono bg-gray-100 px-1">/daftar/{id}/pembayaran</code>) sebagai opsi selain gateway online.
    </p>
    <div class="mt-3 inline-flex items-center gap-2 text-xs px-3 py-2" style="background:rgba(184,134,11,.08); color:var(--c-secondary);">
        <span class="ink-accent">⓵</span>
        <span><strong>Beda dengan SPP:</strong> ini untuk billing langganan SaaS — bukan SPP siswa. Untuk SPP, admin sekolah set sendiri di <code class="font-mono">/admin/payment/methods</code>.</span>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Form tambah --}}
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-lg ink-primary mb-4">Tambah Rekening</h3>
            <form method="POST" action="{{ route('super.billing.accounts.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Nama Bank</label>
                    <input type="text" name="bank_name" required maxlength="100"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"
                           placeholder="BCA, BRI, Mandiri, BNI, …">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">No. Rekening</label>
                    <input type="text" name="account_number" required maxlength="50"
                           class="w-full border-2 border-rule px-3 py-2 font-mono text-sm"
                           placeholder="1234567890">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Atas Nama</label>
                    <input type="text" name="account_holder" required maxlength="200"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"
                           placeholder="PT Whitelabel Indonesia">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Urutan (opsional)</label>
                    <input type="number" name="sort_order" min="0" value="0"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan Rekening</button>
            </form>
        </div>
    </div>

    {{-- List --}}
    <div class="lg:col-span-2 space-y-3">
        @forelse($accounts as $acc)
            <div class="bg-white border border-rule p-5 flex items-center justify-between gap-4 {{ !$acc->is_active ? 'opacity-50' : '' }}">
                <div class="flex-1">
                    <div class="elite-kicker text-[.6rem] mb-1">{{ $acc->bank_name }}</div>
                    <div class="font-mono text-xl ink-primary tracking-wider">{{ $acc->account_number }}</div>
                    <div class="font-serif text-sm text-gray-600">a.n. {{ $acc->account_holder }}</div>
                </div>
                <div class="flex flex-col gap-2">
                    <form method="POST" action="{{ route('super.billing.accounts.toggle', $acc) }}">
                        @csrf
                        <button class="text-xs underline ink-secondary hover:ink-accent">
                            {{ $acc->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('super.billing.accounts.destroy', $acc) }}"
                          onsubmit="return confirm('Hapus rekening ini?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-700 hover:underline">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white border border-rule p-10 text-center text-gray-500 font-serif italic">
                Belum ada rekening pembayaran. Tambahkan minimal 1 supaya halaman pendaftaran publik bisa menampilkan instruksi transfer.
            </div>
        @endforelse
    </div>
</div>

@endsection

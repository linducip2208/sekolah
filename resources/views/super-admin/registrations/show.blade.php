@extends('super-admin.layout')
@section('title', 'Detail Pendaftaran')
@section('content')

<a href="{{ route('super.registrations.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kembali ke daftar</a>

<div class="flex justify-between items-start mb-7">
    <div>
        <div class="elite-kicker mb-2">REG-{{ str_pad($registration->id, 6, '0', STR_PAD_LEFT) }}</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">{{ $registration->school_name }}</h1>
        <div class="elite-rule"></div>
    </div>
    <span class="text-sm px-3 py-2 rounded font-semibold uppercase
        {{ $registration->status === 'pending' ? 'bg-gray-100 text-gray-700' : '' }}
        {{ $registration->status === 'verifying' ? 'bg-yellow-100 text-yellow-800' : '' }}
        {{ $registration->status === 'paid' ? 'bg-blue-100 text-blue-800' : '' }}
        {{ $registration->status === 'activated' ? 'bg-green-100 text-green-800' : '' }}
        {{ $registration->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
        {{ $registration->status }}
    </span>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Detail sekolah --}}
        <div class="bg-white border border-rule p-7">
            <h3 class="elite-h3 text-lg ink-primary mb-4">Informasi Sekolah</h3>
            <table class="w-full text-sm">
                <tr class="border-b border-rule"><td class="py-2 elite-kicker text-[.6rem] w-40">Subdomain</td><td class="py-2 font-mono">{{ $registration->subdomain }}</td></tr>
                <tr class="border-b border-rule"><td class="py-2 elite-kicker text-[.6rem]">Admin Nama</td><td class="py-2">{{ $registration->admin_name }}</td></tr>
                <tr class="border-b border-rule"><td class="py-2 elite-kicker text-[.6rem]">Admin Email</td><td class="py-2 font-mono text-xs">{{ $registration->admin_email }}</td></tr>
                <tr class="border-b border-rule"><td class="py-2 elite-kicker text-[.6rem]">Telepon</td><td class="py-2">{{ $registration->admin_phone ?? '—' }}</td></tr>
                <tr><td class="py-2 elite-kicker text-[.6rem]">Alamat</td><td class="py-2">{{ $registration->address ?? '—' }}</td></tr>
            </table>
        </div>

        {{-- Plan --}}
        <div class="bg-white border border-rule p-7">
            <h3 class="elite-h3 text-lg ink-primary mb-4">Paket Berlangganan</h3>
            <div class="flex items-baseline justify-between mb-3">
                <div>
                    <div class="font-display text-2xl ink-primary">{{ $registration->plan->name }}</div>
                    <div class="text-sm text-gray-500">{{ $registration->billing_months }} bulan</div>
                </div>
                <div class="text-right">
                    <div class="font-display text-3xl ink-primary">Rp {{ number_format($registration->plan_price/100, 0, ',', '.') }}</div>
                    <div class="elite-kicker text-[.55rem]">total bayar</div>
                </div>
            </div>
        </div>

        {{-- Pembayaran --}}
        <div class="bg-white border border-rule p-7">
            <h3 class="elite-h3 text-lg ink-primary mb-4">Bukti Pembayaran</h3>
            @if($registration->payment_proof_path)
                <table class="w-full text-sm mb-4">
                    <tr><td class="py-2 elite-kicker text-[.6rem] w-40">Metode</td><td class="py-2">{{ $registration->payment_method }}</td></tr>
                    <tr><td class="py-2 elite-kicker text-[.6rem]">Referensi</td><td class="py-2 font-mono text-xs">{{ $registration->payment_reference ?? '—' }}</td></tr>
                    <tr><td class="py-2 elite-kicker text-[.6rem]">Dibayar</td><td class="py-2">{{ $registration->paid_at?->format('d M Y H:i') }}</td></tr>
                </table>
                <a href="{{ asset('storage/'.$registration->payment_proof_path) }}" target="_blank" class="btn-elite-ghost">Lihat Bukti Transfer</a>
            @else
                <p class="font-serif text-sm text-gray-500 italic">Belum ada bukti pembayaran diupload.</p>
            @endif
        </div>

        @if($registration->rejection_reason)
            <div class="bg-red-50 border-l-4 border-red-700 p-5">
                <h4 class="elite-kicker text-[.65rem] text-red-800 mb-2">Alasan Penolakan</h4>
                <p class="font-serif text-sm text-red-900">{{ $registration->rejection_reason }}</p>
            </div>
        @endif
    </div>

    {{-- Action panel --}}
    <div class="space-y-4">
        @if($registration->status === 'activated')
            <div class="bg-green-50 border-l-4 border-green-700 p-5">
                <h4 class="elite-h3 text-base text-green-800 mb-2">✓ Sudah Aktif</h4>
                <p class="font-serif text-sm text-green-900 mb-3">Sekolah berhasil diaktivasi {{ $registration->activated_at?->diffForHumans() }}.</p>
                @if($registration->activatedSchool)
                    <a href="{{ route('super.schools.show', $registration->activatedSchool) }}" class="elite-kicker text-xs underline">Lihat Sekolah →</a>
                @endif
            </div>
        @elseif($registration->status === 'rejected')
            <div class="bg-gray-100 p-5">
                <p class="font-serif text-sm text-gray-700">Pendaftaran ini sudah ditolak.</p>
            </div>
        @else
            <div class="bg-white border border-rule p-6 deco-frame">
                <div class="elite-kicker mb-2" style="color: var(--c-accent);">Aktivasi</div>
                <h4 class="elite-h3 text-base ink-primary mb-3">Setujui & Aktifkan</h4>
                <p class="font-serif text-sm text-gray-600 mb-4">Buat akun admin sekolah & aktifkan langganan dengan plan yang dipilih.</p>

                @if($errors->any())
                    <div class="mb-3 px-3 py-2 bg-red-50 text-red-800 text-xs">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('super.registrations.approve', $registration) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="elite-kicker text-[.6rem] block mb-1">Set Password Admin Pertama</label>
                        <input type="text" name="admin_password" required minlength="8"
                               class="w-full border-2 border-rule px-3 py-2 font-mono text-sm"
                               placeholder="min. 8 karakter">
                    </div>
                    <button type="submit" class="btn-elite w-full" style="padding:.7rem;font-size:.65rem;">
                        Aktifkan Sekolah
                    </button>
                </form>
            </div>

            <details class="bg-white border border-rule p-5">
                <summary class="elite-kicker cursor-pointer">Tolak Pendaftaran</summary>
                <form method="POST" action="{{ route('super.registrations.reject', $registration) }}" class="mt-4 space-y-3">
                    @csrf
                    <textarea name="rejection_reason" rows="3" required maxlength="1000"
                              class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"
                              placeholder="Alasan penolakan…"></textarea>
                    <button type="submit" class="text-xs px-3 py-2 bg-red-50 text-red-800 hover:bg-red-100 w-full">
                        Tolak
                    </button>
                </form>
            </details>
        @endif
    </div>
</div>

@endsection

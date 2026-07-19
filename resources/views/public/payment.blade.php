@extends('elite.layout')
@section('title', 'Instruksi Pembayaran')

@section('header')@include('elite.partials.header')@endsection

@section('content')

<section class="paper">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-16">
        <div class="elite-kicker mb-2">Solutio Manualis</div>
        <h1 class="elite-h1 text-4xl ink-primary mb-3">Instruksi Pembayaran</h1>
        <div class="elite-rule mb-7"></div>

        @if(session('success'))
            <div class="mb-5 px-5 py-3 bg-white border-l-4" style="border-color: var(--c-accent);">
                <p class="font-serif text-base ink-primary">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Order summary --}}
        <div class="bg-white border border-rule p-7 mb-6">
            <h2 class="elite-h3 text-lg ink-primary mb-4">Ringkasan Pesanan</h2>
            <table class="w-full text-sm">
                <tr class="border-b border-rule">
                    <td class="py-2 elite-kicker text-[.6rem]">Sekolah</td>
                    <td class="py-2 text-right font-serif">{{ $registration->school_name }}</td>
                </tr>
                <tr class="border-b border-rule">
                    <td class="py-2 elite-kicker text-[.6rem]">Subdomain</td>
                    <td class="py-2 text-right font-mono text-xs">{{ $registration->subdomain }}</td>
                </tr>
                <tr class="border-b border-rule">
                    <td class="py-2 elite-kicker text-[.6rem]">Paket</td>
                    <td class="py-2 text-right font-serif">{{ $registration->plan->name }} × {{ $registration->billing_months }} bulan</td>
                </tr>
                <tr class="bg-[rgba(184,134,11,.06)]">
                    <td class="py-3 px-3 elite-kicker">Total Bayar</td>
                    <td class="py-3 px-3 text-right font-display text-2xl ink-primary">Rp {{ number_format($registration->plan_price / 100, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        {{-- Bank accounts --}}
        <div class="bg-white border border-rule p-7 mb-6">
            <h2 class="elite-h3 text-lg ink-primary mb-1">Transfer ke salah satu rekening berikut:</h2>
            <div class="elite-rule mb-5"></div>

            @forelse($billingAccounts as $acc)
                <div class="border border-rule p-5 mb-3 last:mb-0 flex items-center justify-between gap-5">
                    <div>
                        <div class="elite-kicker text-[.6rem] mb-1">{{ $acc->bank_name }}</div>
                        <div class="font-mono text-2xl ink-primary tracking-wider">{{ $acc->account_number }}</div>
                        <div class="font-serif text-sm text-gray-600 mt-1">a.n. {{ $acc->account_holder }}</div>
                    </div>
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $acc->account_number }}'); this.innerText='Tersalin ✓'"
                            class="elite-kicker text-xs px-3 py-2 border border-rule hover:bg-gray-50">Salin No. Rek</button>
                </div>
            @empty
                <p class="font-serif text-base text-gray-600 italic">Daftar rekening pembayaran belum tersedia. Hubungi admin platform via WhatsApp.</p>
            @endforelse

            <div class="mt-5 p-4" style="background:rgba(184,134,11,.06); border-left: 3px solid var(--c-accent);">
                <div class="elite-kicker mb-2" style="color: var(--c-accent);">Petunjuk</div>
                <ol class="list-decimal list-inside font-serif text-sm text-gray-700 space-y-1">
                    <li>Transfer <strong>tepat sebesar</strong> Rp {{ number_format($registration->plan_price / 100, 0, ',', '.') }} (jangan dibulatkan)</li>
                    <li>Simpan bukti transfer (foto/screenshot/PDF)</li>
                    <li>Upload bukti via form di bawah</li>
                    <li>Tim platform verifikasi (1×24 jam) lalu sekolah Anda diaktifkan</li>
                </ol>
            </div>
        </div>

        {{-- Upload proof --}}
        @if(in_array($registration->status, ['pending', 'verifying']))
            <div class="bg-white border border-rule p-7">
                <h2 class="elite-h3 text-lg ink-primary mb-1">Upload Bukti Pembayaran</h2>
                <div class="elite-rule mb-5"></div>

                @if($errors->any())
                    <div class="mb-5 px-5 py-3 bg-red-50 border-l-4 border-red-700">
                        <ul class="list-disc list-inside font-serif text-sm text-red-800">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('public.subscription.upload', $registration) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="elite-kicker block mb-2">Metode Pembayaran</label>
                        <select name="payment_method" required class="w-full border-2 border-rule px-4 py-3 font-serif">
                            <option value="bank_transfer">Transfer Bank</option>
                            <option value="qris">QRIS</option>
                            <option value="ewallet">E-Wallet</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="elite-kicker block mb-2">No. Referensi / ID Transaksi (opsional)</label>
                        <input type="text" name="payment_reference" maxlength="100"
                               class="w-full border-2 border-rule px-4 py-3 font-mono">
                    </div>
                    <div>
                        <label class="elite-kicker block mb-2">File Bukti (JPG/PNG/PDF, maks 5MB)</label>
                        <input type="file" name="payment_proof" required accept=".jpg,.jpeg,.png,.pdf"
                               class="w-full border-2 border-rule px-4 py-3 font-serif">
                    </div>
                    <button type="submit" class="btn-elite w-full" style="padding: 1rem;">Upload & Submit</button>
                </form>
            </div>
        @else
            <div class="bg-blue-50 border-l-4 border-blue-700 p-5">
                <p class="font-serif text-base text-blue-900">Status saat ini: <strong>{{ ucfirst($registration->status) }}</strong>. Anda akan dihubungi via email saat sekolah aktif.</p>
            </div>
        @endif
    </div>
</section>

@endsection

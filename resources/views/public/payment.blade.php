@extends('landing.layout')
@section('title', 'Instruksi Pembayaran')
@section('description', 'Instruksi pembayaran untuk aktivasi sekolah Anda di Sikad Pro.')

@section('content')
<section class="lp-section" style="background: var(--lp-bg);">
    <div class="lp-container max-w-3xl">
        <div class="mb-8">
            <p class="lp-kicker mb-3">Pembayaran</p>
            <h1 class="lp-title text-4xl sm:text-5xl">Instruksi Pembayaran</h1>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-lg" style="background: var(--lp-accent-soft); border: 1px solid var(--lp-accent); color: var(--lp-ink);">{{ session('success') }}</div>
        @endif

        <div class="lp-card lp-card-shadow p-7 mb-6">
            <h2 class="font-display font-bold text-lg mb-4" style="color: var(--lp-ink);">Ringkasan Pesanan</h2>
            <dl class="text-sm space-y-2.5">
                <div class="flex justify-between py-2 border-b" style="border-color: var(--lp-border);">
                    <dt style="color: var(--lp-muted);">Sekolah</dt>
                    <dd class="font-medium" style="color: var(--lp-ink);">{{ $registration->school_name }}</dd>
                </div>
                <div class="flex justify-between py-2 border-b" style="border-color: var(--lp-border);">
                    <dt style="color: var(--lp-muted);">Subdomain</dt>
                    <dd class="font-mono text-xs" style="color: var(--lp-ink);">{{ $registration->subdomain }}</dd>
                </div>
                <div class="flex justify-between py-2 border-b" style="border-color: var(--lp-border);">
                    <dt style="color: var(--lp-muted);">Paket</dt>
                    <dd style="color: var(--lp-ink);">{{ $registration->plan->name }} × {{ $registration->billing_months }} bulan</dd>
                </div>
                <div class="flex justify-between py-3 px-3 rounded-lg" style="background: var(--lp-accent-soft);">
                    <dt class="font-semibold" style="color: var(--lp-ink);">Total Bayar</dt>
                    <dd class="font-display text-2xl font-bold" style="color: var(--lp-primary);">Rp {{ number_format($registration->plan_price / 100, 0, ',', '.') }}</dd>
                </div>
            </dl>
        </div>

        <div class="lp-card lp-card-shadow p-7 mb-6">
            <h2 class="font-display font-bold text-lg mb-4" style="color: var(--lp-ink);">Transfer ke salah satu rekening berikut:</h2>

            @forelse($billingAccounts as $acc)
                <div class="flex items-center justify-between gap-5 p-5 mb-3 last:mb-0 rounded-lg border" style="border-color: var(--lp-border);">
                    <div>
                        <div class="text-xs uppercase tracking-wide mb-1" style="color: var(--lp-muted);">{{ $acc->bank_name }}</div>
                        <div class="font-mono text-2xl tracking-wider font-semibold" style="color: var(--lp-ink);">{{ $acc->account_number }}</div>
                        <div class="text-sm mt-1" style="color: var(--lp-muted);">a.n. {{ $acc->account_holder }}</div>
                    </div>
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $acc->account_number }}'); this.innerText='Tersalin ✓'" class="lp-btn lp-btn-secondary" style="min-height: 38px; padding: .4rem .9rem; font-size: .8125rem;">Salin No. Rek</button>
                </div>
            @empty
                <p class="text-sm italic" style="color: var(--lp-muted);">Daftar rekening pembayaran belum tersedia. Hubungi admin platform via WhatsApp.</p>
            @endforelse

            <div class="mt-5 p-4 rounded-lg" style="background: var(--lp-accent-soft); border-left: 3px solid var(--lp-accent);">
                <div class="text-xs uppercase tracking-wide font-semibold mb-2" style="color: var(--lp-primary);">Petunjuk</div>
                <ol class="list-decimal list-inside text-sm space-y-1" style="color: var(--lp-muted);">
                    <li>Transfer <strong>tepat sebesar</strong> Rp {{ number_format($registration->plan_price / 100, 0, ',', '.') }} (jangan dibulatkan)</li>
                    <li>Simpan bukti transfer (foto/screenshot/PDF)</li>
                    <li>Upload bukti via form di bawah</li>
                    <li>Tim platform verifikasi (1×24 jam) lalu sekolah Anda diaktifkan</li>
                </ol>
            </div>
        </div>

        @if(in_array($registration->status, ['pending', 'verifying']))
            <div class="lp-card lp-card-shadow p-7">
                <h2 class="font-display font-bold text-lg mb-4" style="color: var(--lp-ink);">Upload Bukti Pembayaran</h2>

                @if(isset($errors) && $errors->any())
                    <div class="mb-5 p-4 rounded-lg" style="background: var(--lp-accent-soft); border: 1px solid #fca5a5; color: #b91c1c;">
                        <ul class="list-disc list-inside text-sm">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('public.subscription.upload', $registration) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="label" for="payment_method">Metode Pembayaran</label>
                        <select id="payment_method" name="payment_method" required class="select">
                            <option value="bank_transfer">Transfer Bank</option>
                            <option value="qris">QRIS</option>
                            <option value="ewallet">E-Wallet</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="label" for="payment_reference">No. Referensi / ID Transaksi (opsional)</label>
                        <input type="text" id="payment_reference" name="payment_reference" maxlength="100" class="input font-mono">
                    </div>
                    <div>
                        <label class="label" for="payment_proof">File Bukti (JPG/PNG/PDF, maks 5MB)</label>
                        <input type="file" id="payment_proof" name="payment_proof" required accept=".jpg,.jpeg,.png,.pdf" class="input">
                    </div>
                    <button type="submit" class="lp-btn w-full">Upload &amp; Submit</button>
                </form>
            </div>
        @else
            <div class="p-5 rounded-lg" style="background: var(--lp-accent-soft); border-left: 3px solid var(--lp-accent);">
                <p class="text-sm" style="color: var(--lp-ink);">Status saat ini: <strong>{{ ucfirst($registration->status) }}</strong>. Anda akan dihubungi via email saat sekolah aktif.</p>
            </div>
        @endif
    </div>
</section>
@endsection

@extends('layouts.parent')
@section('title', 'Status Pembayaran')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
@endpush

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-xl mx-auto">
    <div class="text-center mb-5">
        @if($tx->status === 'paid')
            <div class="inline-block px-4 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">Berhasil dibayar</div>
            <div class="text-3xl mt-2">✅</div>
        @elseif($tx->status === 'awaiting_payment')
            <div class="inline-block px-4 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">Menunggu pembayaran</div>
        @elseif($tx->status === 'expired')
            <div class="inline-block px-4 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">Kedaluwarsa</div>
        @elseif($tx->status === 'failed')
            <div class="inline-block px-4 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">Gagal</div>
        @else
            <div class="inline-block px-4 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">{{ $tx->status }}</div>
        @endif
        <div class="text-2xl font-bold mt-3">Rp {{ number_format($tx->amount / 100, 0, ',', '.') }}</div>
        <div class="text-xs text-gray-500 mt-1 font-mono">{{ $tx->reference_no }}</div>
    </div>

    @if($tx->status === 'awaiting_payment')
        @if($tx->redirect_url)
            <a href="{{ $tx->redirect_url }}" class="block btn-brand text-center mb-3">Lanjutkan ke Halaman Pembayaran →</a>
        @endif

        @if($tx->va_number)
            <div class="border rounded p-4 mb-3">
                <div class="text-xs text-gray-500">Virtual Account {{ $tx->va_bank_code }}</div>
                <div class="text-2xl font-mono font-bold tracking-wider">{{ $tx->va_number }}</div>
                <div class="text-xs text-gray-500 mt-2">
                    Transfer ke nomor di atas dari mobile/internet banking. Sistem otomatis update setelah pembayaran diterima.
                </div>
            </div>
        @endif

        @if($tx->qr_string)
            <div class="border rounded p-4 mb-3 text-center">
                <div class="text-xs text-gray-500 mb-2">Scan QRIS dari aplikasi pembayaran Anda</div>
                <div id="qrbox" class="inline-block"></div>
                <script>
                    new QRCode(document.getElementById('qrbox'), {
                        text: @json($tx->qr_string),
                        width: 240,
                        height: 240,
                    });
                </script>
            </div>
        @endif

        @if($tx->deeplink_url)
            <a href="{{ $tx->deeplink_url }}" class="block btn-brand text-center mb-3">Buka di Aplikasi E-Wallet →</a>
        @endif

        @if(!empty($tx->raw_response['bank_accounts']))
            <div class="border rounded p-4 mb-3">
                <div class="text-sm font-medium mb-2">Transfer ke salah satu rekening berikut:</div>
                @foreach($tx->raw_response['bank_accounts'] as $acc)
                    <div class="mb-2 last:mb-0">
                        <div class="text-xs text-gray-500">{{ $acc['bank_name'] ?? '' }}</div>
                        <div class="font-mono font-bold">{{ $acc['account_number'] ?? '' }}</div>
                        <div class="text-xs">{{ $acc['account_holder'] ?? '' }}</div>
                    </div>
                @endforeach
                @if(!empty($tx->raw_response['instructions']))
                    <div class="text-xs text-gray-600 mt-3">{{ $tx->raw_response['instructions'] }}</div>
                @endif
            </div>
        @endif

        @if($tx->expired_at)
            <div class="text-xs text-gray-500 text-center mb-3">
                Kedaluwarsa: {{ $tx->expired_at->format('d M Y H:i') }}
            </div>
        @endif

        <form method="POST" action="{{ route('portal.payments.cancel', $tx->reference_no) }}" onsubmit="return confirm('Batalkan transaksi ini?')">
            @csrf
            <button class="w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded">Batalkan</button>
        </form>

        <script>
            // poll every 5s
            setInterval(() => {
                fetch(location.href, { headers: { 'Accept': 'text/html' } })
                    .then(r => r.text())
                    .then(html => {
                        if (!html.includes('Menunggu pembayaran')) location.reload();
                    });
            }, 5000);
        </script>
    @endif

    @if($tx->status === 'paid')
        <div class="text-center text-sm text-gray-600 mt-3">
            Pembayaran diterima pada {{ optional($tx->paid_at)->format('d M Y H:i') }}.
        </div>
    @endif

    <div class="text-center mt-5">
        <a href="{{ route('portal.invoices') }}" class="text-sm text-brand-primary hover:underline">← Kembali ke daftar tagihan</a>
    </div>
</div>
@endsection

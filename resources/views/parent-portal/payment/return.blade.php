@extends('layouts.parent')
@section('title', 'Kembali dari Pembayaran')
@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-md mx-auto text-center">
    @if($tx)
        <div class="text-3xl mb-2">⏳</div>
        <div class="font-bold mb-1">Menunggu konfirmasi pembayaran...</div>
        <div class="text-sm text-gray-600">
            Mohon tunggu sebentar. Status pembayaran akan otomatis terupdate.
        </div>
        <div class="mt-5">
            <a href="{{ route('portal.payments.show', $tx->reference_no) }}" class="btn-brand inline-block">Lihat Status</a>
        </div>
    @else
        <div class="font-bold">Tidak ada transaksi ditemukan.</div>
        <a href="{{ route('portal.invoices') }}" class="text-sm text-brand-primary hover:underline mt-3 inline-block">Kembali ke tagihan</a>
    @endif
</div>
@endsection

@extends('layouts.parent')
@section('title', 'Pilih Metode Pembayaran')
@section('content')
<div class="bg-white rounded-lg shadow p-5 mb-4">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-xs text-gray-500">Invoice</div>
            <div class="font-mono">{{ $invoice->invoice_no }}</div>
        </div>
        <div class="text-right">
            <div class="text-xs text-gray-500">Sisa Tagihan</div>
            <div class="text-2xl font-bold">Rp {{ number_format(($invoice->amount - $invoice->discount - $invoice->paid_amount) / 100, 0, ',', '.') }}</div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="px-4 py-3 border-b">
        <h2 class="font-bold">Pilih Metode Pembayaran</h2>
    </div>
    @if($methods->isEmpty())
        <div class="p-12 text-center text-gray-500">
            Belum ada metode pembayaran aktif. Hubungi admin sekolah.
        </div>
    @else
        <form method="POST" action="{{ route('portal.invoices.initiate', $invoice->id) }}" class="divide-y">
            @csrf
            @foreach($methods as $m)
                <label class="flex items-start gap-3 p-4 cursor-pointer hover:bg-gray-50">
                    <input type="radio" name="payment_method_id" value="{{ $m->id }}" required class="mt-1.5">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            @if($m->logo_url)
                                <img src="{{ $m->logo_url }}" class="h-6">
                            @endif
                            <div class="font-medium">{{ $m->display_name }}</div>
                            <div class="text-xs text-gray-500 ml-auto">
                                @if($m->fee_flat || $m->fee_percent_bp)
                                    Biaya admin
                                    @if($m->fee_flat)Rp {{ number_format($m->fee_flat / 100, 0, ',', '.') }}@endif
                                    @if($m->fee_percent_bp) + {{ $m->fee_percent_bp / 100 }}%@endif
                                    @if($m->feeBorneByParent()) (ditanggung Anda)@else (ditanggung sekolah)@endif
                                @endif
                            </div>
                        </div>
                        @if($m->instruction_template)
                            <div class="text-xs text-gray-600 mt-1">{{ $m->instruction_template }}</div>
                        @endif
                    </div>
                </label>
            @endforeach
            <div class="p-4">
                <button type="submit" class="w-full btn-brand">Lanjut Bayar</button>
            </div>
        </form>
    @endif
</div>
@endsection

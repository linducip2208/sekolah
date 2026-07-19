<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $tx->reference_no }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid {{ $branding->color_primary ?? '#2563EB' }}; padding-bottom: 12px; margin-bottom: 16px; }
        .header h1 { margin: 4px 0; font-size: 18px; }
        .school-name { font-size: 14px; font-weight: bold; }
        .info-row { display: flex; padding: 4px 0; border-bottom: 1px dotted #ccc; }
        .info-label { width: 40%; color: #666; }
        .info-value { font-weight: bold; }
        .amount-box { background: #f0f9ff; border: 1px solid {{ $branding->color_primary ?? '#2563EB' }}; padding: 12px; margin: 12px 0; text-align: center; }
        .amount-box .amount { font-size: 24px; font-weight: bold; color: {{ $branding->color_primary ?? '#2563EB' }}; }
        .footer { margin-top: 24px; text-align: center; font-size: 9px; color: #666; }
        .stamp { border: 2px solid green; color: green; padding: 4px 8px; display: inline-block; transform: rotate(-5deg); font-weight: bold; margin: 8px 0; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">{{ $school->name ?? config('app.name') }}</div>
        @if($school->address)<div>{{ $school->address }}</div>@endif
        <h1>RECEIPT PEMBAYARAN</h1>
    </div>

    <div>
        <div class="info-row"><span class="info-label">No. Referensi</span><span class="info-value">{{ $tx->reference_no }}</span></div>
        <div class="info-row"><span class="info-label">No. Invoice</span><span class="info-value">{{ $invoice?->invoice_no ?? '—' }}</span></div>
        <div class="info-row"><span class="info-label">Periode</span><span class="info-value">{{ $invoice?->period ?? '—' }}</span></div>
        <div class="info-row"><span class="info-label">Tanggal Bayar</span><span class="info-value">{{ optional($tx->paid_at)->format('d F Y H:i') ?? '—' }}</span></div>
        <div class="info-row"><span class="info-label">Metode</span><span class="info-value">{{ $method?->display_name ?? '—' }}</span></div>
    </div>

    <div class="amount-box">
        <div>Total Dibayar</div>
        <div class="amount">Rp {{ number_format($tx->amount / 100, 0, ',', '.') }}</div>
    </div>

    @if($tx->status === 'paid')
        <div style="text-align: center;">
            <span class="stamp">LUNAS</span>
        </div>
    @endif

    <div class="footer">
        Receipt ini sah tanpa tanda tangan dan stempel basah.<br>
        Dicetak otomatis pada {{ now()->format('d M Y H:i') }}
    </div>
</body>
</html>

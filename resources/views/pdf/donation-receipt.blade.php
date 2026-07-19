<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kuitansi Donasi {{ $donation->receipt_no }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; padding-bottom: 12px; margin-bottom: 16px; border-bottom: 2px solid {{ $branding->color_primary ?? '#16A34A' }}; }
        .header h1 { margin: 4px 0; font-size: 18px; }
        .school-name { font-size: 14px; font-weight: bold; }
        .info-row { display: flex; padding: 4px 0; border-bottom: 1px dotted #ccc; }
        .info-label { width: 40%; color: #666; }
        .info-value { font-weight: bold; }
        .amount-box { background: #f0fdf4; border: 1px solid {{ $branding->color_primary ?? '#16A34A' }}; padding: 12px; margin: 12px 0; text-align: center; }
        .amount-box .amount { font-size: 24px; font-weight: bold; color: {{ $branding->color_primary ?? '#16A34A' }}; }
        .footer { margin-top: 24px; text-align: center; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">{{ $school->name ?? config('app.name') }}</div>
        <h1>KUITANSI DONASI</h1>
    </div>

    <div>
        <div class="info-row"><span class="info-label">No. Kuitansi</span><span class="info-value">{{ $donation->receipt_no }}</span></div>
        <div class="info-row"><span class="info-label">Tanggal</span><span class="info-value">{{ optional($donation->donated_at)->format('d F Y') }}</span></div>
        <div class="info-row"><span class="info-label">Donatur</span><span class="info-value">{{ $donation->is_anonymous ? '(Anonim)' : $donation->donor_name }}</span></div>
        @if($donation->npwp)
            <div class="info-row"><span class="info-label">NPWP</span><span class="info-value">{{ $donation->npwp }}</span></div>
        @endif
        <div class="info-row"><span class="info-label">Kampanye</span><span class="info-value">{{ $campaign?->title ?? 'Donasi Umum' }}</span></div>
    </div>

    <div class="amount-box">
        <div>Jumlah Donasi</div>
        <div class="amount">Rp {{ number_format($donation->amount / 100, 0, ',', '.') }}</div>
    </div>

    <p>
        Telah diterima dari <strong>{{ $donation->is_anonymous ? '(Anonim)' : $donation->donor_name }}</strong>,
        donasi sebesar <strong>Rp {{ number_format($donation->amount / 100, 0, ',', '.') }}</strong>
        ({{ ucfirst(\App\Helpers\NumberToWords::toIndonesian(intval($donation->amount / 100))) }} rupiah)
        untuk kampanye "<em>{{ $campaign?->title ?? 'Donasi Umum' }}</em>".
    </p>

    <div class="footer">
        Kuitansi ini dapat dipakai sebagai bukti potongan pajak (PPh 21).<br>
        {{ $school->name }} · Dicetak {{ now()->format('d M Y H:i') }}
    </div>
</body>
</html>

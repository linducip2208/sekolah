<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label QR — {{ $asset->name }}</title>
    <style>
        body { font-family: sans-serif; margin: 0; padding: 40px; text-align: center; }
        .label { border: 2px solid #000; padding: 20px; display: inline-block; width: 280px; }
        .label h3 { margin: 0 0 10px; font-size: 14px; }
        .label p { margin: 5px 0; font-size: 11px; color: #555; }
        .qr { width: 160px; height: 160px; }
        .code { font-family: monospace; font-size: 12px; margin-top: 10px; }
        @media print { body { margin: 0; padding: 20px; } }
    </style>
</head>
<body>
    <div class="label">
        <h3>{{ $asset->name }}</h3>
        <p>Kode: {{ $asset->asset_code ?? '—' }}</p>
        <p>{{ $asset->category?->name ?? '' }}</p>
        <img src="{{ $qrData }}" alt="QR Code" class="qr">
        <div class="code">{{ $asset->qr_code }}</div>
        <p style="font-size:9px;color:#999;">eSchool SaaS · {{ now()->format('d/m/Y') }}</p>
    </div>
</body>
</html>

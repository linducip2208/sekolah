<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ringkasan Akreditasi — {{ $school->name ?? 'Sekolah' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|inter:300,400,500,600,700|cormorant:500,600,700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Inter', sans-serif; padding: 2cm; color: #1a1a1a; font-size: 11pt; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 3px solid #0b1d3a; padding-bottom: 16px; }
        .header h1 { font-family: 'Playfair Display', serif; font-size: 18pt; color: #0b1d3a; }
        .header .meta { font-size: 9pt; color: #666; margin-top: 4px; }
        .score-box { text-align: center; margin: 20px 0; padding: 16px; border: 2px solid #b8860b; max-width: 300px; margin-left: auto; margin-right: auto; }
        .score-box .value { font-family: 'Playfair Display', serif; font-size: 28pt; font-weight: 700; color: #b8860b; }
        .score-box .label { font-size: 8pt; text-transform: uppercase; letter-spacing: .2em; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        thead th { background: #0b1d3a; color: #fff; padding: 8px 10px; font-size: 7pt; text-transform: uppercase; letter-spacing: .15em; text-align: left; }
        tbody td { padding: 6px 10px; border-bottom: 1px solid #ddd; font-size: 9pt; }
        .std-header td { background: #f5f3ed; font-weight: 700; font-family: 'Playfair Display', serif; font-size: 10pt; color: #0b1d3a; }
        @media print { body { padding: 1.5cm; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>Ringkasan Penilaian Akreditasi</h1>
        <div class="meta">{{ $school->name ?? 'Sekolah' }} — BAN-S/M IASP 2020</div>
        <div class="meta">Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</div>
    </div>

    <div class="score-box">
        <div class="value">{{ number_format($totalPredicted, 1) }}</div>
        <div class="label">Prediksi Nilai Akhir</div>
        <div style="font-family:'Playfair Display',serif; font-size:14pt; font-weight:700; color:{{ $grade['color'] }}; margin-top:4px;">
            {{ $grade['grade'] }} — {{ $grade['label'] }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Instrumen</th>
                <th>Deskripsi</th>
                <th>Nilai Mandiri</th>
                <th>Nilai Asesor</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @php $currentStd = null; @endphp
            @foreach($rows as $row)
                @if($currentStd !== $row['standard']->code)
                    <tr class="std-header">
                        <td colspan="6">Standar {{ $row['standard']->code }}: {{ $row['standard']->name }} (Bobot {{ $row['standard']->weight_percent }}%)</td>
                    </tr>
                    @php $currentStd = $row['standard']->code; @endphp
                @endif
                <tr>
                    <td>{{ $row['instrument']->number }}</td>
                    <td>{{ $row['instrument']->description }}</td>
                    <td style="font-size:8pt;color:#888;">{{ $row['instrument']->evidence_hint ?? '-' }}</td>
                    <td style="text-align:center;">{{ $row['self_score'] !== null ? $row['self_score'] : '-' }}</td>
                    <td style="text-align:center;">{{ $row['actual_score'] !== null ? $row['actual_score'] : '-' }}</td>
                    <td style="font-size:8pt;">{{ $row['notes'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="text-align:right; font-size:8pt; color:#999; margin-top:12px;">
        Dicetak dari Sikad Pro — {{ config('app.url') }}
    </div>
    <script>window.print();</script>
</body>
</html>

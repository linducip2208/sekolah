<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan PDF</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; font-size: 11px; color: #1e293b; margin: 0; padding: 20px; }
        h1 { font-size: 18px; font-weight: 700; margin-bottom: 4px; color: #0b1d3a; }
        .subtitle { font-size: 10px; color: #64748b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 9px; }
        th { background: #0b1d3a; color: white; padding: 6px 8px; text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) { background: #f8fafc; }
        .header-bar { border-bottom: 3px solid #b8860b; padding-bottom: 12px; margin-bottom: 16px; }
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="header-bar">
    <h1>{{ $title }}</h1>
    <div class="subtitle">{{ $schoolName }} &mdash; Digenerate {{ $generatedAt }}</div>
</div>

<table>
    <thead>
        <tr>
            @foreach($columns as $field => $label)
                <th>{{ $label }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
            <tr>
                @foreach($columns as $field => $label)
                    <td>{{ data_get($row, $field, '-') }}</td>
                @endforeach
            </tr>
        @empty
            <tr><td colspan="{{ count($columns) }}" style="text-align:center;padding:20px;">Tidak ada data.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">Dokumen ini digenerate otomatis oleh Sikad Pro Report Builder.</div>
</body>
</html>

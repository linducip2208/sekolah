<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan SHU {{ $tahun }}</title>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12px; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { font-size: 16px; margin-bottom: 5px; }
        .header p { font-size: 11px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table th, table td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background: #f5f5f5; font-weight: bold; }
        .text-right { text-align: right; }
        .total { font-weight: bold; font-size: 14px; }
        .footer { margin-top: 40px; font-size: 10px; color: #888; text-align: center; }
        @media print { body { margin: 20px; } }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN SISA HASIL USAHA (SHU)</h2>
        <p>Koperasi Sekolah — Tahun Buku {{ $tahun }}</p>
        <p>Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:70%">Uraian</th>
                <th class="text-right">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Pinjaman Disalurkan</td>
                <td class="text-right">{{ number_format($shu['total_loans'] / 100, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Pendapatan Bunga Pinjaman</td>
                <td class="text-right">{{ number_format($shu['total_interest'] / 100, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Simpanan Anggota</td>
                <td class="text-right">{{ number_format($shu['total_savings'] / 100, 0, ',', '.') }}</td>
            </tr>
            <tr class="total">
                <td><strong>Surplus Kotor (SHU)</strong></td>
                <td class="text-right"><strong>{{ number_format($shu['gross_surplus'] / 100, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width:70%">Pembagian SHU</th>
                <th class="text-right">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Dana Cadangan Koperasi (25%)</td>
                <td class="text-right">{{ number_format($shu['reserve'] / 100, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Bagian Anggota (75%)</td>
                <td class="text-right">{{ number_format($shu['member_share'] / 100, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Jumlah Anggota Aktif</td>
                <td class="text-right">{{ $shu['member_count'] }} orang</td>
            </tr>
            @if($shu['member_count'] > 0)
            <tr class="total">
                <td><strong>Estimasi SHU per Anggota</strong></td>
                <td class="text-right"><strong>{{ number_format(($shu['member_share'] / $shu['member_count']) / 100, 0, ',', '.') }}</strong></td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini digenerate secara otomatis oleh sistem eSchool SaaS.</p>
        <p>&copy; {{ date('Y') }} eSchool — Laporan SHU Koperasi</p>
    </div>
</body>
</html>

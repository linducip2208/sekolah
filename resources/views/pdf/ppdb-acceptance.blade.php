<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Surat Penerimaan — {{ $app->student_name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; line-height: 1.6; }
        .header { text-align: center; padding-bottom: 16px; margin-bottom: 24px; border-bottom: 2px solid {{ $branding->color_primary ?? '#2563EB' }}; }
        .school-name { font-size: 18px; font-weight: bold; color: {{ $branding->color_primary ?? '#2563EB' }}; }
        h1 { text-align: center; margin: 24px 0; }
        .info-table { width: 100%; margin: 12px 0; }
        .info-table td { padding: 4px 8px; }
        .info-table td:first-child { width: 30%; color: #666; }
        .signature-area { margin-top: 64px; text-align: right; padding-right: 32px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">{{ $school->name }}</div>
        @if($school->address)<div>{{ $school->address }}</div>@endif
    </div>

    <h1>SURAT KEPUTUSAN PENERIMAAN PESERTA DIDIK BARU</h1>

    <p>Yang bertanda tangan di bawah ini, Kepala Sekolah {{ $school->name }}, dengan ini menyatakan bahwa:</p>

    <table class="info-table">
        <tr><td>Nama Lengkap</td><td>: <strong>{{ $app->student_name }}</strong></td></tr>
        <tr><td>NISN</td><td>: {{ $app->nisn ?? '—' }}</td></tr>
        <tr><td>Tanggal Lahir</td><td>: {{ $app->date_of_birth->format('d F Y') }}</td></tr>
        <tr><td>Jenis Kelamin</td><td>: {{ $app->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
        <tr><td>Alamat</td><td>: {{ $app->address }}</td></tr>
        <tr><td>No. Pendaftaran</td><td>: <strong>{{ $app->registration_no }}</strong></td></tr>
        <tr><td>Jalur Penerimaan</td><td>: {{ ucfirst($app->jalur) }}</td></tr>
    </table>

    <p style="margin-top: 24px;">
        Telah <strong>DITERIMA</strong> sebagai peserta didik baru di {{ $school->name }}
        Tahun Ajaran {{ now()->year }}/{{ now()->year + 1 }}.
    </p>

    <p>Demikian surat keputusan ini kami buat untuk dapat dipergunakan sebagaimana mestinya.</p>

    <div class="signature-area">
        <p>{{ $school->settings['city'] ?? 'Indonesia' }}, {{ optional($app->accepted_at)->format('d F Y') ?? now()->format('d F Y') }}</p>
        <p>Kepala Sekolah,</p>
        <br><br><br>
        <p style="border-top: 1px solid #333; padding-top: 4px; display: inline-block;">
            <strong>(_________________________)</strong>
        </p>
    </div>
</body>
</html>

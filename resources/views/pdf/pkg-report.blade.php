<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan PKG — {{ $assessment->teacher?->name }}</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: 'Inter', Arial, sans-serif; font-size: 10pt; color: #1a1a1a; line-height: 1.5; }
        h1 { font-size: 16pt; font-weight: 700; margin-bottom: 2px; color: #0b1d3a; }
        h2 { font-size: 12pt; font-weight: 600; color: #0b1d3a; border-bottom: 2px solid #b8860b; padding-bottom: 4px; margin-top: 18px; }
        .kicker { font-size: 8pt; letter-spacing: 2px; text-transform: uppercase; color: #b8860b; font-weight: 600; }
        .grid { display: flex; flex-wrap: wrap; gap: 12px; margin: 10px 0; }
        .grid-item { flex: 1; min-width: 45%; }
        .label { font-size: 7pt; letter-spacing: 1.5px; text-transform: uppercase; color: #6b6660; font-weight: 600; }
        .value { font-size: 10pt; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        th { background: #0b1d3a; color: #fff; padding: 8px 6px; text-align: left; font-size: 8pt; text-transform: uppercase; letter-spacing: 1.5px; }
        td { padding: 6px; border-bottom: 1px solid #e5e0d8; }
        .score-box { text-align: center; padding: 16px; border: 2px solid #b8860b; margin: 12px 0; }
        .score-number { font-size: 28pt; font-weight: 800; color: #b8860b; }
        .footer { margin-top: 30px; padding-top: 12px; border-top: 1px solid #ccc; font-size: 8pt; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="kicker">Penilaian Kinerja Guru — Permendiknas No. 16/2007</div>
    <h1>Laporan PKG: {{ $assessment->teacher?->name }}</h1>

    <div class="grid">
        <div class="grid-item"><span class="label">Penilai</span><br><span class="value">{{ $assessment->assessor?->name ?? '—' }}</span></div>
        <div class="grid-item"><span class="label">Tipe Penilaian</span><br><span class="value">{{ match($assessment->type){'self'=>'Self Assessment','peer'=>'Peer Review','supervisor'=>'Kepala Sekolah / Pengawas',default:$assessment->type} }}</span></div>
        <div class="grid-item"><span class="label">Tahun Ajaran</span><br><span class="value">{{ $assessment->academicYear?->name ?? '—' }}</span></div>
        <div class="grid-item"><span class="label">Semester</span><br><span class="value">{{ $assessment->semester }}</span></div>
        <div class="grid-item"><span class="label">Tanggal Penilaian</span><br><span class="value">{{ $assessment->assessment_date?->format('d M Y') }}</span></div>
        <div class="grid-item"><span class="label">Status</span><br><span class="value">{{ match($assessment->status){'draft'=>'Draft','submitted'=>'Terkirim','verified'=>'Terverifikasi',default:$assessment->status} }}</span></div>
    </div>

    <div class="score-box">
        <span class="label">Skor Akhir</span><br>
        <span class="score-number">{{ $assessment->final_score ?? '—' }}</span><br>
        <span style="font-size:11pt;font-weight:600;">{{ app(\App\Services\PkgService::class)->getRecommendationLabel($assessment->recommendation) }}</span>
    </div>

    <h2>Rincian Skor per Kompetensi</h2>
    <table>
        <tr><th>Kode</th><th>Kompetensi</th><th>Tipe</th><th>Bobot</th><th>Skor</th></tr>
        @foreach($competencies as $comp)
        <tr>
            <td>{{ $comp->code }}</td>
            <td>{{ $comp->name }}</td>
            <td>{{ $comp->competency_type }}</td>
            <td style="text-align:center">{{ $comp->weight }}</td>
            <td style="text-align:right;font-weight:700">{{ $scoreMap[$comp->id] ?? '—' }}</td>
        </tr>
        @endforeach
    </table>

    @if($assessment->observations->isNotEmpty())
    <h2>Observasi Kelas</h2>
    @foreach($assessment->observations as $obs)
    <div class="grid">
        <div class="grid-item"><span class="label">Tanggal</span><br>{{ $obs->observation_date?->format('d M Y') }}</div>
        <div class="grid-item"><span class="label">Kelas</span><br>{{ $obs->classSection?->classRoom?->name }} {{ $obs->classSection?->section?->name }}</div>
        <div class="grid-item"><span class="label">Suasana</span><br>{{ $obs->class_atmosphere }}</div>
        <div class="grid-item"><span class="label">Keterlibatan</span><br>{{ $obs->student_engagement }}</div>
    </div>
    <p style="font-style:italic;font-size:9pt;">{{ $obs->observation_notes }}</p>
    @endforeach
    @endif

    @if($assessment->notes)
    <h2>Catatan</h2>
    <p style="font-style:italic;font-size:10pt;">{{ $assessment->notes }}</p>
    @endif

    <div class="footer">
        Dokumen ini digenerate otomatis oleh eSchool SaaS &mdash; {{ now()->format('d M Y H:i') }}
    </div>
</body>
</html>

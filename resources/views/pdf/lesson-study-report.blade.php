<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Lesson Study — {{ $lessonStudy->title }}</title>
    <style>
        @page { size: A4; margin: 20mm; }
        body { font-family:'DejaVu Sans',sans-serif;font-size:11px;color:#1e293b;line-height:1.6; }
        .header { text-align:center;margin-bottom:24px; }
        .school-name { font-size:14px;font-weight:bold;color:#1e3a5f;letter-spacing:2px;text-transform:uppercase; }
        .doc-title { font-size:18px;color:#c5a45b;font-weight:bold;margin:8px 0; }
        .line { width:80px;height:1px;background:#c5a45b;margin:12px auto; }
        h2 { font-size:14px;color:#1e3a5f;border-bottom:1px solid #e2e8f0;padding-bottom:4px;margin:20px 0 10px; }
        table { width:100%;border-collapse:collapse;margin:10px 0;font-size:10px; }
        th { background:#1e3a5f;color:#fff;padding:6px 8px;text-align:left;font-size:9px;text-transform:uppercase;letter-spacing:1px; }
        td { padding:6px 8px;border-bottom:1px solid #e2e8f0; }
        .meta { font-size:10px;color:#64748b;margin-bottom:16px; }
        .rating-bar { display:inline-block;width:80px;height:8px;background:#e2e8f0;border-radius:4px;vertical-align:middle; }
        .rating-fill { height:8px;background:#c5a45b;border-radius:4px; }
        .rec { padding:4px 8px;background:#fef3c7;border-left:3px solid #c5a45b;margin:4px 0;font-size:10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">{{ $school->name ?? 'Sekolah' }}</div>
        <div class="doc-title">LAPORAN LESSON STUDY</div>
        <div class="line"></div>
        <h1 style="font-size:16px;color:#1e293b;margin:8px 0;">{{ $lessonStudy->title }}</h1>
    </div>

    <div class="meta">
        <strong>Fase:</strong> {{ $lessonStudy->phase }} |
        <strong>Status:</strong> {{ $lessonStudy->status }} |
        <strong>Guru Model:</strong> {{ $lessonStudy->leadTeacher->name ?? '—' }}
        @if($lessonStudy->subject) | <strong>Mapel:</strong> {{ $lessonStudy->subject->name }} @endif
        @if($lessonStudy->classSection) | <strong>Kelas:</strong> {{ $lessonStudy->classSection->classRoom->name ?? '' }} {{ $lessonStudy->classSection->section->name ?? '' }} @endif
    </div>

    <h2>ANGGOTA TIM</h2>
    <table>
        <tr><th>No</th><th>Nama</th><th>Peran</th></tr>
        @foreach($lessonStudy->members as $i => $m)
        <tr><td>{{ $i+1 }}</td><td>{{ $m->staff->name ?? '—' }}</td><td>{{ $m->role }}</td></tr>
        @endforeach
    </table>

    <h2>HASIL OBSERVASI</h2>
    @foreach($observationSummary as $type => $data)
        <div style="margin-bottom:12px;">
            <strong>{{ $data['label'] }}</strong>
            <span style="margin-left:12px;">Rating: {{ $data['avg_rating'] ?? 'N/A' }}/5</span>
            <span style="margin-left:12px;font-size:9px;color:#64748b;">{{ $data['count'] }} observer</span>
            <div class="rating-bar"><div class="rating-fill" style="width:{{ ($data['avg_rating'] ?? 0) * 20 }}%"></div></div>
            @foreach($data['notes'] as $note)
                <div style="font-size:10px;margin:4px 0;padding-left:12px;border-left:2px solid #e2e8f0;">{{ $note }}</div>
            @endforeach
        </div>
    @endforeach

    <h2>REFLEKSI TIM ({{ $reflectionSummary['count'] }} refleksi)</h2>
    @if(!empty($reflectionSummary['strengths']))
        <h3 style="font-size:11px;color:#16a34a;margin-bottom:4px;">Kekuatan:</h3>
        @foreach($reflectionSummary['strengths'] as $s)
            <div style="font-size:10px;margin:2px 0;padding-left:12px;">✓ {{ $s }}</div>
        @endforeach
    @endif
    @if(!empty($reflectionSummary['improvements']))
        <h3 style="font-size:11px;color:#dc2626;margin:12px 0 4px;">Area Perbaikan:</h3>
        @foreach($reflectionSummary['improvements'] as $imp)
            <div style="font-size:10px;margin:2px 0;padding-left:12px;">• {{ $imp }}</div>
        @endforeach
    @endif

    <h2>REKOMENDASI TINDAK LANJUT</h2>
    @foreach($recommendations as $r)
        <div class="rec">{{ $r }}</div>
    @endforeach

    <div style="margin-top:30px;text-align:right;font-size:9px;color:#94a3b8;">
        Laporan digenerate: {{ now()->format('d F Y H:i') }}
    </div>
</body>
</html>

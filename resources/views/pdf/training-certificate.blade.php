<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat Pelatihan — {{ $participant->staff->name ?? 'Peserta' }}</title>
    <style>
        @page { size: A4 landscape; margin: 0; }
        body { margin:0;padding:0;font-family:'DejaVu Sans',sans-serif; }
        .cert { width:100%;height:100%;position:relative;background:linear-gradient(135deg,#f8fafc 0%,#e2e8f0 100%); }
        .border-outer { position:absolute;top:30px;left:30px;right:30px;bottom:30px;border:4px solid #1e3a5f; }
        .border-inner { position:absolute;top:42px;left:42px;right:42px;bottom:42px;border:1px solid #c5a45b; }
        .content { position:absolute;top:60px;left:60px;right:60px;bottom:60px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center; }
        .school-name { font-size:16px;color:#1e3a5f;letter-spacing:4px;text-transform:uppercase;margin-bottom:12px;font-weight:bold; }
        .cert-title { font-size:32px;color:#c5a45b;font-weight:bold;letter-spacing:6px;margin-bottom:8px;text-transform:uppercase; }
        .line { width:120px;height:2px;background:#c5a45b;margin:16px auto; }
        .to-text { font-size:12px;color:#64748b;margin-bottom:4px; }
        .recipient { font-size:28px;color:#1e3a5f;font-weight:bold;margin-bottom:12px; }
        .desc { font-size:14px;color:#334155;max-width:600px;line-height:1.7;margin-bottom:20px; }
        .training-title { font-size:18px;color:#1e3a5f;font-weight:bold;margin-bottom:6px; }
        .details { font-size:12px;color:#64748b;line-height:2;margin-bottom:20px; }
        .cert-number { font-size:11px;color:#94a3b8;margin-bottom:10px; }
        .signatures { display:flex;justify-content:center;gap:100px;margin-top:20px; }
        .sig-block { text-align:center; }
        .sig-line { width:160px;border-bottom:1px solid #1e3a5f;margin-bottom:4px; }
        .sig-name { font-size:11px;color:#1e3a5f;font-weight:bold; }
        .sig-role { font-size:10px;color:#94a3b8; }
    </style>
</head>
<body>
    <div class="cert">
        <div class="border-outer"><div class="border-inner"></div></div>
        <div class="content">
            <div class="school-name">{{ $school->name ?? 'Sekolah' }}</div>
            <div class="cert-title">SERTIFIKAT</div>
            <div class="line"></div>
            <div class="to-text">DIBERIKAN KEPADA</div>
            <div class="recipient">{{ $participant->staff->name ?? 'Peserta' }}</div>
            <div class="desc">
                @if($training->certificate_template)
                    {{ str_replace(['{nama}','{pelatihan}'], [$participant->staff->name ?? '', $training->title], $training->certificate_template) }}
                @else
                    Atas partisipasi aktif dalam kegiatan:
                @endif
            </div>
            <div class="training-title">"{{ $training->title }}"</div>
            <div class="details">
                Diselenggarakan oleh: {{ $training->provider ?? 'Sekolah' }}<br>
                Tanggal: {{ $training->start_date->format('d F Y') }} @if($training->end_date) — {{ $training->end_date->format('d F Y') }} @endif<br>
                Durasi: {{ $training->duration_hours }} Jam Pelajaran
                @if($participant->score) | Nilai: {{ $participant->score }}/100 @endif
            </div>
            <div class="cert-number">No. Sertifikat: {{ $participant->certificate_number }}</div>

            <div class="signatures">
                <div class="sig-block">
                    <div class="sig-line"></div>
                    <div class="sig-name">Kepala Sekolah</div>
                    <div class="sig-role">{{ $school->name ?? '' }}</div>
                </div>
                <div class="sig-block">
                    <div class="sig-line"></div>
                    <div class="sig-name">Ketua Panitia</div>
                    <div class="sig-role">{{ $training->provider ?? '' }}</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

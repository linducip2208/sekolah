<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sertifikat — {{ $achievement->title }}</title>
    <style>
        @page { margin: 0; }
        body { font-family: 'Helvetica', sans-serif; margin: 0; }
        .certificate {
            width: 100%;
            min-height: 540pt;
            padding: 60pt;
            text-align: center;
            background:
                linear-gradient(135deg, {{ $branding->color_primary ?? '#2563EB' }} 0%, {{ $branding->color_secondary ?? '#64748B' }} 100%);
            color: white;
            box-sizing: border-box;
        }
        .inner-frame {
            border: 4px double white;
            padding: 40pt;
            min-height: 420pt;
        }
        h1 { font-size: 36pt; margin: 16pt 0; letter-spacing: 4pt; }
        .recipient { font-size: 28pt; font-weight: bold; margin: 32pt 0 16pt; border-bottom: 2px solid white; display: inline-block; padding: 0 32pt 8pt; }
        .achievement { font-size: 18pt; font-style: italic; margin: 24pt 0; }
        .school-name { margin-top: 48pt; font-weight: bold; font-size: 14pt; letter-spacing: 2pt; }
        .date-line { margin-top: 16pt; font-size: 11pt; }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="inner-frame">
            <div style="font-size: 12pt; letter-spacing: 8pt;">CERTIFICATE</div>
            <h1>OF ACHIEVEMENT</h1>
            <div style="font-size: 13pt;">Diberikan kepada</div>
            <div class="recipient">{{ $student?->user->name ?? '—' }}</div>
            <div class="achievement">atas pencapaiannya sebagai</div>
            <div style="font-size: 22pt; font-weight: bold;">{{ $achievement->title }}</div>
            @if($achievement->description)
                <div style="font-size: 12pt; margin-top: 12pt;">{{ $achievement->description }}</div>
            @endif
            <div class="school-name">{{ $school->name }}</div>
            <div class="date-line">{{ optional($achievement->achieved_at)->format('d F Y') }}</div>
        </div>
    </div>
</body>
</html>

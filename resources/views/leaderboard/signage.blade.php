<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $school->name }} — Leaderboard {{ $periodLabel }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700|playfair-display:400,500,600,700,800|cormorant-garamond:400,500,600,700|jetbrains-mono:400,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{}}};</script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background: #0b1d3a; color: #fff; font-family: 'Inter', sans-serif; min-height:100vh; display:flex; flex-direction:column; overflow:hidden; }
        .bg-pattern { position: fixed; inset: 0; background-image: radial-gradient(rgba(184,134,11,.08) 1px, transparent 1px); background-size: 40px 40px; z-index: 0; }
        .content { position: relative; z-index: 1; flex:1; display:flex; flex-direction:column; padding: 2rem 3rem; }
        .header { text-align: center; margin-bottom: 2rem; }
        .header h1 { font-family: 'Playfair Display', serif; font-size: 2.5rem; font-weight: 700; letter-spacing: -.01em; }
        .header .school-name { font-family: 'Cormorant Garamond', serif; font-size: 1.25rem; color: #b8860b; font-style: italic; margin-bottom: .25rem; }
        .header .period { font-size: .7rem; letter-spacing: .25em; text-transform: uppercase; color: rgba(255,255,255,.4); margin-top: .5rem; }
        .podium-row { display: flex; align-items: flex-end; justify-content: center; gap: 1.5rem; flex:1; padding: 0 1rem; }
        .podium-spot { display: flex; flex-direction: column; align-items: center; width: 220px; }
        .podium-bar { width: 100%; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding-top: 1.5rem; border-radius: 6px 6px 0 0; transition: all .5s ease; }
        .gold .podium-bar { height: 260px; background: linear-gradient(180deg, #FFD700 0%, #B8860B 100%); }
        .silver .podium-bar { height: 210px; background: linear-gradient(180deg, #C0C0C0 0%, #808080 100%); }
        .bronze .podium-bar { height: 170px; background: linear-gradient(180deg, #CD7F32 0%, #8B4513 100%); }
        .rank-num { font-family: 'Playfair Display', serif; font-size: 4rem; font-weight: 800; color: rgba(0,0,0,.3); text-shadow: 0 2px 4px rgba(255,255,255,.15); }
        .rank-name { font-family: 'Cormorant Garamond', serif; font-size: 1.1rem; font-weight: 700; color: rgba(0,0,0,.8); margin-top: .25rem; text-align: center; }
        .rank-class { font-size: .65rem; font-weight: 500; color: rgba(0,0,0,.5); }
        .rank-score { font-size: .8rem; font-weight: 700; color: rgba(0,0,0,.6); margin-top: .25rem; }
        .rank-label { font-family: 'Inter', sans-serif; font-size: .65rem; letter-spacing: .15em; text-transform: uppercase; font-weight: 600; margin-top: .75rem; color: #b8860b; }

        @media (max-width: 768px) {
            .content { padding: 1rem; }
            .header h1 { font-size: 1.5rem; }
            .podium-row { gap: .5rem; }
            .podium-spot { width: 100px; }
            .gold .podium-bar { height: 180px; }
            .silver .podium-bar { height: 140px; }
            .bronze .podium-bar { height: 110px; }
            .rank-num { font-size: 2.5rem; }
            .rank-name { font-size: .8rem; }
        }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    <div class="content">
        <div class="header">
            <div class="school-name">{{ $school->name }}</div>
            <h1>Leaderboard {{ $periodLabel }}</h1>
            <div class="period">Diperbarui {{ now()->format('d M Y H:i') }}</div>
        </div>

        @if(count($rankings) >= 3)
            @php
                $gold = $rankings[0];
                $silver = $rankings[1];
                $bronze = $rankings[2];
            @endphp
            <div class="podium-row">
                <div class="podium-spot silver">
                    <div class="podium-bar">
                        <div class="rank-num">2</div>
                        <div class="rank-name">{{ \Illuminate\Support\Str::limit($silver['student_name'], 18) }}</div>
                        <div class="rank-class">{{ $silver['class_section'] }}</div>
                        <div class="rank-score">{{ (int)$silver['weighted_score'] }} poin</div>
                    </div>
                    <div class="rank-label">Juara 2</div>
                </div>
                <div class="podium-spot gold">
                    <div class="podium-bar">
                        <div class="rank-num">1</div>
                        <div class="rank-name">{{ \Illuminate\Support\Str::limit($gold['student_name'], 18) }}</div>
                        <div class="rank-class">{{ $gold['class_section'] }}</div>
                        <div class="rank-score">{{ (int)$gold['weighted_score'] }} poin</div>
                    </div>
                    <div class="rank-label">Juara 1</div>
                </div>
                <div class="podium-spot bronze">
                    <div class="podium-bar">
                        <div class="rank-num">3</div>
                        <div class="rank-name">{{ \Illuminate\Support\Str::limit($bronze['student_name'], 18) }}</div>
                        <div class="rank-class">{{ $bronze['class_section'] }}</div>
                        <div class="rank-score">{{ (int)$bronze['weighted_score'] }} poin</div>
                    </div>
                    <div class="rank-label">Juara 3</div>
                </div>
            </div>
        @endif

        @if(count($rankings) > 3)
        <div style="margin-top:2rem;flex:1;overflow-y:auto;max-height:30vh;">
            <table style="width:100%;font-size:.75rem;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(255,255,255,.15);">
                        <th style="text-align:left;padding:.5rem .75rem;color:rgba(255,255,255,.5);font-size:.6rem;letter-spacing:.15em;text-transform:uppercase;">#</th>
                        <th style="text-align:left;padding:.5rem .75rem;color:rgba(255,255,255,.5);font-size:.6rem;letter-spacing:.15em;text-transform:uppercase;">Nama</th>
                        <th style="text-align:left;padding:.5rem .75rem;color:rgba(255,255,255,.5);font-size:.6rem;letter-spacing:.15em;text-transform:uppercase;">Kelas</th>
                        <th style="text-align:right;padding:.5rem .75rem;color:rgba(255,255,255,.5);font-size:.6rem;letter-spacing:.15em;text-transform:uppercase;">Poin</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rankings as $entry)
                        @if($entry['rank'] <= 3) @continue @endif
                    <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                        <td style="padding:.4rem .75rem;color:rgba(255,255,255,.5);font-weight:600;">{{ $entry['rank'] }}</td>
                        <td style="padding:.4rem .75rem;font-family:'Cormorant Garamond',serif;font-weight:600;">{{ $entry['student_name'] }}</td>
                        <td style="padding:.4rem .75rem;color:rgba(255,255,255,.45);">{{ $entry['class_section'] }}</td>
                        <td style="padding:.4rem .75rem;text-align:right;color:#b8860b;font-weight:700;">{{ (int)$entry['weighted_score'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div style="text-align:center;margin-top:1.5rem;opacity:.3;font-size:.6rem;letter-spacing:.15em;">
            Sikad Pro &middot; {{ $school->name }} &middot; {{ $periodLabel }}
        </div>
    </div>

    <script>
        setTimeout(() => location.reload(), 60000);
    </script>
</body>
</html>

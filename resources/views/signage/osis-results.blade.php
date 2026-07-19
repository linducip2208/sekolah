<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="{{ $refreshInterval }}">
    <title>Hasil OSIS — {{ $school->name }}</title>
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:400,500,600,700,400i|playfair-display:400,500,600,700,800,900|inter:300,400,500,600,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --c-primary: #0b1d3a; --c-accent: #b8860b; }
        body { background: #0b1d3a; color: #fff; font-family: 'Inter', sans-serif; overflow: hidden; }
        .font-display { font-family: 'Playfair Display', Georgia, serif; }
        .font-serif { font-family: 'Cormorant Garamond', Georgia, serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center">
<div class="w-full max-w-6xl px-8">
    <div class="text-center mb-10">
        <div class="font-display text-5xl font-bold mb-2">Hasil Pemilihan OSIS</div>
        <div class="font-serif text-2xl opacity-80">{{ $election->title }}</div>
        <div class="mt-3 text-lg opacity-60">{{ $school->name }} · Total Pemilih: {{ $totalVoters }}</div>
    </div>

    <div class="grid grid-cols-2 gap-8 mb-10">
        @foreach($winners as $w)
        <div class="border-2 border-yellow-600/50 p-8 text-center bg-white/5 backdrop-blur">
            <div class="text-6xl mb-3">🏆</div>
            <div class="font-display text-3xl font-bold">{{ $w['candidate']->student?->user?->name ?? '—' }}</div>
            <div class="font-serif text-xl text-yellow-400 mt-2">{{ $w['position'] }}</div>
            <div class="font-display text-5xl font-black text-yellow-400 mt-4">{{ $w['vote_count'] }}</div>
            <div class="text-sm opacity-60 mt-1">SUARA</div>
        </div>
        @endforeach
    </div>

    <div class="space-y-3">
        @foreach($candidates as $c)
        <div class="flex justify-between items-center bg-white/5 p-4">
            <div>
                <span class="font-serif text-lg">{{ $c->student?->user?->name }}</span>
                <span class="text-sm opacity-50 ml-3">{{ $c->position }}</span>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-64 h-5 bg-white/10">
                    @php
                    $max = $candidates->max('vote_count') ?: 1;
                    $pct = $max > 0 ? ($c->vote_count / $max * 100) : 0;
                    @endphp
                    <div class="h-full bg-yellow-500/60" style="width:{{ $pct }}%"></div>
                </div>
                <span class="font-mono text-xl font-bold w-10 text-right">{{ $c->vote_count }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <div class="text-center mt-10 text-sm opacity-40">Auto-refresh setiap {{ $refreshInterval }} detik · {{ now()->format('H:i:s') }}</div>
</div>
</body>
</html>

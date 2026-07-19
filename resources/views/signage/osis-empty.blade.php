<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="30">
    <title>Belum Ada Pemilihan — {{ $school->name }}</title>
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:400,500,600,700,400i|playfair-display:400,500,600,700,800,900|inter:300,400,500,600,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --c-primary: #0b1d3a; --c-accent: #b8860b; }
        body { background: #0b1d3a; color: #fff; font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Playfair Display', Georgia, serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center">
<div class="text-center px-4">
    <div class="text-8xl mb-6">🗳️</div>
    <div class="font-display text-4xl font-bold mb-3">Belum Ada Pemilihan</div>
    <div class="font-serif text-xl opacity-60">Saat ini tidak ada pemilihan OSIS yang aktif di {{ $school->name }}.</div>
</div>
</body>
</html>

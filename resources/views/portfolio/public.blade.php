<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $portfolio->title }} — e-Portfolio</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,600,700|cormorant-garamond:400,500,600,700|inter:300,400,500,600,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --c-primary: #0b1d3a;
            --c-accent: #b8860b;
            --c-paper: #f8f5ee;
        }
        body { background: var(--c-paper); font-family: 'Inter', sans-serif; color: #1a1a1a; -webkit-font-smoothing: antialiased; }
        .font-display { font-family: 'Playfair Display', Georgia, serif; }
        .font-serif { font-family: 'Cormorant Garamond', Georgia, serif; }
        .elite-kicker { font-family: 'Inter', sans-serif; font-size: .68rem; letter-spacing: .35em; text-transform: uppercase; font-weight: 600; color: var(--c-accent); }
        .portfolio-card { background: #fff; border: 1px solid rgba(11,29,58,.12); max-width: 48rem; margin: 2rem auto; }
    </style>
</head>
<body class="paper min-h-screen flex items-center justify-center p-4">

<div class="portfolio-card rounded-lg shadow-lg overflow-hidden w-full">
    <div class="bg-gradient-to-r from-[var(--c-primary)] to-[#1a365d] px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <div class="elite-kicker text-white/70 text-[.6rem]">e-Portfolio</div>
                <h1 class="font-display text-2xl text-white font-bold mt-1">{{ $portfolio->title }}</h1>
            </div>
            @php
                $icon = match($portfolio->portfolio_type) {
                    'academic' => '📚', 'achievement' => '🏆', 'project' => '🔬',
                    'certificate' => '📜', 'artwork' => '🎨', default => '📁'
                };
            @endphp
            <span class="text-4xl">{{ $icon }}</span>
        </div>
    </div>

    <div class="p-6">
        <div class="grid md:grid-cols-3 gap-6 mb-6">
            <div class="md:col-span-2">
                @if($portfolio->description)
                    <div class="mb-4">
                        <div class="elite-kicker text-[.6rem] mb-2">Deskripsi</div>
                        <p class="font-serif text-sm leading-relaxed text-gray-700">{{ $portfolio->description }}</p>
                    </div>
                @endif

                @if($portfolio->tags)
                    <div class="mb-4">
                        <div class="elite-kicker text-[.6rem] mb-2">Tag</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($portfolio->tags as $tag)
                                <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($portfolio->url)
                    <div class="mb-4">
                        <div class="elite-kicker text-[.6rem] mb-2">Link</div>
                        <a href="{{ $portfolio->url }}" target="_blank" rel="noopener" class="text-sm text-blue-700 hover:underline">{{ $portfolio->url }}</a>
                    </div>
                @endif
            </div>

            <div class="space-y-3">
                @if($portfolio->student)
                <div>
                    <div class="elite-kicker text-[.6rem] mb-1">Oleh</div>
                    <p class="font-serif font-semibold text-sm">{{ $portfolio->student->user?->name }}</p>
                    <p class="text-xs text-gray-500">{{ $portfolio->student->admission_no }}</p>
                </div>
                @endif

                <div>
                    <div class="elite-kicker text-[.6rem] mb-1">Tipe</div>
                    <p class="text-sm font-semibold">
                        {{ ['academic' => 'Akademik', 'achievement' => 'Prestasi', 'project' => 'Proyek', 'certificate' => 'Sertifikat', 'artwork' => 'Karya Seni', 'other' => 'Lainnya'][$portfolio->portfolio_type] ?? $portfolio->portfolio_type }}
                    </p>
                </div>

                <div>
                    <div class="elite-kicker text-[.6rem] mb-1">Dibuat</div>
                    <p class="text-sm text-gray-600">{{ $portfolio->created_at->format('d M Y') }}</p>
                </div>

                @if($portfolio->approved_at)
                <div>
                    <div class="elite-kicker text-[.6rem] mb-1">Status</div>
                    <p class="text-sm text-green-700 font-semibold">✓ Disetujui</p>
                </div>
                @endif
            </div>
        </div>

        @if($portfolio->file_path)
            <div class="border-t pt-4">
                <a href="{{ asset('storage/' . $portfolio->file_path) }}" class="btn-elite text-xs inline-flex items-center gap-2" download>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Unduh File
                </a>
            </div>
        @endif
    </div>

    <div class="bg-gray-50 border-t px-6 py-3 text-center text-xs text-gray-400">
        e-Portfolio · Sikad Pro · {{ date('Y') }}
    </div>
</div>

</body>
</html>

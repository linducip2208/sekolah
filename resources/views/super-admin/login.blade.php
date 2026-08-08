<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Magisterium — Operator Platform | {{ $platform['app_name'] ?? 'Sikad Pro' }}</title>
    @include('elite.partials.head')
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative" style="background: var(--c-primary);">
    {{-- Background ornament --}}
    <svg class="absolute top-0 left-0 w-full h-full opacity-[.04] pointer-events-none" preserveAspectRatio="xMidYMid slice" viewBox="0 0 100 100">
        <pattern id="dot-grid" width="6" height="6" patternUnits="userSpaceOnUse">
            <circle cx="1" cy="1" r=".5" fill="white"/>
        </pattern>
        <rect width="100" height="100" fill="url(#dot-grid)"/>
    </svg>

    <div class="w-full max-w-md relative">
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-3 mb-6">
                @if(!empty($platform['logo_dark_url']))
                    <img src="{{ $platform['logo_dark_url'] }}" alt="" class="h-12">
                @elseif(!empty($platform['logo_url']))
                    <img src="{{ $platform['logo_url'] }}" alt="" class="h-12 brightness-0 invert">
                @else
                    <div class="crest-mark lg" style="border-color: var(--c-accent); color: var(--c-accent);">
                        <svg class="w-9 h-9" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 2 7l10 5 10-5-10-5zm0 7L2 14l10 5 10-5-10-5z"/></svg>
                    </div>
                @endif
            </a>
            <div class="ornament-center" style="color: var(--c-accent);"></div>
            <div class="elite-kicker mb-2" style="color: var(--c-accent);">Magisterium</div>
            <h1 class="elite-h1 text-3xl text-white">Operator Platform</h1>
            <div class="elite-rule mx-auto mt-3" style="color: var(--c-accent);"></div>
        </div>

        <div class="bg-white deco-frame">
            <div class="p-10">
                <h2 class="elite-h3 text-xl ink-primary mb-1">Sesi Eksklusif</h2>
                <p class="font-serif text-base mb-6" style="color: var(--c-muted);">Masuk untuk mengelola seluruh institusi dalam platform.</p>

                @if($errors->any())
                    <div class="mb-5 px-4 py-3 bg-red-50 border-l-4 border-red-700">
                        <p class="font-serif text-sm text-red-800">{{ $errors->first() }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('super.login.post') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="elite-kicker block mb-2">Surat Elektronik</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full border-2 border-rule bg-white px-4 py-3 font-serif text-base">
                    </div>
                    <div>
                        <label class="elite-kicker block mb-2">Kata Sandi</label>
                        <input type="password" name="password" required
                               class="w-full border-2 border-rule bg-white px-4 py-3 font-serif text-base">
                    </div>
                    <button type="submit" class="btn-elite w-full" style="padding: 1rem;">Masuk ke Magisterium</button>
                </form>

                <div class="mt-5 p-4" style="background: rgba(184,134,11,.06); border-left: 3px solid var(--c-accent);">
                    <div class="elite-kicker mb-2" style="color: var(--c-accent);">Akun Demo</div>
                    <div class="font-serif text-sm leading-relaxed text-gray-700 space-y-1">
                        <div><code style="background:#fff;padding:.1rem .35rem;font-size:.85em;">super@sikadpro.app</code> / <code style="background:#fff;padding:.1rem .35rem;font-size:.85em;">SuperAdmin123!</code></div>
                        <div class="text-xs text-gray-500 mt-2">Admin sekolah? Login di <a href="/admin/login" class="underline ink-secondary hover:ink-accent">/admin/login</a>.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-8 font-script italic text-xl" style="color: var(--c-accent);">{{ $platform['motto_latin'] ?? 'Floreat Schola' }}</div>
    </div>
</body>
</html>

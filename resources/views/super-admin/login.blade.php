<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Operator Platform — {{ $platform['app_name'] ?? 'Sikad Pro' }}</title>
    @include('elite.partials.head')
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased min-h-screen flex items-center justify-center p-4 relative" style="background: var(--c-primary-dark, #134E4A);">
    {{-- Background dot grid --}}
    <svg class="absolute top-0 left-0 w-full h-full opacity-[0.05] pointer-events-none" preserveAspectRatio="xMidYMid slice" viewBox="0 0 100 100" aria-hidden="true">
        <pattern id="dot-grid" width="6" height="6" patternUnits="userSpaceOnUse">
            <circle cx="1" cy="1" r=".5" fill="white"/>
        </pattern>
        <rect width="100" height="100" fill="url(#dot-grid)"/>
    </svg>

    <div class="w-full max-w-md relative">
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-3 mb-6">
                @if(!empty($platform['logo_dark_url']))
                    <img src="{{ $platform['logo_dark_url'] }}" alt="" class="h-12 w-auto">
                @elseif(!empty($platform['logo_url']))
                    <img src="{{ $platform['logo_url'] }}" alt="" class="h-12 w-auto brightness-0 invert">
                @else
                    <span class="h-12 w-12 rounded-xl flex items-center justify-center font-extrabold text-white" style="background: var(--c-accent);">{{ Str::upper(Str::substr($platform['app_name'] ?? 'S', 0, 1)) }}</span>
                @endif
            </a>
            <div class="text-xs uppercase tracking-widest font-semibold mb-2" style="color: var(--c-accent);">Operator Platform</div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white">Panel Super Admin</h1>
        </div>

        <div class="rounded-2xl p-8 shadow-xl" style="background: var(--color-surface);">
            <h2 class="text-xl font-bold" style="color: var(--color-text);">Masuk</h2>
            <p class="text-sm mt-1 mb-6" style="color: var(--color-text-muted);">Kelola seluruh sekolah, billing, dan konfigurasi platform.</p>

            @if(isset($errors) && $errors->any())
                <x-ui.alert variant="danger" class="mb-5">{{ $errors->first() }}</x-ui.alert>
            @endif

            <form method="POST" action="{{ route('super.login.post') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="label" for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus class="input" placeholder="super@platform.id">
                </div>
                <div>
                    <label class="label" for="password">Kata Sandi</label>
                    <input type="password" id="password" name="password" required class="input" placeholder="••••••••">
                </div>
                <button type="submit" class="btn w-full" style="min-height: 46px;">Masuk</button>
            </form>

            <div class="mt-5 p-4 rounded-xl" style="background: var(--color-primary-soft); border: 1px solid var(--color-primary-light);">
                <div class="text-sm font-semibold mb-2" style="color: var(--color-primary);">Akun Demo</div>
                <div class="text-xs font-mono space-y-1" style="color: var(--color-text-secondary);">
                    <div>super@sikadpro.app / SuperAdmin123!</div>
                    <div class="mt-1 text-[var(--color-text-muted)]">Admin sekolah? Login di <a href="/admin/login" class="underline font-semibold">/admin/login</a>.</div>
                </div>
            </div>
        </div>

        <div class="text-center mt-8 text-sm text-white/60">{{ $platform['motto_latin'] ?? 'Floreat Schola' }}</div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — {{ $platform['app_name'] ?? 'Sikad Pro' }}</title>
    @include('elite.partials.head')
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased" style="background: var(--color-background);">
<div class="min-h-screen grid lg:grid-cols-2">

    {{-- Brand panel --}}
    <div class="hidden lg:flex flex-col justify-between p-12 relative overflow-hidden" style="background: var(--c-primary);">
        <div class="lp-pattern absolute inset-0 opacity-[0.07] pointer-events-none" aria-hidden="true" style="background-image: radial-gradient(circle at 1px 1px, #CCFBF1 1px, transparent 0); background-size: 26px 26px;"></div>
        <div class="relative">
            <a href="/" class="flex items-center gap-3">
                @if(!empty($platform['logo_dark_url']))
                    <img src="{{ $platform['logo_dark_url'] }}" alt="" class="h-11 w-auto">
                @elseif(!empty($platform['logo_url']))
                    <img src="{{ $platform['logo_url'] }}" alt="" class="h-11 w-auto brightness-0 invert">
                @else
                    <span class="h-11 w-11 rounded-xl flex items-center justify-center font-extrabold text-white" style="background: var(--c-accent);">{{ Str::upper(Str::substr($platform['app_name'] ?? 'S', 0, 1)) }}</span>
                @endif
                <span class="text-white font-extrabold text-xl">{{ $platform['app_name'] ?? 'Sikad Pro' }}</span>
            </a>
        </div>

        <div class="relative text-white">
            <h2 class="text-4xl font-extrabold leading-tight tracking-tight max-w-md">Kelola seluruh operasional sekolah dalam satu platform.</h2>
            <p class="mt-4 text-white/80 text-lg leading-relaxed max-w-md">Akademik, siswa, keuangan, PPDB, HR, dan komunikasi — terintegrasi untuk sekolah, yayasan, dan pesantren.</p>
            <ul class="mt-8 space-y-4 max-w-md">
                @foreach(['Multi-tenant & white-label per sekolah', 'Role-based untuk admin, guru, orang tua, siswa', 'BYOK: payment, AI, SMS — Anda yang memilih'] as $b)
                    <li class="flex items-center gap-3 text-white/90">
                        <span class="h-7 w-7 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(255,255,255,.15);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <span class="text-sm">{{ $b }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="relative text-white/60 text-xs">&copy; {{ now()->year }} {{ $platform['app_name'] ?? 'Sikad Pro' }} · Powered by Laravel</div>
    </div>

    {{-- Form panel --}}
    <div class="flex items-center justify-center p-6 sm:p-12">
        <div class="w-full max-w-md">
            <div class="mb-10">
                <h1 class="text-3xl font-extrabold tracking-tight" style="color: var(--color-text);">Masuk</h1>
                <p class="mt-2 text-sm" style="color: var(--color-text-muted);">Masuk ke panel administrasi sekolah Anda.</p>
            </div>

            @if(isset($errors) && $errors->any())
                <x-ui.alert variant="danger" class="mb-5">{{ $errors->first() }}</x-ui.alert>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="label" for="email">Email</label>
                    <input type="email" id="email" name="email" required value="{{ old('email') }}" class="input" placeholder="anda@sekolah.id">
                </div>
                <div>
                    <label class="label" for="password">Kata Sandi</label>
                    <input type="password" id="password" name="password" required class="input" placeholder="••••••••">
                </div>
                <button type="submit" class="btn w-full" style="min-height: 46px;">Masuk</button>
            </form>

            <div class="mt-6 p-4 rounded-xl" style="background: var(--color-primary-soft); border: 1px solid var(--color-primary-light);">
                <div class="text-sm font-semibold mb-2" style="color: var(--color-primary);">Akun Demo</div>
                <div class="text-xs space-y-1.5 font-mono" style="color: var(--color-text-secondary);">
                    <div><strong>Admin Sekolah:</strong> admin@sman1demo.sch.id / Admin123!</div>
                    <div><strong>Guru:</strong> guru1@sman1demo.sch.id / Guru123!</div>
                    <div class="mt-2 text-[var(--color-text-muted)]">Super Admin? Login di <a href="/super/login" class="underline font-semibold">/super/login</a>.</div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t text-center text-sm" style="border-color: var(--color-border); color: var(--color-text-muted);">
                <div class="flex justify-center gap-4">
                    <a href="/docs/admin" class="underline hover:text-[var(--color-primary)]">Buku Panduan</a>
                    @if(!empty($platform['contact_phone']))
                        <span>·</span>
                        <a href="tel:{{ $platform['contact_phone'] }}" class="underline hover:text-[var(--color-primary)]">{{ $platform['contact_phone'] }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

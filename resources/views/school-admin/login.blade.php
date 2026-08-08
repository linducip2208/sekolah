<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manus Magistri — Login Administrator | {{ $platform['app_name'] ?? 'Sikad Pro' }}</title>
    @include('elite.partials.head')
</head>
<body class="paper min-h-screen">
<div class="min-h-screen grid lg:grid-cols-2">
    {{-- Decorative left panel --}}
    <div class="hidden lg:flex flex-col justify-between p-12 text-white relative overflow-hidden" style="background: var(--c-primary);">
        <div>
            <a href="/" class="flex items-center gap-3 mb-12">
                @if(!empty($platform['logo_url']))
                    <img src="{{ $platform['logo_url'] }}" alt="" class="h-10 brightness-0 invert">
                @else
                    <div class="crest-mark" style="border-color: var(--c-accent); color: var(--c-accent);">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 2 7l10 5 10-5-10-5zm0 7L2 14l10 5 10-5-10-5z"/></svg>
                    </div>
                @endif
                <div>
                    <div class="elite-h3 text-xl text-white">{{ $platform['app_name'] ?? 'Sikad Pro' }}</div>
                    <div class="elite-kicker text-[.55rem]" style="color: var(--c-accent);">Est. {{ $platform['established_year'] ?? '1890' }}</div>
                </div>
            </a>
        </div>

        <div>
            <div class="quote-mark" style="color: var(--c-accent); opacity:.55;">"</div>
            <p class="font-display text-3xl text-white leading-snug mb-5" style="font-weight:500;">Pendidikan adalah perhiasan dalam kemakmuran dan tempat berlindung dalam kesulitan.</p>
            <div class="elite-rule mb-3" style="color: var(--c-accent);"></div>
            <div class="elite-kicker" style="color: var(--c-accent);">— Aristoteles</div>
        </div>

        <div class="font-script italic text-2xl" style="color: var(--c-accent);">{{ $platform['motto_latin'] ?? 'Floreat Schola' }}</div>

        {{-- Background ornament --}}
        <svg class="absolute -right-20 -bottom-20 w-96 h-96 opacity-[.06]" viewBox="0 0 100 100" fill="currentColor">
            <path d="M50 5 L65 35 L95 40 L72 62 L78 92 L50 78 L22 92 L28 62 L5 40 L35 35 Z"/>
        </svg>
    </div>

    {{-- Form panel --}}
    <div class="flex items-center justify-center p-6 sm:p-12">
        <div class="w-full max-w-md">
            <div class="text-center mb-10">
                <div class="ornament-center"></div>
                <div class="elite-kicker mb-2">Manus Magistri</div>
                <h1 class="elite-h1 text-4xl ink-primary mb-3">Selamat Datang Kembali</h1>
                <div class="elite-rule mx-auto mb-3"></div>
                <p class="font-serif text-lg" style="color: var(--c-muted);">Masuk ke panel administrasi sekolah Anda.</p>
            </div>

            @if($errors->any())
                <div class="mb-5 px-5 py-3 bg-white border-l-4 border-red-700">
                    <p class="font-serif text-base text-red-800">{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="elite-kicker block mb-2">Surat Elektronik</label>
                    <input type="email" name="email" required value="{{ old('email') }}"
                           class="w-full border-2 border-rule bg-white px-4 py-3 font-serif text-base"
                           placeholder="anda@sekolah.id">
                </div>
                <div>
                    <label class="elite-kicker block mb-2">Kata Sandi</label>
                    <input type="password" name="password" required
                           class="w-full border-2 border-rule bg-white px-4 py-3 font-serif text-base"
                           placeholder="••••••••">
                </div>
                <button type="submit" class="btn-elite w-full" style="padding: 1rem;">Masuk ke Akademi</button>
            </form>

            <div class="mt-6 p-4" style="background: rgba(184,134,11,.06); border-left: 3px solid var(--c-accent);">
                <div class="elite-kicker mb-2" style="color: var(--c-accent);">Akun Demo (Development)</div>
                <div class="font-serif text-sm leading-relaxed text-gray-700 space-y-1">
                    <div><strong>Admin Sekolah:</strong> <code style="background:#fff;padding:.1rem .35rem;font-size:.85em;">admin@sman1demo.sch.id</code> / <code style="background:#fff;padding:.1rem .35rem;font-size:.85em;">Admin123!</code></div>
                    <div><strong>Guru:</strong> <code style="background:#fff;padding:.1rem .35rem;font-size:.85em;">guru1@sman1demo.sch.id</code> / <code style="background:#fff;padding:.1rem .35rem;font-size:.85em;">Guru123!</code></div>
                    <div class="text-xs text-gray-500 mt-2">Super Admin? Login di <a href="/super/login" class="underline ink-secondary hover:ink-accent">/super/login</a> (bukan halaman ini).</div>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-rule text-center">
                <div class="elite-kicker mb-3" style="color: var(--c-muted);">Membutuhkan Bantuan?</div>
                <div class="flex justify-center gap-4">
                    <a href="/docs/admin" class="text-sm font-serif underline ink-secondary hover:ink-accent">Buku Panduan</a>
                    <span class="ink-accent">·</span>
                    <a href="/super/login" class="text-sm font-serif underline ink-secondary hover:ink-accent">Login Super Admin</a>
                    @if(!empty($platform['contact_phone']))
                        <span class="ink-accent">·</span>
                        <a href="tel:{{ $platform['contact_phone'] }}" class="text-sm font-serif underline ink-secondary hover:ink-accent">{{ $platform['contact_phone'] }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

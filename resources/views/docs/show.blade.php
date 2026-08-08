@extends('elite.layout')

@section('title', strip_tags($title) . ' — Panduan')
@section('description', $lead)

@section('header')
@include('elite.partials.header')
@endsection

@section('content')

<section class="paper">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12 lg:py-16">
        <nav class="elite-kicker mb-8" style="color: var(--c-muted);">
            <a href="/" class="hover:ink-primary">Beranda</a>
            <span class="mx-2 ink-accent">/</span>
            <a href="/docs" class="hover:ink-primary">Panduan</a>
            <span class="mx-2 ink-accent">/</span>
            <span>{{ $kicker }}</span>
        </nav>

        <div class="grid lg:grid-cols-12 gap-12">
            {{-- Sidebar --}}
            <aside class="lg:col-span-3 lg:sticky lg:top-32 lg:self-start">
                <div class="elite-kicker mb-4">Bab Lain</div>
                <ul class="space-y-2 border-l border-rule pl-5">
                    @foreach($roles as $r)
                        <li>
                            <a href="{{ url('/docs/' . $r['slug']) }}"
                               class="block py-1.5 font-serif text-base transition {{ $r['slug'] === $role ? 'ink-secondary font-semibold' : 'text-gray-600 hover:ink-primary' }}">
                                @if($r['slug'] === $role)<span class="ink-accent mr-2">❦</span>@endif
                                {!! $r['title'] !!}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-10 deco-frame">
                    <div class="bg-white border border-rule p-5">
                        <div class="elite-kicker mb-2">Tindakan Cepat</div>
                        <a href="{{ $login_url }}" class="block btn-elite text-center" style="padding:.7rem 1rem;font-size:.65rem;">{{ $login_label }}</a>
                    </div>
                </div>
            </aside>

            {{-- Main content --}}
            <article class="lg:col-span-9">
                <header class="mb-12 pb-10 border-b border-rule">
                    <div class="elite-kicker mb-3">{{ $kicker }}</div>
                    <h1 class="elite-h1 text-4xl sm:text-5xl ink-primary mb-5">{!! $title !!}</h1>
                    <div class="elite-rule mb-5"></div>
                    <p class="elite-lead">{{ $lead }}</p>

                    <div class="mt-7 flex flex-wrap gap-3">
                        <a href="{{ $login_url }}" class="btn-elite">{{ $login_label }}</a>
                        @if(!empty($platform['whatsapp_link']))
                            <a href="{{ $platform['whatsapp_link'] }}" target="_blank" rel="noopener" class="btn-elite-gold">Tanya via WhatsApp</a>
                        @endif
                    </div>
                </header>

                @if(!empty($credentials))
                    <section class="mb-12 deco-frame">
                        <div class="bg-white border border-rule p-6 sm:p-8">
                            <div class="flex items-baseline gap-4 mb-3">
                                <span class="font-display text-2xl ink-accent">⚿</span>
                                <h2 class="elite-h2 text-2xl ink-primary">Kredensial Login Default</h2>
                            </div>
                            <div class="elite-rule mb-5"></div>

                            @if(!empty($credentials['note']))
                                <p class="font-serif text-base leading-relaxed text-gray-700 mb-5">
                                    {{ $credentials['note'] }}
                                </p>
                            @endif

                            @if(!empty($credentials['accounts']))
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm border border-rule" style="font-family:JetBrains Mono,ui-monospace,monospace;">
                                        <thead class="bg-[var(--c-primary)] text-white">
                                            <tr>
                                                <th class="text-left px-4 py-3 font-semibold" style="letter-spacing:.1em;font-size:.7rem;text-transform:uppercase;">Email</th>
                                                <th class="text-left px-4 py-3 font-semibold" style="letter-spacing:.1em;font-size:.7rem;text-transform:uppercase;">Password</th>
                                                <th class="text-left px-4 py-3 font-semibold" style="letter-spacing:.1em;font-size:.7rem;text-transform:uppercase;">Konteks</th>
                                                <th class="text-left px-4 py-3 font-semibold" style="letter-spacing:.1em;font-size:.7rem;text-transform:uppercase;">Role</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($credentials['accounts'] as $acc)
                                                <tr class="border-t border-rule {{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                                                    <td class="px-4 py-3 ink-primary font-semibold">{{ $acc['email'] }}</td>
                                                    <td class="px-4 py-3 ink-secondary">
                                                        <span style="background:rgba(184,134,11,.12);padding:.15rem .5rem;border-radius:3px;">{{ $acc['password'] }}</span>
                                                    </td>
                                                    <td class="px-4 py-3 text-gray-700">{{ $acc['school'] }}</td>
                                                    <td class="px-4 py-3">
                                                        <span class="elite-kicker" style="color: var(--c-accent);">{{ $acc['role'] }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-6 flex items-start gap-3 p-4" style="background:rgba(220,38,38,.06); border-left: 3px solid #b91c1c;">
                                    <span class="font-display text-xl" style="color:#b91c1c;">⚠</span>
                                    <p class="font-serif text-sm leading-relaxed text-gray-800">
                                        <strong class="ink-primary">Peringatan keamanan:</strong>
                                        akun-akun di atas dibuat oleh database seeder untuk keperluan demo dan development.
                                        Sebelum deploy ke production, ganti seluruh password (lewat halaman profil masing-masing user)
                                        atau jalankan ulang seeder versi production yang aman.
                                    </p>
                                </div>
                            @else
                                <p class="font-serif text-base leading-relaxed text-gray-700 italic">
                                    Tidak ada akun demo default untuk peran ini. Akun dibuatkan oleh administrator/TU sekolah.
                                </p>
                            @endif
                        </div>
                    </section>
                @endif

                @foreach($sections as $idx => $section)
                    <section class="mb-12 {{ $idx > 0 ? 'pt-10 border-t border-rule' : '' }}">
                        <div class="flex items-baseline gap-4 mb-6">
                            <span class="font-display text-2xl ink-accent">§ {{ $idx + 1 }}</span>
                            <h2 class="elite-h2 text-3xl ink-primary">{{ $section['title'] }}</h2>
                        </div>

                        <ol class="space-y-4 font-serif text-lg leading-relaxed text-gray-800 pl-1">
                            @foreach($section['items'] as $itemIdx => $item)
                                <li class="flex gap-4">
                                    <span class="elite-kicker mt-1.5 shrink-0" style="color: var(--c-accent); min-width: 2rem;">{{ str_pad($itemIdx + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span>
                                        @php
                                            $text = e($item);
                                            $text = preg_replace('#(/[a-zA-Z0-9\-\/_:.]+)#', '<code style="font-family:JetBrains Mono,ui-monospace,monospace;background:rgba(184,134,11,.1);padding:.1rem .35rem;color:var(--c-secondary);font-size:.92em;">$1</code>', $text);
                                        @endphp
                                        {!! $text !!}
                                    </span>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                @endforeach

                <footer class="mt-12 pt-10 border-t border-rule text-center">
                    <div class="ornament-center"></div>
                    <p class="font-script italic text-2xl ink-secondary mb-2">"{{ $platform['motto_latin'] ?? 'Floreat Schola' }}"</p>
                    <p class="elite-kicker" style="color: var(--c-muted);">— {{ $platform['app_name'] ?? 'Sikad Pro' }}</p>
                </footer>
            </article>
        </div>
    </div>
</section>

@endsection

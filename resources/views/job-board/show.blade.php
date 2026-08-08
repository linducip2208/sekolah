<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $listing->position_title }} di {{ $listing->company_name }} — Job Board Sikad Pro</title>
    @include('elite.partials.head')
    <style>
        .type-badge { display: inline-block; padding: .2rem .7rem; font-size: .65rem; letter-spacing: .1em; text-transform: uppercase; font-weight: 600; border-radius: 2px; }
        .type-fulltime { background: #DCFCE7; color: #166534; }
        .type-parttime { background: #DBEAFE; color: #1E40AF; }
        .type-internship { background: #FEF3C7; color: #92400E; }
        .type-contract { background: #F3E8FF; color: #6B21A8; }
        .type-freelance { background: #FCE7F3; color: #9D1B4C; }
        .prose { font-family: 'Cormorant Garamond', Georgia, serif; font-size: 1.05rem; line-height: 1.7; color: #2d2a26; }
        .prose ul { list-style: disc; padding-left: 1.5rem; }
        .prose li { margin-bottom: .35rem; }
    </style>
</head>
<body class="paper antialiased">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
        <a href="{{ route('job-board.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-6 inline-block">← Kembali ke Job Board</a>

        {{-- Header --}}
        <div class="mb-8">
            <div class="elite-kicker mb-2">Lowongan Kerja</div>
            <h1 class="elite-h1 text-3xl sm:text-5xl ink-primary mb-2">{{ $listing->position_title }}</h1>
            <div class="font-display text-2xl text-gray-700 mb-4">{{ $listing->company_name }}</div>
            <div class="flex flex-wrap gap-3 items-center">
                <span class="type-badge type-{{ $listing->job_type }}">
                    {{ ['fulltime' => 'Full-time', 'parttime' => 'Part-time', 'internship' => 'Internship', 'contract' => 'Kontrak', 'freelance' => 'Freelance'][$listing->job_type] ?? $listing->job_type }}
                </span>
                @if($listing->location)
                <span class="text-sm text-gray-500">📍 {{ $listing->location }}</span>
                @endif
                @if($listing->salary_range)
                <span class="text-sm text-gray-500">💰 {{ $listing->salary_range }}</span>
                @endif
            </div>
            <div class="flex flex-wrap gap-x-5 gap-y-1 text-xs text-gray-400 mt-2">
                <span>Diposting {{ $listing->posted_at?->diffForHumans() }}</span>
                @if($listing->expires_at)
                <span>⏰ Berakhir {{ $listing->expires_at->format('d M Y') }}</span>
                @endif
                <span>👁 {{ $listing->view_count }} dilihat</span>
            </div>
            <div class="elite-rule mt-6"></div>
        </div>

        {{-- Content --}}
        <div class="grid md:grid-cols-3 gap-8 mb-10">
            <div class="md:col-span-2">
                @if($listing->description)
                <div class="mb-8">
                    <h2 class="elite-h2 text-xl ink-primary mb-4">Deskripsi Pekerjaan</h2>
                    <div class="prose">{!! nl2br(e($listing->description)) !!}</div>
                </div>
                @endif

                @if($listing->requirements)
                <div class="mb-8">
                    <h2 class="elite-h2 text-xl ink-primary mb-4">Persyaratan</h2>
                    <div class="prose">{!! nl2br(e($listing->requirements)) !!}</div>
                </div>
                @endif
            </div>

            <div class="md:col-span-1">
                {{-- Quick Info --}}
                <div class="elite-card p-6 mb-6">
                    <h3 class="elite-h3 text-lg ink-primary mb-4">Informasi</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <div class="elite-kicker text-[.55rem]">Diposting Oleh</div>
                            <div class="font-serif font-semibold">{{ $listing->alumniProfile?->user?->name ?? 'Alumni' }}</div>
                            <div class="text-xs text-gray-500">Lulus {{ $listing->alumniProfile?->graduation_year ?? '—' }}
                                @if($listing->alumniProfile?->current_position)
                                · {{ $listing->alumniProfile->current_position }} di {{ $listing->alumniProfile->current_company }}
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="elite-kicker text-[.55rem]">Tipe</div>
                            <div class="font-serif">{{ ['fulltime' => 'Full-time', 'parttime' => 'Part-time', 'internship' => 'Internship', 'contract' => 'Kontrak', 'freelance' => 'Freelance'][$listing->job_type] ?? $listing->job_type }}</div>
                        </div>
                        @if($listing->location)
                        <div>
                            <div class="elite-kicker text-[.55rem]">Lokasi</div>
                            <div class="font-serif">{{ $listing->location }}</div>
                        </div>
                        @endif
                        @if($listing->salary_range)
                        <div>
                            <div class="elite-kicker text-[.55rem]">Kisaran Gaji</div>
                            <div class="font-serif">{{ $listing->salary_range }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Apply Section --}}
                <div class="elite-card p-6">
                    <h3 class="elite-h3 text-lg ink-primary mb-4">Cara Melamar</h3>
                    @if($listing->application_url)
                    <a href="{{ $listing->application_url }}" target="_blank" rel="nofollow" class="btn-elite-gold w-full block text-center mb-4">
                        Lamar via Website
                    </a>
                    @endif

                    @if(session('success'))
                    <div class="mb-4 px-3 py-2 bg-green-50 border-l-4 border-green-600 text-sm text-green-800 font-serif">
                        {{ session('success') }}
                    </div>
                    @endif

                    <details class="mt-3" {{ !$listing->application_url ? 'open' : '' }}>
                        <summary class="cursor-pointer text-sm font-semibold ink-accent">Lamar Langsung Disini</summary>
                        <form method="POST" action="{{ route('job-board.apply', $listing->slug) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                            @csrf
                            <input type="text" name="full_name" required placeholder="Nama Lengkap *" class="w-full border-2 border-rule px-3 py-2 text-sm font-serif">
                            <input type="email" name="email" required placeholder="Email *" class="w-full border-2 border-rule px-3 py-2 text-sm font-serif">
                            <input type="text" name="phone" placeholder="Nomor Telepon" class="w-full border-2 border-rule px-3 py-2 text-sm font-serif">
                            <textarea name="cover_letter" rows="4" placeholder="Cover Letter / Surat Pengantar..." class="w-full border-2 border-rule px-3 py-2 text-sm font-serif"></textarea>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider mb-1 ink-primary">Upload Resume (PDF/DOC, max 5MB)</label>
                                <input type="file" name="resume" accept=".pdf,.doc,.docx" class="w-full text-sm">
                            </div>
                            <button type="submit" class="btn-elite w-full">Kirim Lamaran</button>
                        </form>
                    </details>

                    @if($listing->application_email && !$listing->application_url && !$listing->application_email === '')
                    <div class="mt-4 pt-4 border-t border-rule text-xs text-gray-500">
                        Atau kirim lamaran ke: <a href="mailto:{{ $listing->application_email }}" class="ink-accent underline">{{ $listing->application_email }}</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="pt-8 border-t border-rule text-center">
            <p class="font-script italic text-sm text-gray-500">Didukung oleh Sikad Pro — Jaringan Profesional Alumni</p>
        </div>
    </div>
</body>
</html>
